<?php

namespace App\Packages\Order\Infrastructures;

use App\Models\Order as OrderModel;
use App\Models\OrderItem as OrderItemModel;
use App\Packages\Order\Domains\OrderRepositoryInterface;
use App\Packages\Order\Domains\Entities\Order;
use App\Packages\Order\Domains\Entities\Orders;
use App\Packages\Order\Domains\Entities\OrderItem;
use App\Packages\Order\Domains\Entities\OrderItems;
use App\Packages\Order\Domains\ValueObjects\OrderId;
use App\Packages\Order\Domains\ValueObjects\OrderStatus;
use App\Packages\Order\Domains\ValueObjects\OrderCustomerInfo;
use App\Packages\Order\Domains\ValueObjects\OrderItemId;
use App\Packages\Order\Domains\ValueObjects\OrderItemName;
use App\Packages\Order\Domains\ValueObjects\OrderItemPrice;
use App\Packages\Order\Domains\ValueObjects\ShippingFee;
use App\Packages\Shared\Domains\ValueObjects\EcSiteCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use DateTimeImmutable;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * 注文日をもとに次の注文IDを採番
     *
     * 同じ注文日の注文IDのうち、最大の連番に1を加えた注文IDを返す。
     * 連番の重複を避けるため、トランザクション内で呼び出すことを想定している。
     *
     * @param DateTimeImmutable $orderedAt
     * @return OrderId
     */
    public function nextOrderId(DateTimeImmutable $orderedAt): OrderId
    {
        $prefix = sprintf('Order-%s-', $orderedAt->format('Ymd'));

        $latestOrderId = OrderModel::query()
            ->where('order_id', 'like', $prefix . '%')
            ->orderBy('order_id', 'desc')
            ->lockForUpdate()
            ->value('order_id');

        $sequence = $latestOrderId === null
            ? 1
            : ((int) substr($latestOrderId, strlen($prefix))) + 1;

        return new OrderId($prefix . str_pad((string) $sequence, 3, '0', STR_PAD_LEFT));
    }

    /**
     * 注文を保存
     *
     * @param Order $order
     * @return void
     */
    public function save(Order $order): void
    {
        DB::transaction(function () use ($order) {
            // 注文の保存
            OrderModel::updateOrCreate(
                ['order_id' => $order->getOrderId()->getValue()],
                [
                    'ec_site_code' => $order->getEcSiteCode()->getValue(),
                    'status' => $order->getStatus()->getValue(),
                    'customer_name' => $order->getCustomerInfo()->getName(),
                    'customer_email' => $order->getCustomerInfo()->getEmail(),
                    'customer_phone' => $order->getCustomerInfo()->getPhoneNumber(),
                    'customer_address' => $order->getCustomerInfo()->getAddress(),
                    'shipping_fee_with_tax' => $order->getShippingFee()->getPriceWithTax(),
                    'shipping_fee_without_tax' => $order->getShippingFee()->getPriceWithoutTax(),
                    'shipping_fee_tax_rate' => $order->getShippingFee()->getTaxRate(),
                    'total_amount_with_tax' => $order->getTotalAmountWithTax(),
                    'total_amount_without_tax' => $order->getTotalAmountWithoutTax(),
                    'ordered_at' => $order->getOrderedAt(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            // 注文商品の保存
            foreach ($order->getOrderItems() as $item) {
                OrderItemModel::updateOrCreate(
                    [
                        'order_id' => $order->getOrderId()->getValue(),
                        'item_id' => $item->getItemId()->getValue(),
                        'name' => $item->getName()->getValue(),
                        'price_with_tax' => $item->getPrice()->getPriceWithTax(),
                        'price_without_tax' => $item->getPrice()->getPriceWithoutTax(),
                        'price_tax_rate' => $item->getPrice()->getTaxRate(),
                        'quantity' => $item->getQuantity(),
                        'created_at' => $order->getOrderedAt(),
                        'updated_at' => now(),
                    ]
                );
            }
        });
    }

    /**
     * 注文を一括保存
     */
    public function saveAll(Orders $orders): void
    {
        foreach ($orders as $order) {
            $this->save($order);
        }
    }

    /**
     * 注文IDから注文を取得
     *
     * @param OrderId $orderId
     * @return Order|null
     */
    public function find(OrderId $orderId): ?Order
    {
        $orderModel = OrderModel::with('orderItems')
            ->find($orderId->getValue());

        if (!$orderModel) {
            return null;
        }

        return $this->toEntity($orderModel);
    }

    /**
     * 最近の注文一覧を取得
     *
     * @return Orders
     */
    public function getRecentOrders(): Orders
    {
        $orderModels = OrderModel::with('orderItems')
            ->where('ordered_at', '>=', now()->subDays(30))
            ->orderBy('ordered_at', 'desc')
            ->get();

        return new Orders(
            array_map(
                fn (OrderModel $model) => $this->toEntity($model),
                $orderModels->all()
            )
        );
    }

    /**
     * キャンセル
     *
     * @param Order $order
     * @return void
     */
    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            OrderModel::updateOrCreate(
                ['order_id' => $order->getOrderId()->getValue()],
                [
                    'status' => $order->getStatus()->getValue(),
                    'canceled_at' => now(),
                    'cancel_reason' => $order->getCancelReason(),
                    'updated_at' => now(),
                ]
            );
        });
    }

    /**
     * 保留中の注文を取得
     */
    public function findPending(): Orders
    {
        return $this->findByStatus('pending');
    }

    /**
     * 失敗した注文を取得
     */
    public function findFailed(): Orders
    {
        return $this->findByStatus('failed');
    }

    /**
     * 未発送の注文を取得
     */
    public function findUnshipped(): Orders
    {
        return $this->findByStatus('unshipped');
    }

    /**
     * 注文を削除
     */
    public function delete(OrderId $orderId): void
    {
        OrderModel::destroy($orderId->getValue());
    }

    /**
     * 注文を一括削除
     */
    public function deleteAll(array $orderIds): void
    {
        $ids = array_map(fn (OrderId $id) => $id->getValue(), $orderIds);
        OrderModel::destroy($ids);
    }

    /**
     * ステータスで注文を検索
     */
    private function findByStatus(string $status): Orders
    {
        $orderModels = OrderModel::with('orderItems')
            ->where('status', $status)
            ->orderBy('ordered_at', 'desc')
            ->get();

        return new Orders(
            array_map(
                fn (OrderModel $model) => $this->toEntity($model),
                $orderModels->all()
            )
        );
    }

    /**
     * モデルをエンティティに変換
     */
    private function toEntity(OrderModel $model): Order
    {
        // 注文商品の変換
        $orderItems = new OrderItems(
            array_map(
                fn (OrderItemModel $item) => new OrderItem(
                    new OrderItemId($item->item_id),
                    new OrderItemName($item->name),
                    new OrderItemPrice(
                        $item->price_without_tax,
                        $item->price_tax_rate
                    ),
                    $item->quantity
                ),
                $model->orderItems->all()
            )
        );

        // 注文の変換
        return new Order(
            new OrderId($model->order_id),
            new EcSiteCode($model->ec_site_code),
            new OrderStatus($model->status),
            new DateTimeImmutable($model->ordered_at),
            new ShippingFee(
                $model->shipping_fee_without_tax,
                $model->shipping_fee_tax_rate
            ),
            new OrderCustomerInfo(
                $model->customer_name,
                $model->customer_email,
                $model->customer_phone,
                $model->customer_address
            ),
            $orderItems,
            new DateTimeImmutable($model->created_at),
            $model->updated_at ? new DateTimeImmutable($model->updated_at) : null,
            $model->canceled_at ? new DateTimeImmutable($model->canceled_at) : null,
            $model->cancel_reason
        );
    }

    /**
     * 検索条件付きの注文一覧を取得
     *
     * @param array $searchParams
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchOrders(array $searchParams, int $perPage): LengthAwarePaginator
    {
        $query = OrderModel::query()->with('orderItems');

        // ステータスで絞り込み
        if (!empty($searchParams['status'])) {
            $query->where('status', $searchParams['status']);
        }

        // 注文日で絞り込み
        if (!empty($searchParams['ordered_from'])) {
            $query->where('ordered_at', '>=', $searchParams['ordered_from'] . ' 00:00:00');
        }
        if (!empty($searchParams['ordered_to'])) {
            $query->where('ordered_at', '<=', $searchParams['ordered_to'] . ' 23:59:59');
        }

        // 注文日の降順でソート
        $query->orderBy('ordered_at', 'desc');

        // ページネーション
        return $query->paginate($perPage)->withQueryString();
    }
}
