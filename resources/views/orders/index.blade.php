<x-app-layout>
    <x-slot name="title">{{ __('注文一覧') }}</x-slot>
    <x-slot name="header">{{ __('注文一覧') }}</x-slot>
    <x-slot name="notification">{{ __('表示されている注文データはすべてテスト用の架空のデータです。') }}</x-slot>

    @push('styles')
    <style>
        /* トーストメッセージのスタイル */
        #toast {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 50;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }
        #toast.show {
            transform: translateX(0);
        }
    </style>
    @endpush

    <!-- トーストメッセージ -->
    <div id="toast" class="transform transition-transform duration-300">
        <div class="bg-white rounded-lg shadow-lg p-4 max-w-sm">
            <div class="flex items-center">
                <!-- 成功アイコン -->
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <!-- メッセージ -->
                <div class="ml-3">
                    <p id="toastMessage" class="text-sm font-medium text-gray-900"></p>
                </div>
                <!-- 閉じるボタン -->
                <div class="ml-4 flex-shrink-0 flex">
                    <button type="button" class="inline-flex text-gray-400 hover:text-gray-500" onclick="hideToast()">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- 新規登録ボタン --}}
    <div class="flex justify-end mb-4">
        <a href="{{ route('orders.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            {{ __('注文を新規登録') }}
        </a>
    </div>

    {{-- 検索フォーム --}}
    <form method="GET" action="{{ route('orders.index') }}" class="bg-white p-6 rounded-lg shadow-md mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">{{ __('ステータス') }}</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">{{ __('すべて') }}</option>
                    @foreach($statuses as $value => $label)
                        <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="ordered_from" class="block text-sm font-medium text-gray-700">{{ __('開始日') }}</label>
                <input type="date" name="ordered_from" id="ordered_from" 
                       value="{{ request('ordered_from') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div>
                <label for="ordered_to" class="block text-sm font-medium text-gray-700">{{ __('終了日') }}</label>
                <input type="date" name="ordered_to" id="ordered_to" 
                       value="{{ request('ordered_to') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('検索') }}
                </button>
                <a href="{{ route('orders.index') }}" class="ml-2 text-blue-500 hover:text-blue-700">
                    {{ __('クリア') }}
                </a>
            </div>
        </div>
    </form>

    {{-- 注文一覧 --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('注文番号') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('ステータス') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('お客様名') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('合計金額') }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('注文日時') }}</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('操作') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($orders as $order)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $order['order_id'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $order['status'] === 'shipped' ? 'bg-green-100 text-green-800' : 
                                   ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($order['status'] === 'failed' ? 'bg-red-100 text-red-800' : 
                                   ($order['status'] === 'canceled' ? 'bg-red-100 text-gray-800' : 'bg-blue-100 text-blue-800'))) }}">
                                {{ $statuses[$order['status']] ?? $order['status'] }}
                            </span>
                        </td>
                        {{-- {{dd($order)}} --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $order->customer_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                            ¥{{ number_format($order['total_amount_with_tax']) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $order['ordered_at'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 space-x-2">
                            {{-- 詳細ボタン --}}
                            <a href="{{ route('orders.detail', $order->order_id) }}" 
                                class="text-blue-600 hover:text-blue-900"
                                target="_blank">
                                 {{ __('詳細') }}
                            </a>

                            {{-- 領収書リンク --}}
                            @if($order->status === 'unshipped')
                            <a href="{{ route('orders.receipt', $order->order_id) }}" 
                               class="text-blue-600 hover:text-blue-900"
                               target="_blank">
                                {{ __('領収書') }}
                            </a>
                            @else
                            <span class="text-gray-400 cursor-not-allowed" title="{{ __('未発送の注文の領収書は表示できません') }}">
                                {{ __('領収書') }}
                            </span>
                            @endif

                            {{-- キャンセルボタン（未発送の場合のみ表示） --}}
                            @if ($order->status === 'unshipped')
                                <button type="button" class="text-red-600 hover:text-red-900 cancel-order" onclick="openCancelModal('{{ $order['order_id'] }}')">
                                    {{ __('キャンセル') }}
                                </button>
                            @endif
                        </td>
                    {{-- <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('orders.detail', $order['order_id']) }}" 
                               target="_blank"
                               class="text-blue-600 hover:text-blue-900 mr-3">詳細</a>
                            <a href="{{ route('orders.receipt', $order['order_id']) }}" 
                               target="_blank"
                               class="text-green-600 hover:text-green-900 mr-3">領収書</a>
                            @if($order['status'] !== 'canceled' && $order['status'] !== 'shipped')
                                <button onclick="openCancelModal('{{ $order['order_id'] }}')" 
                                        class="text-red-600 hover:text-red-900">キャンセル</button>
                            @endif
                        </td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ページネーション --}}
    <div class="mt-4">
        {{ $orders->links() }}
    </div>

    {{-- キャンセルモーダル --}}
    <div id="cancelModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 mb-4">{{ __('注文キャンセル') }}</h3>
            <p class="mb-4 text-sm text-gray-600">{{ __('この注文をキャンセルしますか？この操作は取り消せません。') }}</p>
            
            <form id="cancelForm" method="POST" action="">
                @csrf
                @method('POST')
                <div class="mb-4">
                    <label for="cancel_reason" class="block text-sm font-medium text-gray-700 mb-2">{{ __('キャンセル理由') }}</label>
                    <select id="cancel_reason" name="cancel_reason" class="w-full rounded-md border-gray-300 shadow-sm" required>
                        <option value="">{{ __('選択してください') }}</option>
                        <option value="customer_request">{{ __('お客様のご都合') }}</option>
                        <option value="out_of_stock">{{ __('在庫切れ') }}</option>
                        <option value="duplicate_order">{{ __('重複注文') }}</option>
                        <option value="system_error">{{ __('システムエラー') }}</option>
                        <option value="other">{{ __('その他') }}</option>
                    </select>
                </div>
                <div class="mb-4 hidden" id="otherReasonContainer">
                    <label for="other_reason" class="block text-sm font-medium text-gray-700 mb-2">{{ __('その他の理由') }}</label>
                    <textarea id="other_reason" name="other_reason" rows="3" class="w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeCancelModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                        {{ __('キャンセル') }}
                    </button>
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        {{ __('注文をキャンセルする') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function showToast(message) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toastMessage');
            toastMessage.textContent = message;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        function hideToast() {
            const toast = document.getElementById('toast');
            toast.classList.remove('show');
        }

        // キャンセルモーダル関連の関数
        function openCancelModal(orderId) {
            document.getElementById('cancelForm').action = `/orders/${orderId}/cancel`;
            document.getElementById('cancelModal').classList.remove('hidden');
        }


        function closeCancelModal() {
            document.getElementById('cancelModal').classList.add('hidden');
            document.getElementById('cancelForm').reset();
            document.getElementById('otherReasonContainer').classList.add('hidden');
        }

        // キャンセルフォーム送信時の処理
        document.getElementById('cancelForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                closeCancelModal();
                showToast(data.message || '注文がキャンセルされました');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('エラーが発生しました。もう一度お試しください。');
            });
        });

        // その他の理由が選択されたときにテキストエリアを表示
        document.getElementById('cancel_reason').addEventListener('change', function() {
            const otherContainer = document.getElementById('otherReasonContainer');
            if (this.value === 'other') {
                otherContainer.classList.remove('hidden');
            } else {
                otherContainer.classList.add('hidden');
            }
        });

        // URLパラメータからメッセージを取得して表示
        const urlParams = new URLSearchParams(window.location.search);
        const message = urlParams.get('message');
        if (message) {
            showToast(message);
        }
    </script>
    @endpush
</x-app-layout>