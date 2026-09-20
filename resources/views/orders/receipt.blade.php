<x-app-layout>
    <x-slot name="title">{{ __('領収書 - :param_1', ['param_1' => $order['order_id']]) }}</x-slot>
    <x-slot name="header">{{ __('領収書') }}</x-slot>
    <x-slot name="notification">{{ __('この領収書はテスト用の架空のデータに基づいて作成されています。実際の取引を示すものではありません。') }}</x-slot>

    @push('styles')
    <style>
        @media print {
            @page {
                size: A4;
                margin: 12mm;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
            header, footer, .print\:hidden {
                display: none !important;
            }
            .container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>
    @endpush

    <div class="max-w-3xl mx-auto">
        {{-- 領収書ヘッダー --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold mb-4">{{ __('領収書') }}</h1>
            <div class="text-right">
                <p class="text-sm">{{ __('発行日: :param_1', ['param_1' => $receipt['issue_date']->format('Y年m月d日')]) }}</p>
                <p class="text-sm">{{ __('領収書番号: :param_1', ['param_1' => $receipt['number']]) }}</p>
                <p class="text-sm">{{ __('注文番号: :param_1', ['param_1' => $order['order_id']]) }}</p>
            </div>
        </div>

        {{-- 宛名と金額 --}}
        <div class="mb-8">
            <p class="text-xl mb-4">{{ __(':param_1 様', ['param_1' => $order['customer_info']['customer_name']]) }}</p>
            <div class="border-b-2 border-black text-2xl font-bold py-2">
                ￥{{ number_format($order['total_amount_with_tax']) }}<span class="text-sm ml-2">{{ __('（税込）') }}</span>
            </div>
        </div>

        {{-- 明細 --}}
        <div class="mb-8">
            <table class="w-full mb-4">
                <thead>
                    <tr class="border-b">
                        <th class="py-2 text-left">{{ __('商品名') }}</th>
                        <th class="py-2 text-right">{{ __('単価') }}</th>
                        <th class="py-2 text-right">{{ __('数量') }}</th>
                        <th class="py-2 text-right">{{ __('金額') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order['order_items'] as $item)
                        <tr class="border-b">
                            <td class="py-2">{{ $item['name'] }}</td>
                            <td class="py-2 text-right">¥{{ number_format($item['price_with_tax']) }}</td>
                            <td class="py-2 text-right">{{ $item['quantity'] }}</td>
                            <td class="py-2 text-right">¥{{ number_format($item['price_with_tax'] * $item['quantity']) }}</td>
                        </tr>
                    @endforeach
                    <tr class="border-b">
                        <td class="py-2">{{ __('送料') }}</td>
                        <td class="py-2 text-right">-</td>
                        <td class="py-2 text-right">-</td>
                        <td class="py-2 text-right">¥{{ number_format($order['shipping_fee_with_tax']) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2" class="py-2"></td>
                        <td class="py-2 text-right font-bold">{{ __('小計') }}</td>
                        <td class="py-2 text-right">¥{{ number_format($order['total_amount_without_tax']) }}</td>
                    </tr>
                    {{-- 税率ごとの内訳を表示 --}}
                    @foreach($taxAmountsByRate  as $taxInfo)
                        <tr>
                            <td colspan="2" class="py-2"></td>
                            <td class="py-2 text-right font-bold">
                                {{ __('消費税 (:param_1%)', ['param_1' => number_format($taxInfo['tax_rate'] * 100, 1)]) }}
                            </td>
                            <td class="py-2 text-right">
                                ¥{{ number_format($taxInfo['tax_amount']) }}
                                <span class="text-xs text-gray-500">
                                    {{ __('(対象: ¥:param_1)', ['param_1' => number_format($taxInfo['subtotal_without_tax'])]) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                    <tr class="border-t-2 border-black">
                        <td colspan="2" class="py-2"></td>
                        <td class="py-2 text-right font-bold">{{ __('合計') }}</td>
                        <td class="py-2 text-right font-bold">¥{{ number_format($order['total_amount_with_tax']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- 発行元情報 --}}
        <div class="text-center border-t pt-8">
            <h2 class="text-xl font-bold mb-2">{{ $company['name'] }}</h2>
            <p class="text-sm">〒{{ $company['postal_code'] }}</p>
            <p class="text-sm">{{ $company['address'] }}</p>
            <p class="text-sm">{{ __('TEL: :param_1', ['param_1' => $company['tel']]) }}</p>
            <p class="text-sm mt-2">
                {{ __('適格請求書発行事業者登録番号: :param_1', ['param_1' => $company['registration_number']]) }}
            </p>
        </div>

        {{-- 印刷時の注意書きを追加 --}}
        <div class="mt-4 text-center text-xs text-gray-500">
            <p>{{ __('この領収書は電子的に作成されています。印章や署名がなくても有効です。') }}</p>
            <p>{{ __('再発行の場合は「再発行」と記載されます。') }}</p>
        </div>

        {{-- 印刷ボタン --}}
        <div class="mt-8 text-center print:hidden">
            <div class="flex justify-center space-x-4">
                <button onclick="window.print()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('印刷する') }}
                </button>
                <button onclick="window.close()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('閉じる') }}
                </button>
            </div>
        </div>
    </div>
</x-app-layout> 