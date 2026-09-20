<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Packages\Order\UseCases\OrderShowUseCase;
use App\Packages\Order\UseCases\OrderShowReceiptUseCase;
use App\Packages\Order\UseCases\OrderCancelUseCase;
use App\Packages\Order\UseCases\OrderIndexUseCase;
use App\Packages\Order\UseCases\OrderCreateFormUseCase;
use App\Packages\Order\UseCases\OrderCreateUseCase;
use App\Packages\Order\UseCases\Dtos\OrderIndexRequestDto;
use App\Packages\Order\UseCases\Dtos\OrderShowRequestDto;
use App\Packages\Order\UseCases\Dtos\OrderShowReceiptRequestDto;
use App\Packages\Order\UseCases\Dtos\OrderCancelRequestDto;
use App\Packages\Order\UseCases\Dtos\OrderCreateFormRequestDto;
use App\Packages\Order\UseCases\Dtos\OrderCreateRequestDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Traits\LoggableTrait;

class OrderController extends Controller
{
    use LoggableTrait;

    public function __construct(
        private readonly OrderShowUseCase $orderShowUseCase,
        private readonly OrderShowReceiptUseCase $orderShowReceiptUseCase,
        private readonly OrderCancelUseCase $orderCancelUseCase,
        private readonly OrderIndexUseCase $orderIndexUseCase,
        private readonly OrderCreateFormUseCase $orderCreateFormUseCase,
        private readonly OrderCreateUseCase $orderCreateUseCase
    ) {
    }

    /**
     * 注文一覧を表示
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $this->logCurrentMethod('START request: ' . var_export($request->all(), true));
        // リクエストからDTOを作成
        $requestDto = new OrderIndexRequestDto(
            $request->status,
            $request->ordered_from,
            $request->ordered_to
        );

        // UseCaseを実行
        $responseDto = $this->orderIndexUseCase->execute($requestDto);

        // ビューに渡すデータを取得
        return view('orders.index', $responseDto->toArray());
    }

    /**
     * 注文の新規登録フォームを表示
     * @param Request $request
     * @return View
     */
    public function create(Request $request): View
    {
        $this->logCurrentMethod('START request: ' . var_export($request->all(), true));

        // リクエストからDTOを作成
        $requestDto = new OrderCreateFormRequestDto($request->ec_site_code);

        // UseCaseを実行
        $responseDto = $this->orderCreateFormUseCase->execute($requestDto);

        // ビューに渡すデータを取得
        return view('orders.create', $responseDto->toArray());
    }

    /**
     * 注文を登録
     * @param OrderStoreRequest $request
     * @return RedirectResponse
     */
    public function store(OrderStoreRequest $request): RedirectResponse
    {
        $this->logCurrentMethod('START request: ' . var_export($request->all(), true));

        $validated = $request->validated();

        // リクエストからDTOを作成
        $requestDto = new OrderCreateRequestDto(
            $validated['ec_site_code'],
            $validated['status'],
            $validated['ordered_at'],
            $validated['customer_name'],
            $validated['customer_email'],
            $validated['customer_phone'],
            $validated['customer_address'],
            (int) $validated['shipping_fee'],
            (float) $validated['shipping_fee_tax_rate'],
            array_map(
                fn (array $item) => [
                    'name' => $item['name'],
                    'price' => (int) $item['price'],
                    'tax_rate' => (float) $item['tax_rate'],
                    'quantity' => (int) $item['quantity'],
                ],
                array_values($validated['items'])
            )
        );

        // UseCaseを実行してレスポンスDTOを取得
        $responseDto = $this->orderCreateUseCase->execute($requestDto);

        // 登録に失敗した場合は入力内容を保持したままフォームに戻す
        if (!$responseDto->isSuccess()) {
            return redirect()
                ->route('orders.create')
                ->withInput()
                ->with('error', $responseDto->getMessage());
        }

        // 登録に成功した場合は一覧にメッセージ付きで遷移する
        return redirect()->route('orders.index', ['message' => $responseDto->getMessage()]);
    }

    /**
     * 注文をキャンセル
     * @param Request $request
     * @param string $orderId
     * @return JsonResponse
     */
    public function cancel(Request $request, string $orderId): JsonResponse
    {
        $this->logCurrentMethod('START request: ' . var_export($request->all(), true));

        $cancelReason = $request->input('cancel_reason');
        $cancelReason .= $request->input('other_reason') ? ':' . $request->input('other_reason') : '';
        // リクエストからDTOを作成
        $requestDto = new OrderCancelRequestDto(
            $orderId,
            $cancelReason,
            now()->format('Y-m-d H:i:s')
        );

        // UseCaseを実行してレスポンスDTOを取得
        $responseDto = $this->orderCancelUseCase->execute($requestDto);

        // レスポンスDTOの内容に基づいてJSONレスポンスを返す
        if ($responseDto->isSuccess()) {
            return response()->json([
                'success' => true,
                'message' => $responseDto->getMessage(),
                'order_id' => $responseDto->getOrderId()
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $responseDto->getMessage(),
                'order_id' => $responseDto->getOrderId()
            ], 400);
        }
    }

    /**
     * 領収書を表示
     * @param string $orderId
     * @return View
     */
    public function showReceipt(string $orderId): View
    {
        $this->logCurrentMethod('START request: ' . $orderId);

        // リクエストからDTOを作成
        $requestDto = new OrderShowReceiptRequestDto($orderId);

        // UseCaseを実行
        $responseDto = $this->orderShowReceiptUseCase->execute($requestDto);

        // ビューに渡すデータを取得
        return view('orders.receipt', $responseDto->toArray());
    }

    /**
     * 注文詳細を表示
     * @param string $orderId
     * @return View
     */
    public function showDetail(string $orderId): View
    {
        $this->logCurrentMethod('START request: ' . $orderId);

        // リクエストからDTOを作成
        $requestDto = new OrderShowRequestDto($orderId);

        // UseCaseを実行
        $responseDto = $this->orderShowUseCase->execute($requestDto);

        // ビューに渡すデータを取得
        return view('orders.detail', $responseDto->toArray());
    }
}
