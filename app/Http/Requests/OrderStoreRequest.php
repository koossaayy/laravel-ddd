<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * 注文登録リクエスト
 */
class OrderStoreRequest extends FormRequest
{
    /** @var array<int, string> 選択できる消費税率一覧 */
    private const VALID_TAX_RATES = ['0.00', '0.08', '0.10'];

    /** @var array<int, string> 手動登録できる注文ステータス一覧 */
    private const VALID_STATUSES = ['pending', 'unshipped'];

    /**
     * リクエストが許可されているかどうかを判定
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーション前に入力値を整形
     *
     * 税率は「0.1」と「0.10」のどちらで送信されても同じ値として扱う。
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $items = $this->input('items');

        $this->merge([
            'shipping_fee_tax_rate' => $this->normalizeTaxRate($this->input('shipping_fee_tax_rate')),
            'items' => is_array($items)
                ? array_map(
                    function ($item) {
                        if (!is_array($item)) {
                            return $item;
                        }

                        $item['tax_rate'] = $this->normalizeTaxRate($item['tax_rate'] ?? null);

                        return $item;
                    },
                    $items
                )
                : $items,
        ]);
    }

    /**
     * バリデーションルールを取得
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'ec_site_code' => [
                'required',
                'string',
                'max:20',
                Rule::exists('ec_sites', 'code')->where('is_active', true),
            ],
            'status' => ['required', 'string', Rule::in(self::VALID_STATUSES)],
            'ordered_at' => ['required', 'date', 'before_or_equal:now'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'string', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:20', 'regex:/\A[0-9０-９+＋\-ー]+\z/u'],
            'customer_address' => ['required', 'string', 'max:255'],
            'shipping_fee' => ['required', 'integer', 'min:0', 'max:99999'],
            'shipping_fee_tax_rate' => ['required', Rule::in(self::VALID_TAX_RATES)],
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.price' => ['required', 'integer', 'min:0', 'max:9999999'],
            'items.*.tax_rate' => ['required', Rule::in(self::VALID_TAX_RATES)],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * 項目名を取得
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ec_site_code' => 'ECサイト',
            'status' => 'ステータス',
            'ordered_at' => '注文日時',
            'customer_name' => 'お客様名',
            'customer_email' => 'メールアドレス',
            'customer_phone' => '電話番号',
            'customer_address' => '住所',
            'shipping_fee' => '送料（税抜）',
            'shipping_fee_tax_rate' => '送料の税率',
            'items' => '注文商品',
            'items.*.name' => '商品名',
            'items.*.price' => '単価（税抜）',
            'items.*.tax_rate' => '税率',
            'items.*.quantity' => '数量',
        ];
    }

    /**
     * バリデーションメッセージを取得
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':attributeを入力してください。',
            'integer' => ':attributeは整数で入力してください。',
            'date' => ':attributeは日時の形式で入力してください。',
            'in' => ':attributeの選択値が正しくありません。',
            'min' => ':attributeは:min以上で入力してください。',
            'max' => ':attributeは:max文字以内で入力してください。',
            'ec_site_code.required' => 'ECサイトを選択してください。',
            'ec_site_code.exists' => '選択されたECサイトは存在しないか、無効になっています。',
            'status.required' => 'ステータスを選択してください。',
            'ordered_at.before_or_equal' => '注文日時に未来の日時は指定できません。',
            'customer_email.email' => 'メールアドレスの形式が正しくありません。',
            'customer_phone.regex' => '電話番号は数字とハイフンのみで入力してください。',
            'shipping_fee.min' => '送料（税抜）は:min円以上で入力してください。',
            'shipping_fee.max' => '送料（税抜）は:max円以下で入力してください。',
            'shipping_fee_tax_rate.in' => '送料の税率は10%、8%、0%のいずれかを選択してください。',
            'items.required' => '注文商品を1件以上入力してください。',
            'items.array' => '注文商品の形式が正しくありません。',
            'items.min' => '注文商品を1件以上入力してください。',
            'items.max' => '注文商品は:max件まで登録できます。',
            'items.*.name.required' => '商品名を入力してください。',
            'items.*.price.required' => '単価（税抜）を入力してください。',
            'items.*.quantity.required' => '数量を入力してください。',
            'items.*.price.min' => '単価（税抜）は:min円以上で入力してください。',
            'items.*.price.max' => '単価（税抜）は:max円以下で入力してください。',
            'items.*.quantity.min' => '数量は:min以上で入力してください。',
            'items.*.quantity.max' => '数量は:max以下で入力してください。',
            'items.*.tax_rate.in' => '税率は10%、8%、0%のいずれかを選択してください。',
        ];
    }

    /**
     * 税率を小数第2位までの文字列に整形
     *
     * @param mixed $taxRate
     * @return mixed
     */
    private function normalizeTaxRate(mixed $taxRate): mixed
    {
        if (!is_numeric($taxRate)) {
            return $taxRate;
        }

        return number_format((float) $taxRate, 2, '.', '');
    }
}
