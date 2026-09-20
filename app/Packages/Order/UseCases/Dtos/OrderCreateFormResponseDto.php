<?php

namespace App\Packages\Order\UseCases\Dtos;

/**
 * 注文登録フォーム表示結果DTO
 */
class OrderCreateFormResponseDto
{
    /**
     * @param array<string, string> $ecSites ECサイト一覧
     * @param array<string, string> $statuses 登録可能なステータス一覧
     * @param array<string, string> $taxRates 選択可能な税率一覧
     * @param string|null $selectedEcSiteCode 初期選択するECサイトコード
     */
    public function __construct(
        private readonly array $ecSites,
        private readonly array $statuses,
        private readonly array $taxRates,
        private readonly ?string $selectedEcSiteCode = null
    ) {
    }

    /**
     * ECサイト一覧を取得
     *
     * @return array<string, string>
     */
    public function getEcSites(): array
    {
        return $this->ecSites;
    }

    /**
     * ステータス一覧を取得
     *
     * @return array<string, string>
     */
    public function getStatuses(): array
    {
        return $this->statuses;
    }

    /**
     * 税率一覧を取得
     *
     * @return array<string, string>
     */
    public function getTaxRates(): array
    {
        return $this->taxRates;
    }

    /**
     * 初期選択するECサイトコードを取得
     *
     * @return string|null
     */
    public function getSelectedEcSiteCode(): ?string
    {
        return $this->selectedEcSiteCode;
    }

    /**
     * ビューに渡すデータを取得
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'ecSites' => $this->ecSites,
            'statuses' => $this->statuses,
            'taxRates' => $this->taxRates,
            'selectedEcSiteCode' => $this->selectedEcSiteCode,
        ];
    }
}
