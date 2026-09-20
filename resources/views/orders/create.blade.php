<x-app-layout>
    <x-slot name="title">{{ __('注文の新規登録') }}</x-slot>
    <x-slot name="header">{{ __('注文の新規登録') }}</x-slot>
    <x-slot name="notification">{{ __('ここで登録した注文は、テスト用の架空のデータとして扱われます。') }}</x-slot>

    @php
        // 入力エラーで戻ってきた場合は入力済みの商品行を、初回表示の場合は空の1行を表示する
        $itemRows = old('items', [['name' => '', 'price' => '', 'tax_rate' => '0.10', 'quantity' => 1]]);
    @endphp

    {{-- 登録処理のエラーメッセージ --}}
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
    @endif

    {{-- バリデーションエラーの一覧 --}}
    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <p class="text-sm font-bold text-red-700">{{ __('入力内容に誤りがあります。以下をご確認ください。') }}</p>
            <ul class="mt-2 list-disc list-inside text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('orders.store') }}" id="orderCreateForm">
        @csrf

        {{-- 注文情報 --}}
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('注文情報') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="ec_site_code" class="block text-sm font-medium text-gray-700">{{ __('ECサイト') }}<span class="text-red-600 ml-1">{{ __('必須') }}</span></label>
                    <select name="ec_site_code" id="ec_site_code" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">{{ __('選択してください') }}</option>
                        @foreach($ecSites as $code => $name)
                            <option value="{{ $code }}" {{ old('ec_site_code', $selectedEcSiteCode) === $code ? 'selected' : '' }}>
                                {{ $name }}（{{ $code }}）
                            </option>
                        @endforeach
                    </select>
                    @error('ec_site_code')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">{{ __('ステータス') }}<span class="text-red-600 ml-1">{{ __('必須') }}</span></label>
                    <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ old('status', 'unshipped') === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="ordered_at" class="block text-sm font-medium text-gray-700">{{ __('注文日時') }}<span class="text-red-600 ml-1">{{ __('必須') }}</span></label>
                    <input type="datetime-local" name="ordered_at" id="ordered_at"
                           value="{{ old('ordered_at', now()->format('Y-m-d\TH:i')) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('ordered_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- お客様情報 --}}
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('お客様情報') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="customer_name" class="block text-sm font-medium text-gray-700">{{ __('お客様名') }}<span class="text-red-600 ml-1">{{ __('必須') }}</span></label>
                    <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}"
                           placeholder="{{ __('仮山田太郎') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('customer_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="customer_email" class="block text-sm font-medium text-gray-700">{{ __('メールアドレス') }}<span class="text-red-600 ml-1">{{ __('必須') }}</span></label>
                    <input type="email" name="customer_email" id="customer_email" value="{{ old('customer_email') }}"
                           placeholder="yamada.taro@example.com"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('customer_email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="customer_phone" class="block text-sm font-medium text-gray-700">{{ __('電話番号') }}<span class="text-red-600 ml-1">{{ __('必須') }}</span></label>
                    <input type="text" name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}"
                           placeholder="090-1234-5678"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('customer_phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="customer_address" class="block text-sm font-medium text-gray-700">{{ __('住所') }}<span class="text-red-600 ml-1">{{ __('必須') }}</span></label>
                    <input type="text" name="customer_address" id="customer_address" value="{{ old('customer_address') }}"
                           placeholder="{{ __('東京都仮新宿区西新宿1-1-1') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('customer_address')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- 注文商品 --}}
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-gray-900">{{ __('注文商品') }}</h2>
                <button type="button" id="addItemButton" class="bg-blue-500 hover:bg-blue-700 text-white text-sm font-bold py-2 px-4 rounded">
                    {{ __('商品を追加') }}
                </button>
            </div>

            @error('items')
                <p class="mb-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('商品名') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('単価（税抜）') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('税率') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('数量') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('小計（税込）') }}</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('操作') }}</th>
                        </tr>
                    </thead>
                    <tbody id="itemRows" class="bg-white divide-y divide-gray-200">
                        @foreach($itemRows as $index => $item)
                            <tr class="item-row align-top">
                                <td class="px-4 py-3">
                                    <input type="text" name="items[{{ $index }}][name]" value="{{ $item['name'] ?? '' }}"
                                           placeholder="{{ __('プロテイン 1kg') }}"
                                           class="block w-full rounded-md border-gray-300 shadow-sm item-name">
                                    @error('items.' . $index . '.name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" name="items[{{ $index }}][price]" value="{{ $item['price'] ?? '' }}"
                                           min="0" step="1"
                                           class="block w-full rounded-md border-gray-300 shadow-sm text-right item-price">
                                    @error('items.' . $index . '.price')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <select name="items[{{ $index }}][tax_rate]" class="block w-full rounded-md border-gray-300 shadow-sm item-tax-rate">
                                        @foreach($taxRates as $value => $label)
                                            <option value="{{ $value }}" {{ (string)($item['tax_rate'] ?? '0.10') === (string)$value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('items.' . $index . '.tax_rate')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}"
                                           min="1" max="99" step="1"
                                           class="block w-full rounded-md border-gray-300 shadow-sm text-right item-quantity">
                                    @error('items.' . $index . '.quantity')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-4 py-3 text-right text-sm text-gray-900 item-subtotal">¥0</td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" class="text-red-600 hover:text-red-900 text-sm remove-item">{{ __('削除') }}</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-sm text-gray-500">{{ __('商品は最大20件まで登録できます。') }}</p>
        </div>

        {{-- 送料と合計 --}}
        <div class="bg-white p-6 rounded-lg shadow-md mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">{{ __('送料') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="shipping_fee" class="block text-sm font-medium text-gray-700">{{ __('送料（税抜）') }}<span class="text-red-600 ml-1">{{ __('必須') }}</span></label>
                    <input type="number" name="shipping_fee" id="shipping_fee" value="{{ old('shipping_fee', 0) }}"
                           min="0" step="1"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-right">
                    @error('shipping_fee')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="shipping_fee_tax_rate" class="block text-sm font-medium text-gray-700">{{ __('送料の税率') }}<span class="text-red-600 ml-1">{{ __('必須') }}</span></label>
                    <select name="shipping_fee_tax_rate" id="shipping_fee_tax_rate" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        @foreach($taxRates as $value => $label)
                            <option value="{{ $value }}" {{ (string)old('shipping_fee_tax_rate', '0.10') === (string)$value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('shipping_fee_tax_rate')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex items-end justify-end">
                    <p class="text-sm text-gray-500">
                        {{ __('注文合計（税込）：') }}
                        <span id="totalAmount" class="ml-2 text-xl font-bold text-gray-900">¥0</span>
                    </p>
                </div>
            </div>
            <p class="mt-2 text-sm text-gray-500">{{ __('※ 画面に表示している合計金額は入力内容からの概算です。確定金額は登録後の注文詳細でご確認ください。') }}</p>
        </div>

        {{-- 操作ボタン --}}
        <div class="flex justify-end space-x-3">
            <a href="{{ route('orders.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                {{ __('一覧に戻る') }}
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                {{ __('この内容で登録する') }}
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        const TAX_RATE_OPTIONS = @json($taxRates);
        const MAX_ITEM_COUNT = 20;

        // 商品行を1行分のHTMLとして組み立てる
        function buildItemRow(index) {
            const options = Object.entries(TAX_RATE_OPTIONS).map(function (entry) {
                const selected = entry[0] === '0.10' ? ' selected' : '';
                return '<option value="' + entry[0] + '"' + selected + '>' + entry[1] + '</option>';
            }).join('');

            const row = document.createElement('tr');
            row.className = 'item-row align-top';
            row.innerHTML = [
                '<td class="px-4 py-3">',
                '<input type="text" name="items[' + index + '][name]" placeholder="プロテイン 1kg" class="block w-full rounded-md border-gray-300 shadow-sm item-name">',
                '</td>',
                '<td class="px-4 py-3">',
                '<input type="number" name="items[' + index + '][price]" min="0" step="1" class="block w-full rounded-md border-gray-300 shadow-sm text-right item-price">',
                '</td>',
                '<td class="px-4 py-3">',
                '<select name="items[' + index + '][tax_rate]" class="block w-full rounded-md border-gray-300 shadow-sm item-tax-rate">' + options + '</select>',
                '</td>',
                '<td class="px-4 py-3">',
                '<input type="number" name="items[' + index + '][quantity]" value="1" min="1" max="99" step="1" class="block w-full rounded-md border-gray-300 shadow-sm text-right item-quantity">',
                '</td>',
                '<td class="px-4 py-3 text-right text-sm text-gray-900 item-subtotal">¥0</td>',
                '<td class="px-4 py-3 text-right">',
                '<button type="button" class="text-red-600 hover:text-red-900 text-sm remove-item">削除</button>',
                '</td>'
            ].join('');

            return row;
        }

        // 行の追加・削除で名前の添字がずれないように振り直す
        function reindexItemRows() {
            document.querySelectorAll('#itemRows .item-row').forEach(function (row, index) {
                row.querySelectorAll('input, select').forEach(function (field) {
                    field.name = field.name.replace(/items\[\d+\]/, 'items[' + index + ']');
                });
            });
        }

        // 税込価格を計算する（税額は四捨五入）
        function toPriceWithTax(price, taxRate) {
            return price + Math.round(price * taxRate);
        }

        // 小計と注文合計を再計算する
        function recalculateTotals() {
            let total = 0;

            document.querySelectorAll('#itemRows .item-row').forEach(function (row) {
                const price = parseInt(row.querySelector('.item-price').value, 10) || 0;
                const quantity = parseInt(row.querySelector('.item-quantity').value, 10) || 0;
                const taxRate = parseFloat(row.querySelector('.item-tax-rate').value) || 0;
                const subtotal = toPriceWithTax(price, taxRate) * quantity;

                row.querySelector('.item-subtotal').textContent = '¥' + subtotal.toLocaleString();
                total += subtotal;
            });

            const shippingFee = parseInt(document.getElementById('shipping_fee').value, 10) || 0;
            const shippingTaxRate = parseFloat(document.getElementById('shipping_fee_tax_rate').value) || 0;
            total += toPriceWithTax(shippingFee, shippingTaxRate);

            document.getElementById('totalAmount').textContent = '¥' + total.toLocaleString();
        }

        document.getElementById('addItemButton').addEventListener('click', function () {
            const rows = document.querySelectorAll('#itemRows .item-row');
            if (rows.length >= MAX_ITEM_COUNT) {
                alert('商品は最大' + MAX_ITEM_COUNT + '件まで登録できます。');
                return;
            }

            document.getElementById('itemRows').appendChild(buildItemRow(rows.length));
            reindexItemRows();
            recalculateTotals();
        });

        document.getElementById('itemRows').addEventListener('click', function (event) {
            if (!event.target.classList.contains('remove-item')) {
                return;
            }

            const rows = document.querySelectorAll('#itemRows .item-row');
            if (rows.length <= 1) {
                alert('注文商品は1件以上必要です。');
                return;
            }

            event.target.closest('.item-row').remove();
            reindexItemRows();
            recalculateTotals();
        });

        document.getElementById('orderCreateForm').addEventListener('input', recalculateTotals);
        document.getElementById('orderCreateForm').addEventListener('change', recalculateTotals);

        recalculateTotals();
    </script>
    @endpush
</x-app-layout>
