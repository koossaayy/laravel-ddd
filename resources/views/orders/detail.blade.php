<x-app-layout>
    <x-slot name="title">{{ __('注文詳細 - :param_1', ['param_1' => $order['order_id']]) }}</x-slot>
    <x-slot name="header">{{ __('注文詳細') }}</x-slot>
    <x-slot name="notification">{{ __('表示されているデータはテスト用の架空のデータです。実際の取引を示すものではありません。') }}</x-slot>

    <div class="max-w-3xl mx-auto">
        {{-- ヘッダー --}}
        <div class="flex justify-between items-center mb-6">
        </div>

        {{-- 注文基本情報 --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-bold mb-4">{{ __('注文情報') }}</h2>
                    <dl class="grid grid-cols-3 gap-2 text-sm">
                        <dt class="text-gray-600">{{ __('注文番号:') }}</dt>
                        <dd class="col-span-2">{{ $order['order_id'] }}</dd>
                        
                        <dt class="text-gray-600">{{ __('注文日時:') }}</dt>
                        <dd class="col-span-2">{{ $order['ordered_at'] }}</dd>
                        
                        <dt class="text-gray-600">{{ __('ステータス:') }}</dt>
                        <dd class="col-span-2">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $order['status'] === 'shipped' ? 'bg-green-100 text-green-800' : 
                                   ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($order['status'] === 'failed' ? 'bg-red-100 text-red-800' : 
                                   ($order['status'] === 'canceled' ? 'bg-red-100 text-gray-800' : 'bg-blue-100 text-blue-800'))) }}">
                                {{ $statuses[$order['status']] ?? $order['status'] }}
                            </span>
                            @if($order['status'] === 'canceled')
                                <span class="block text-xs text-gray-500 mt-1">
                                    {{ __('キャンセル日時: :param_1', ['param_1' => $order['canceled_at']]) }}<br>
                                    {{ __('キャンセル理由: :param_1', ['param_1' => $order['cancel_reason']]) }}
                                </span>
                            @endif
                        </dd>
                    </dl>
                </div>

                <div>
                    <h2 class="text-lg font-bold mb-4">{{ __('お客様情報') }}</h2>
                    <dl class="grid grid-cols-3 gap-2 text-sm">
                        <dt class="text-gray-600">{{ __('お名前:') }}</dt>
                        <dd class="col-span-2">{{ $order['customer_info']['customer_name'] }}</dd>
                        
                        <dt class="text-gray-600">{{ __('メール:') }}</dt>
                        <dd class="col-span-2">{{ $order['customer_info']['customer_email'] }}</dd>
                        
                        <dt class="text-gray-600">{{ __('電話番号:') }}</dt>
                        <dd class="col-span-2">{{ $order['customer_info']['customer_phone'] }}</dd>
                        
                        <dt class="text-gray-600">{{ __('配送先:') }}</dt>
                        <dd class="col-span-2 whitespace-pre-line">{{ $order['customer_info']['customer_address'] }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        {{-- 注文商品 --}}
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-bold mb-4">{{ __('注文商品') }}</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2 text-left">{{ __('商品名') }}</th>
                            <th class="py-2 text-right">{{ __('単価（税込）') }}</th>
                            <th class="py-2 text-right">{{ __('単価（税抜）') }}</th>
                            <th class="py-2 text-right">{{ __('税率') }}</th>
                            <th class="py-2 text-right">{{ __('数量') }}</th>
                            <th class="py-2 text-right">{{ __('小計（税込）') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order['order_items'] as $item)
                            <tr class="border-b">
                                <td class="py-2">{{ $item['name'] }}</td>
                                <td class="py-2 text-right">¥{{ number_format($item['price_with_tax']) }}</td>
                                <td class="py-2 text-right">¥{{ number_format($item['price_without_tax']) }}</td>
                                <td class="py-2 text-right">{{ number_format($item['price_tax_rate'] * 100, 1) }}%</td>
                                <td class="py-2 text-right">{{ $item['quantity'] }}</td>
                                <td class="py-2 text-right">¥{{ number_format($item['price_with_tax'] * $item['quantity']) }}</td>
                            </tr>
                        @endforeach
                        <tr class="border-b">
                            <td class="py-2">{{ __('送料') }}</td>
                            <td class="py-2 text-right">¥{{ number_format($order['shipping_fee_with_tax']) }}</td>
                            <td class="py-2 text-right">¥{{ number_format($order['shipping_fee_without_tax']) }}</td>
                            <td class="py-2 text-right">{{ number_format($order['shipping_fee_tax_rate'] * 100, 1) }}%</td>
                            <td class="py-2 text-right">-</td>
                            <td class="py-2 text-right">¥{{ number_format($order['shipping_fee_with_tax']) }}</td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="py-2"></td>
                            <td class="py-2 text-right font-bold">{{ __('小計（税抜）:') }}</td>
                            <td class="py-2 text-right">¥{{ number_format($order['total_amount_without_tax']) }}</td>
                        </tr>
                        @foreach($taxAmountsByRate as $taxInfo)
                            <tr>
                                <td colspan="3" class="py-2"></td>
                                <td class="py-2 text-right font-bold">
                                    {{ __('消費税 (:param_1%):', ['param_1' => number_format($taxInfo['tax_rate'] * 100, 1)]) }}
                                </td>
                                <td class="py-2 text-right">
                                    ¥{{ number_format($taxInfo['tax_amount']) }}
                                </td>
                            </tr>
                        @endforeach
                        <tr class="border-t-2 border-black">
                            <td colspan="3" class="py-2"></td>
                            <td class="py-2 text-right font-bold">{{ __('合計（税込）:') }}</td>
                            <td class="py-2 text-right font-bold">¥{{ number_format($order['total_amount_with_tax']) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- 操作ボタン --}}
        <div class="flex justify-end space-x-4">
            <a href="javascript:window.close()" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">{{ __('閉じる') }}</a>
            <a href="{{ route('orders.receipt', $order['order_id']) }}" 
               target="_blank"
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">{{ __('領収書を表示') }}</a>
        </div>
    </div>
</x-app-layout> 