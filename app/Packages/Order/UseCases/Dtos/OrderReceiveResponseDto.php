<?php

namespace App\Packages\Order\UseCases\Dtos;

/**
 * 注文取得結果DTO
 */
class OrderReceiveResponseDto
{
    /**
     * @param int $processedCount 処理した注文数
     * @param int $successCount 成功した注文数
     * @param int $errorCount 失敗した注文数
     * @param array<string> $errorMessages エラーメッセージ
     */
    public function __construct(
        private readonly int $processedCount,
        private readonly int $successCount,
        private readonly int $errorCount,
        private readonly array $errorMessages = []
    ) {
    }

    /**
     * 処理した注文数を取得
     *
     * @return int
     */
    public function getProcessedCount(): int
    {
        return $this->processedCount;
    }

    /**
     * 成功した注文数を取得
     *
     * @return int
     */
    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    /**
     * 失敗した注文数を取得
     *
     * @return int
     */
    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    /**
     * エラーメッセージを取得
     *
     * @return array<string>
     */
    public function getErrorMessages(): array
    {
        return $this->errorMessages;
    }

    /**
     * 処理結果の概要を取得
     *
     * @return string
     */
    public function getSummary(): string
    {
        return sprintf(
            __("処理結果: 合計 %d件 (成功: %d件, 失敗: %d件)"),
            $this->processedCount,
            $this->successCount,
            $this->errorCount
        );
    }

    /**
     * 配列に変換
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'processed_count' => $this->processedCount,
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'error_messages' => $this->errorMessages,
        ];
    }
}
