<?php

namespace App\Packages\Order\Infrastructures;

use App\Packages\Order\Domains\OrderGetterInterface;
use App\Packages\Order\Domains\Entities\Order;
use App\Packages\Order\Domains\Entities\Orders;
use App\Packages\Order\Domains\Entities\OrderItem;
use App\Packages\Order\Domains\Entities\OrderItems;
use App\Packages\Order\Domains\ValueObjects\OrderItemPrice;
use App\Packages\Order\Domains\ValueObjects\OrderId;
use App\Packages\Order\Domains\ValueObjects\OrderStatus;
use App\Packages\Order\Domains\ValueObjects\OrderItemId;
use App\Packages\Order\Domains\ValueObjects\OrderItemName;
use App\Packages\Order\Domains\ValueObjects\OrderCustomerInfo;
use App\Packages\Order\Domains\ValueObjects\ShippingFee;
use App\Packages\Shared\Domains\ValueObjects\EcSiteCode;
use Illuminate\Support\Facades\Log;
use DateTimeImmutable;

class TestMallOrderGetter implements OrderGetterInterface
{
    /** @var array<string, int> 日付ごとの注文数を保持 */
    private array $orderCountByDate = [];

    /** @var array<string, array{name: string, price: int, is_food: bool}> */
    private array $products = [
        // プロテイン・サプリメント（食品）
        'PROTEIN' => ['name' => 'プロテイン 1kg', 'price' => 3980, 'is_food' => true],
        'SUPPLEMENT-50' => ['name' => 'サプリメント 50粒', 'price' => 480, 'is_food' => true],
        'SUPPLEMENT-100' => ['name' => 'サプリメント 100粒', 'price' => 980, 'is_food' => true],
        'SUPPLEMENT-200' => ['name' => 'サプリメント 200粒', 'price' => 1800, 'is_food' => true],
        'CREATINE' => ['name' => 'クレアチン 500g', 'price' => 2980, 'is_food' => true],

        // トレーニング用品
        'DUMBBELL' => ['name' => '可変式ダンベル 20kg', 'price' => 15800, 'is_food' => false],
        'YOGA_MAT' => ['name' => 'ヨガマット 10mm', 'price' => 2480, 'is_food' => false],
        'BAND' => ['name' => 'トレーニングバンド 5本セット', 'price' => 1980, 'is_food' => false],

        // ウェア
        'SHIRT' => ['name' => '速乾性トレーニングシャツ', 'price' => 2980, 'is_food' => false],
        'PANTS' => ['name' => 'ストレッチトレーニングパンツ', 'price' => 3980, 'is_food' => false],
        'SHOES' => ['name' => 'ランニングシューズ', 'price' => 8980, 'is_food' => false],
    ];

    /** @var array<array{name: string, email: string, phone: string, address: string}> */
    private array $repeaterCustomers = [
        [
            'name' => '仮山田太郎',
            'email' => 'yamada.taro@example.com',
            'phone' => '090-1234-5678',
            'address' => '東京都仮新宿区西新宿1-1-1',
        ],
        [
            'name' => '仮鈴木花子',
            'email' => 'suzuki.hanako@example.com',
            'phone' => '090-8765-4321',
            'address' => '大阪府大阪市仮中央区心斎橋1-1-1',
        ],
        [
            'name' => '仮田中次郎',
            'email' => 'tanaka.jiro@example.com',
            'phone' => '090-5678-1234',
            'address' => '愛知県名古屋市仮中村区中村1-1-1',
        ],
    ];

    /**
     * 注文一覧を取得
     *
     * @param int $fromDays 取得開始日（n日前）
     * @param int $toDays 取得終了日（n日前）
     * @param int $limit 取得件数
     * @return Orders
     */
    public function getOrders(int $fromDays = 30, int $toDays = 0, int $limit = 10): Orders
    {
        // 日付順に並べるために、先に日付を生成してソート
        $dates = [];
        for ($i = 0; $i < $limit; $i++) {
            $dates[] = $this->generateRandomDate($fromDays, $toDays);
        }
        usort($dates, fn($a, $b) => $a <=> $b);

        // ソートされた日付を使って注文を生成
        $orders = [];
        foreach ($dates as $date) {
            $orderedAt = $date;
            $createdAt = new DateTimeImmutable();
            $updatedAt = new DateTimeImmutable();
            $orderId = $this->generateOrderId($orderedAt);
            $statuses = ['pending', 'unshipped'];
            $status = new OrderStatus($statuses[array_rand($statuses)]);

            $customerInfo = (mt_rand(1, 100) <= 10)
                ? $this->getRepeaterCustomerInfo()
                : $this->generateRandomCustomerInfo();

            $orderItems = $this->generateRandomOrderItems();
            $shippingFee = new ShippingFee($this->calculateShippingFee($orderItems));

            $orders[] = new Order(
                $orderId,
                new EcSiteCode('TEST-MALL'),
                $status,
                $orderedAt,
                $shippingFee,
                $customerInfo,
                $orderItems,
                $createdAt,
                $updatedAt
            );
        }

        return new Orders($orders);
    }

    /**
     * 注文IDを生成
     *
     * @param DateTimeImmutable $orderedAt
     * @return OrderId
     */
    private function generateOrderId(DateTimeImmutable $orderedAt): OrderId
    {
        $dateStr = $orderedAt->format('Ymd');

        // その日の注文数をカウントアップ
        if (!isset($this->orderCountByDate[$dateStr])) {
            $this->orderCountByDate[$dateStr] = 0;
        }
        $this->orderCountByDate[$dateStr]++;

        // 3桁の連番を生成
        $sequence = str_pad((string)$this->orderCountByDate[$dateStr], 3, '0', STR_PAD_LEFT);

        return new OrderId("Order-{$dateStr}-{$sequence}");
    }

    /**
     * ランダムな注文商品を生成
     *
     * @return OrderItems
     */
    private function generateRandomOrderItems(): OrderItems
    {
        $items = [];
        // 確率に基づいて商品数を決定
        $rand = mt_rand(1, 100);
        $numItems = match (true) {
            $rand <= 40 => 1,  // 40%
            $rand <= 70 => 2,  // 30%
            $rand <= 85 => 3,  // 15%
            $rand <= 95 => 4,  // 10%
            default    => 5,   // 5%
        };

        // 全商品のキーを取得
        $productKeys = array_keys($this->products);
        // キーをシャッフルして重複しないように選択
        shuffle($productKeys);
        // 商品数分だけキーを選択
        $selectedProducts = array_slice($productKeys, 0, $numItems);

        Log::channel('debug')->debug('after2 selectedProducts: ' . json_encode($selectedProducts));

        foreach ($selectedProducts as $productKey) {
            $product = $this->products[$productKey];
            $quantity = $this->determineQuantity($product['price']);

            $taxRate = $product['is_food'] ? 0.08 : 0.10; // 食品は8%、それ以外は10%
            $items[] = new OrderItem(
                new OrderItemId($productKey),
                new OrderItemName($product['name']),
                new OrderItemPrice($product['price'], $taxRate),
                $quantity
            );
        }
        Log::channel('debug')->debug('after2 selectedProducts: ' . var_export($items, true));

        return new OrderItems($items);
    }

    /**
     * 注文数量を決定
     *
     * @param int $price
     * @return int
     */
    private function determineQuantity(int $price): int
    {
        if ($price >= 10000) {
            return mt_rand(1, 2); // 高額商品は1-2個
        } elseif ($price >= 5000) {
            return mt_rand(1, 3); // 中額商品は1-3個
        } elseif ($price >= 500) {
            return mt_rand(1, 5); // 中額商品は1-5個
        } else {
            return mt_rand(1, 10); // 低額商品は1-10個
        }
    }

    /**
     * ランダムな顧客情報を生成
     *
     * @return OrderCustomerInfo
     */
    private function generateRandomCustomerInfo(): OrderCustomerInfo
    {
        $firstNames = [
            '太郎', '次郎', '三郎', '四郎', '五郎',
            '花子', '梅子', '桃子', '菊子', '椿子',
            '翔太', '健一', '大輔', '直樹', '剛',
            '美咲', 'sakura', '愛', '優子', '恵',
            '一郎', '二郎', '正男', '和夫', '勇',
            '京子', '幸子', '和子', '洋子', '裕子',
            '雄大', '翔', '大地', '海斗', '蓮',
            '結衣', '凛', '陽菜', '美羽', '心愛',
            'ヒロシ', 'タケシ', 'ケンジ', 'マサシ', 'ユウジ',
            'アキコ', 'ヨウコ', 'ナオコ', 'マリコ', 'サユリ'
        ];

        $lastNames = [
            '佐藤', '鈴木', '高橋', '田中', '渡辺',
            '伊藤', '山本', '中村', '小林', '加藤',
            '吉田', '山田', '佐々木', '山口', '松本',
            '井上', '木村', '林', '斎藤', '清水',
            '山崎', '森', '池田', '橋本', '阿部',
            '石川', '山下', '中島', '石井', '小川',
            '前田', '岡田', '長谷川', '藤田', '後藤',
            '近藤', '村上', '遠藤', '青木', '坂本',
            '斉藤', '福田', '太田', '西村', '藤井',
            '岡本', '金子', '藤原', '三浦', '中田',
            '中西', '原田', '松田', '竹内', '上田',
            '森田', '原', '柴田', '酒井', '工藤',
            // 地名由来
            '北村', '南', '東', '西山', '中山',
            '川村', '浜田', '上野', '吉野', '富士',
            // 職業由来
            '工藤', '大工', '農田', '漁野', '商店',
            // 自然由来
            '森本', '林田', '山岡', '川上', '浜崎',
            // 季節由来
            '春日', '夏目', '秋山', '冬木', '四季',
            // 色由来
            '赤松', '青山', '黒田', '白石', '緑川'
        ];

        $firstName = $firstNames[array_rand($firstNames)];
        $lastName = $lastNames[array_rand($lastNames)];
        $name = __('仮:lastName :firstName', ['lastName' => $lastName, 'firstName' => $firstName]);

        return new OrderCustomerInfo(
            $name,
            mb_strtolower($this->toRomaji($firstName)) . '.kari' . mb_strtolower($this->toRomaji($lastName)) . '@example.com',
            sprintf('0%d-1234-5678', random_int(1, 9)),
            sprintf(
                '〒%s-%s %s%s%s%d-%d-%d',
                str_pad((string)random_int(100, 999), 3, '0', STR_PAD_LEFT),
                str_pad((string)random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                ['東京都', '神奈川県', '埼玉県', '千葉県', '茨城県', '栃木県', '群馬県'][array_rand([0,1,2,3,4,5,6])],
                ['仮新宿区', '仮渋谷区', '仮品川区', '仮港区', '仮中央区', '仮横浜市', '仮さいたま市'][array_rand([0,1,2,3,4,5,6])],
                ['本町', '栄町', '中央', '駅前', '緑町'][array_rand([0,1,2,3,4])],
                random_int(1, 5),
                random_int(1, 20),
                random_int(1, 1000)
            )
        );
    }

    /**
     * 繰り返し顧客情報を取得
     *
     * @return OrderCustomerInfo
     */
    private function getRepeaterCustomerInfo(): OrderCustomerInfo
    {
        $repeater = $this->repeaterCustomers[array_rand($this->repeaterCustomers)];
        return new OrderCustomerInfo(
            $repeater['name'],
            $repeater['email'],
            $repeater['phone'],
            $repeater['address']
        );
    }

    /**
     * 送料を計算
     *
     * @param OrderItems $orderItems
     * @return int
     */
    private function calculateShippingFee(OrderItems $orderItems): int
    {
        $subtotal = $orderItems->getSubtotalWithTax();
        if ($subtotal >= 10000) {
            return 0; // 1万円以上は送料無料
        }
        return 800; // 通常送料
    }

    /**
     * ランダムな日付を生成
     *
     * @param int $fromDays
     * @param int $toDays
     * @return DateTimeImmutable
     */
    private function generateRandomDate(int $fromDays, int $toDays): DateTimeImmutable
    {
        $timestamp = mt_rand(strtotime("-$fromDays days"), strtotime("-$toDays days"));
        return new DateTimeImmutable("@$timestamp");
    }

    /**
     * ローマ字変換
     *
     * @param string $text
     * @return string
     */
    private function toRomaji(string $text): string
    {
        // 姓のローマ字変換
        $lastNameConversion = [
            // 一般的な姓
            '佐藤' => 'sato', '鈴木' => 'suzuki', '高橋' => 'takahashi',
            '田中' => 'tanaka', '渡辺' => 'watanabe', '伊藤' => 'ito',
            '山本' => 'yamamoto', '中村' => 'nakamura', '小林' => 'kobayashi',
            '加藤' => 'kato', '吉田' => 'yoshida', '山田' => 'yamada',
            '佐々木' => 'sasaki', '山口' => 'yamaguchi', '松本' => 'matsumoto',
            '井上' => 'inoue', '木村' => 'kimura', '林' => 'hayashi',
            '斎藤' => 'saito', '清水' => 'shimizu',

            // 地名由来
            '北村' => 'kitamura', '南' => 'minami', '東' => 'higashi',
            '西山' => 'nishiyama', '中山' => 'nakayama',
            '川村' => 'kawamura', '浜田' => 'hamada', '上野' => 'ueno',
            '吉野' => 'yoshino', '富士' => 'fuji',

            // 職業由来
            '工藤' => 'kudo', '大工' => 'daiku', '農田' => 'noda',
            '漁野' => 'ryono', '商店' => 'shoten',

            // 自然由来
            '森本' => 'morimoto', '林田' => 'hayashida', '山岡' => 'yamaoka',
            '川上' => 'kawakami', '浜崎' => 'hamasaki',

            // 季節由来
            '春日' => 'kasuga', '夏目' => 'natsume', '秋山' => 'akiyama',
            '冬木' => 'fuyuki', '四季' => 'shiki',

            // 色由来
            '赤松' => 'akamatsu', '青山' => 'aoyama', '黒田' => 'kuroda',
            '白石' => 'shiraishi', '緑川' => 'midorikawa'
        ];

        // 名のローマ字変換
        $firstNameConversion = [
            // 伝統的な名前
            '太郎' => 'taro', '次郎' => 'jiro', '三郎' => 'saburo',
            '四郎' => 'shiro', '五郎' => 'goro', '花子' => 'hanako',
            '梅子' => 'umeko', '桃子' => 'momoko', '菊子' => 'kikuko',
            '椿子' => 'tsubakiko',

            // 現代的な名前
            '翔太' => 'shota', '健一' => 'kenichi', '大輔' => 'daisuke',
            '直樹' => 'naoki', '剛' => 'tsuyoshi', '美咲' => 'misaki',
            'sakura' => 'sakura', '愛' => 'ai', '優子' => 'yuko',
            '恵' => 'megumi',

            // 一般的な名前
            '一郎' => 'ichiro', '二郎' => 'jiro', '正男' => 'masao',
            '和夫' => 'kazuo', '勇' => 'isamu', '京子' => 'kyoko',
            '幸子' => 'sachiko', '和子' => 'kazuko', '洋子' => 'yoko',
            '裕子' => 'yuko',

            // 現代風の名前
            '雄大' => 'yudai', '翔' => 'sho', '大地' => 'daichi',
            '海斗' => 'kaito', '蓮' => 'ren', '結衣' => 'yui',
            '凛' => 'rin', '陽菜' => 'hina', '美羽' => 'miu',
            '心愛' => 'kokoa',

            // カタカナ名
            'ヒロシ' => 'hiroshi', 'タケシ' => 'takeshi', 'ケンジ' => 'kenji',
            'マサシ' => 'masashi', 'ユウジ' => 'yuji', 'アキコ' => 'akiko',
            'ヨウコ' => 'yoko', 'ナオコ' => 'naoko', 'マリコ' => 'mariko',
            'サユリ' => 'sayuri'
        ];

        // 姓と名の変換テーブルを結合
        $conversion = array_merge($lastNameConversion, $firstNameConversion);

        return $conversion[$text] ?? 'dummy'; // 変換できない場合はデフォルト値として'dummy'を返す
    }
}
