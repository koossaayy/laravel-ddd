<?php

namespace App\Packages\Shared\Domains;

interface EcSiteGetterInterface
{
    /**
     * 有効なECサイトの一覧を取得
     *
     * @return array<string, string> ECサイトコードをキー、ECサイト名を値とする配列
     */
    public function getActiveEcSites(): array;
}
