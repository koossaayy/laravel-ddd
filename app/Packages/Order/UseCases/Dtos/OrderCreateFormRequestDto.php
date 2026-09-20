<?php

namespace App\Packages\Order\UseCases\Dtos;

/**
 * 注文登録フォーム表示リクエストDTO
 */
class OrderCreateFormRequestDto
{
    /**
     * @param string|null $ecSiteCode 初期選択するECサイトコード
     */
    public function __construct(
        private readonly ?string $ecSiteCode = null
    ) {
    }

    /**
     * 初期選択するECサイトコードを取得
     *
     * @return string|null
     */
    public function getEcSiteCode(): ?string
    {
        return $this->ecSiteCode;
    }
}
