<?php

namespace App\Packages\Shared\Infrastructures;

use App\Models\EcSite as EcSiteModel;
use App\Packages\Shared\Domains\EcSiteGetterInterface;

class EcSiteGetter implements EcSiteGetterInterface
{
    /**
     * 有効なECサイトの一覧を取得
     *
     * @return array<string, string> ECサイトコードをキー、ECサイト名を値とする配列
     */
    public function getActiveEcSites(): array
    {
        return EcSiteModel::active()
            ->orderBy('code')
            ->pluck('name', 'code')
            ->all();
    }
}
