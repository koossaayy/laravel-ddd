<?php

namespace App\Packages\Order\UseCases\Dtos;

/**
 * 注文登録リクエストDTO
 */
class OrderCreateRequestDto
{
    /**
     * @param string $ecSiteCode ECサイトコード
     * @param string $status 注文ステータス
     * @param string $orderedAt 注文日時
     * @param string $customerName お客様名
     * @param string $customerEmail メールアドレス
     * @param string $customerPhone 電話番号
     * @param string $customerAddress 住所
     * @param int $shippingFee 送料（税抜）
     * @param float $shippingFeeTaxRate 送料の税率
     * @param array<int, array{name: string, price: int, tax_rate: float, quantity: int}> $items 注文商品
     */
    public function __construct(
        private readonly string $ecSiteCode,
        private readonly string $status,
        private readonly string $orderedAt,
        private readonly string $customerName,
        private readonly string $customerEmail,
        private readonly string $customerPhone,
        private readonly string $customerAddress,
        private readonly int $shippingFee,
        private readonly float $shippingFeeTaxRate,
        private readonly array $items
    ) {
    }

    /**
     * ECサイトコードを取得
     *
     * @return string
     */
    public function getEcSiteCode(): string
    {
        return $this->ecSiteCode;
    }

    /**
     * 注文ステータスを取得
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * 注文日時を取得
     *
     * @return string
     */
    public function getOrderedAt(): string
    {
        return $this->orderedAt;
    }

    /**
     * お客様名を取得
     *
     * @return string
     */
    public function getCustomerName(): string
    {
        return $this->customerName;
    }

    /**
     * メールアドレスを取得
     *
     * @return string
     */
    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    /**
     * 電話番号を取得
     *
     * @return string
     */
    public function getCustomerPhone(): string
    {
        return $this->customerPhone;
    }

    /**
     * 住所を取得
     *
     * @return string
     */
    public function getCustomerAddress(): string
    {
        return $this->customerAddress;
    }

    /**
     * 送料（税抜）を取得
     *
     * @return int
     */
    public function getShippingFee(): int
    {
        return $this->shippingFee;
    }

    /**
     * 送料の税率を取得
     *
     * @return float
     */
    public function getShippingFeeTaxRate(): float
    {
        return $this->shippingFeeTaxRate;
    }

    /**
     * 注文商品を取得
     *
     * @return array<int, array{name: string, price: int, tax_rate: float, quantity: int}>
     */
    public function getItems(): array
    {
        return $this->items;
    }
}
