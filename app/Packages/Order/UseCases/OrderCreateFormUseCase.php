<?php

namespace App\Packages\Order\UseCases;

use App\Packages\Order\UseCases\Dtos\OrderCreateFormRequestDto;
use App\Packages\Order\UseCases\Dtos\OrderCreateFormResponseDto;
use App\Packages\Shared\Domains\EcSiteGetterInterface;

/**
 * 注文登録フォーム表示ユースケース
 */
class OrderCreateFormUseCase
{
    private static function selectableStatuses(): array
    {
        return [
            'pending' => __('保留中'),
            'unshipped' => __('未発送'),
        ];
    }

    private static function selectableTaxRates(): array
    {
        return [
            '0.10' => __('10%（標準税率）'),
            '0.08' => __('8%（軽減税率）'),
            '0.00' => __('0%（非課税）'),
        ];
    }

    /**
     * コンストラクタ
     *
     * @param EcSiteGetterInterface $ecSiteGetter
     */
    public function __construct(
        private readonly EcSiteGetterInterface $ecSiteGetter
    ) {
    }

    /**
     * 注文登録フォームの表示データを取得
     *
     * @param OrderCreateFormRequestDto $requestDto
     * @return OrderCreateFormResponseDto
     */
    public function execute(OrderCreateFormRequestDto $requestDto): OrderCreateFormResponseDto
    {
        $ecSites = $this->ecSiteGetter->getActiveEcSites();

        // 指定されたECサイトが存在しない場合は先頭のECサイトを初期選択にする
        $selectedEcSiteCode = $requestDto->getEcSiteCode();
        if ($selectedEcSiteCode === null || !array_key_exists($selectedEcSiteCode, $ecSites)) {
            $selectedEcSiteCode = array_key_first($ecSites);
        }

        return new OrderCreateFormResponseDto(
            $ecSites,
            self::selectableStatuses(),
            self::selectableTaxRates(),
            $selectedEcSiteCode
        );
    }
}
