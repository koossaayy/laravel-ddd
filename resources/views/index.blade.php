<x-app-layout>
    <x-slot name="title">{{ __('受注管理システム（テスト）') }}</x-slot>

    {{-- メインコンテンツ --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {{-- 注文一覧カード --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm rounded-lg transition-all hover:shadow-md">
            <a href="{{ route('orders.index') }}" class="block p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                        <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                            {{ __('注文一覧') }}
                        </h2>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ __('注文の一覧を表示し、詳細確認や状態管理を行います') }}
                        </p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
