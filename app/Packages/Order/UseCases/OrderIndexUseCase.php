<?php

namespace App\Packages\Order\UseCases;

use App\Packages\Order\Domains\OrderRepositoryInterface;
use App\Packages\Order\UseCases\Dtos\OrderIndexRequestDto;
use App\Packages\Order\UseCases\Dtos\OrderIndexResponseDto;

class OrderIndexUseCase
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * 注文一覧を取得
     *
     * @param OrderIndexRequestDto $requestDto
     * @return OrderIndexResponseDto
     */
    public function execute(OrderIndexRequestDto $requestDto): OrderIndexResponseDto
    {

        // リポジトリを使用して注文一覧を取得
        $orders = $this->orderRepository->searchOrders(
            $requestDto->toArray(),
            $requestDto->getPerPage()
        );

        // ステータス一覧
        $statuses = [
            'pending' => __('保留中'),
            'canceled' => __('キャンセル'),
            'unshipped' => __('未発送'),
        ];

        return new OrderIndexResponseDto(
            $orders,
            $statuses,
            [
                'status' => $requestDto->getStatus(),
                'ordered_from' => $requestDto->getOrderedFrom(),
                'ordered_to' => $requestDto->getOrderedTo(),
            ]
        );
    }
}
