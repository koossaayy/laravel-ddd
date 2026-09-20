<?php

namespace App\Packages\Order\UseCases;

use App\Packages\Order\Domains\Entities\Order;
use App\Packages\Order\Domains\Entities\OrderItem;
use App\Packages\Order\Domains\Entities\OrderItems;
use App\Packages\Order\Domains\OrderRepositoryInterface;
use App\Packages\Order\Domains\ValueObjects\OrderCustomerInfo;
use App\Packages\Order\Domains\ValueObjects\OrderItemId;
use App\Packages\Order\Domains\ValueObjects\OrderItemName;
use App\Packages\Order\Domains\ValueObjects\OrderItemPrice;
use App\Packages\Order\Domains\ValueObjects\OrderStatus;
use App\Packages\Order\Domains\ValueObjects\ShippingFee;
use App\Packages\Order\UseCases\Dtos\OrderCreateRequestDto;
use App\Packages\Order\UseCases\Dtos\OrderCreateResponseDto;
use App\Packages\Shared\Domains\ValueObjects\EcSiteCode;
use DateTimeImmutable;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * 注文登録ユースケース
 */
class OrderCreateUseCase
{
    /**
     * コンストラクタ
     *
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * 注文を登録
     *
     * @param OrderCreateRequestDto $requestDto
     * @return OrderCreateResponseDto
     */
    public function execute(OrderCreateRequestDto $requestDto): OrderCreateResponseDto
    {
        try {
            $orderId = DB::transaction(function () use ($requestDto) {
                $orderedAt = new DateTimeImmutable($requestDto->getOrderedAt());

                // 注文日から採番した注文IDで注文を組み立てる
                $orderId = $this->orderRepository->nextOrderId($orderedAt);

                $order = new Order(
                    $orderId,
                    new EcSiteCode($requestDto->getEcSiteCode()),
                    new OrderStatus($requestDto->getStatus()),
                    $orderedAt,
                    new ShippingFee(
                        $requestDto->getShippingFee(),
                        $requestDto->getShippingFeeTaxRate()
                    ),
                    new OrderCustomerInfo(
                        $requestDto->getCustomerName(),
                        $requestDto->getCustomerEmail(),
                        $requestDto->getCustomerPhone(),
                        $requestDto->getCustomerAddress()
                    ),
                    $this->buildOrderItems($requestDto->getItems()),
                    new DateTimeImmutable()
                );

                $this->orderRepository->save($order);

                return $orderId;
            });
        } catch (Exception $e) {
            // エラーレスポンスを返す
            return new OrderCreateResponseDto(
                false,
                sprintf('注文登録処理中にエラーが発生しました: %s', $e->getMessage())
            );
        }

        // 成功レスポンスを返す
        return new OrderCreateResponseDto(
            true,
            sprintf('注文を登録しました: %s', $orderId->getValue()),
            $orderId->getValue()
        );
    }

    /**
     * リクエストの注文商品から商品リストを組み立てる
     *
     * @param array<int, array{name: string, price: int, tax_rate: float, quantity: int}> $items
     * @return OrderItems
     */
    private function buildOrderItems(array $items): OrderItems
    {
        $orderItems = new OrderItems();

        foreach (array_values($items) as $index => $item) {
            // 手動登録の商品には注文内で一意になる連番の商品IDを採番する
            $itemId = new OrderItemId(sprintf('ITEM-%05d', $index + 1));

            $orderItems->addItem(
                new OrderItem(
                    $itemId,
                    new OrderItemName($item['name']),
                    new OrderItemPrice($item['price'], $item['tax_rate']),
                    $item['quantity']
                )
            );
        }

        return $orderItems;
    }
}
