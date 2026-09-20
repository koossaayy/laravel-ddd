<?php

namespace App\Packages\Order\UseCases;

use App\Packages\Order\Domains\OrderRepositoryInterface;
use App\Packages\Order\Domains\ValueObjects\OrderId;
use App\Packages\Order\UseCases\Dtos\OrderShowRequestDto;
use App\Packages\Order\UseCases\Dtos\OrderShowResponseDto;

class OrderShowUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * 注文詳細を取得
     *
     * @param OrderShowRequestDto $requestDto
     * @return OrderShowResponseDto
     */
    public function execute(OrderShowRequestDto $requestDto): OrderShowResponseDto
    {
        // リポジトリから注文エンティティを取得
        $orderId = new OrderId($requestDto->getOrderId());
        $order = $this->orderRepository->find($orderId);

        if (!$order) {
            throw new \RuntimeException('注文が見つかりませんでした。');
        }

        // 税率ごとの金額を取得
        $taxAmountsByRate = $order->getTaxAmountsByRate();

        // ステータス一覧
        $statuses = [
            'pending' => __('保留中'),
            'failed' => __('失敗'),
            'unshipped' => __('決済待ち'),
            'shipped' => __('発送済み'),
            'canceled' => __('キャンセル'),
        ];

        // エンティティから注文データを配列に変換
        $orderData = [
            'order_id' => $order->getOrderId()->getValue(),
            'status' => $order->getStatus()->getValue(),
            'ordered_at' => $order->getOrderedAt()->format('Y年m月d日 H:i'),
            'canceled_at' => $order->getCanceledAt() ? $order->getCanceledAt()->format('Y年m月d日 H:i') : null,
            'created_at' => $order->getCreatedAt()->format('Y年m月d日 H:i'),
            'updated_at' => $order->getUpdatedAt() ? $order->getUpdatedAt()->format('Y年m月d日 H:i') : null,
            'cancel_reason' => $order->getCancelReason(),
            'customer_info' => [
                'customer_name' => $order->getCustomerInfo()->getName(),
                'customer_email' => $order->getCustomerInfo()->getEmail(),
                'customer_phone' => $order->getCustomerInfo()->getPhoneNumber(),
                'customer_address' => $order->getCustomerInfo()->getAddress(),
            ],
            'shipping_fee_with_tax' => $order->getShippingFee()->getPriceWithTax(),
            'shipping_fee_without_tax' => $order->getShippingFee()->getPriceWithoutTax(),
            'shipping_fee_tax_rate' => $order->getShippingFee()->getTaxRate(),
            'total_amount_with_tax' => $order->getTotalAmountWithTax(),
            'total_amount_without_tax' => $order->getTotalAmountWithoutTax(),
            'order_items' => []
        ];

        // 注文商品情報を追加
        foreach ($order->getOrderItems() as $item) {
            $orderData['order_items'][] = [
                'item_id' => $item->getItemId()->getValue(),
                'name' => $item->getName()->getValue(),
                'price_with_tax' => $item->getPrice()->getPriceWithTax(),
                'price_without_tax' => $item->getPrice()->getPriceWithoutTax(),
                'price_tax_rate' => $item->getPrice()->getTaxRate(),
                'quantity' => $item->getQuantity(),
                'subtotal_with_tax' => $item->getSubtotalWithTax(),
                'subtotal_without_tax' => $item->getSubtotalWithoutTax()
            ];
        }

        return new OrderShowResponseDto(
            $orderData,
            $taxAmountsByRate,
            $statuses
        );
    }
}
