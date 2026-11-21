<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\KhataService;
use App\Services\LowStockService;
use App\Services\SaleService;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Yasir Rahman',
            'email' => 'demo@khata.test',
            'password' => 'password',
            'store_name' => 'Rahman General Store',
            'store_phone' => '0345-1234567',
            'store_address' => 'Main Bazaar, Batkhela, Malakand',
            'reminder_after_days' => 7,
            'sms_enabled' => true,
        ]);

        $customers = collect([
            ['Ali Raza', '0300-1234567', 0],
            ['Sara Bibi', '0311-2345678', 1500],
            ['Muhammad Kashif', '0333-3456789', 0],
            ['Noor Ul Ain', '0344-4567890', 800],
            ['Fazal Rahman', '0301-5678901', 0],
            ['Hina Gul', '0312-6789012', 0],
            ['Imran Khan', '0334-7890123', 2200],
            ['Zubair Ahmed', '0345-8901234', 0],
            ['Ayesha Siddiqui', '0302-9012345', 0],
            ['Rashid Ali', '0313-0123456', 600],
            ['Sana Tariq', '0335-1122334', 0],
            ['Waqas Khan', '0346-2233445', 0],
        ])->map(fn (array $row) => Customer::create([
            'user_id' => $user->id,
            'name' => $row[0],
            'phone' => $row[1],
            'opening_balance' => $row[2],
            'address' => 'Batkhela, Malakand',
            'is_active' => true,
        ]));

        $products = collect([
            ['Atta 10kg', 'bag', 1250, 1350, 60, 6],
            ['Sugar 1kg', 'kg', 145, 160, 90, 12],
            ['Cooking Oil 1L', 'litre', 560, 620, 48, 8],
            ['Tea 250g', 'packet', 480, 540, 55, 10],
            ['Rice Sella 1kg', 'kg', 280, 320, 110, 15],
            ['Milk 1L', 'litre', 240, 260, 72, 12],
            ['Eggs (dozen)', 'dozen', 320, 360, 36, 6],
            ['Soap', 'piece', 85, 100, 120, 20],
            ['Surf 500g', 'packet', 330, 380, 42, 8],
            ['Daal Chana 1kg', 'kg', 260, 300, 65, 10],
            ['Maida 1kg', 'kg', 120, 140, 70, 12],
            ['Biscuits', 'packet', 45, 60, 140, 24],
            ['Cold Drink 1.5L', 'bottle', 140, 170, 60, 12],
            ['Salt 800g', 'packet', 55, 70, 80, 15],
            ['Shampoo 200ml', 'bottle', 310, 360, 30, 6],
            ['Detergent Bar', 'piece', 40, 55, 100, 18],
            ['Ghee 1kg', 'kg', 620, 690, 28, 5],
            ['Shan Masala', 'packet', 90, 120, 75, 12],
        ])->map(fn (array $row, int $index) => Product::create([
            'user_id' => $user->id,
            'name' => $row[0],
            'unit' => $row[1],
            'purchase_price' => $row[2],
            'sale_price' => $row[3],
            'stock_qty' => $row[4],
            'low_stock_threshold' => $row[5],
            'sku' => 'SKU-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
            'is_active' => true,
        ]));

        $saleService = app(SaleService::class);
        $khata = app(KhataService::class);

        for ($i = 0; $i < 140; $i++) {
            $picked = $products->random(rand(1, 3));
            $isUdhaar = rand(1, 100) <= 45;

            $items = $picked->map(function (Product $product) {
                return [
                    'product_id' => $product->id,
                    'quantity' => rand(1, 3),
                    'unit_price' => (float) $product->sale_price,
                ];
            })->all();

            try {
                $saleService->createSale($user, [
                    'items' => $items,
                    'payment_method' => $isUdhaar ? Sale::PAYMENT_UDHAAR : Sale::PAYMENT_CASH,
                    'customer_id' => $isUdhaar ? $customers->random()->id : null,
                    'discount' => rand(1, 100) <= 20 ? rand(10, 60) : 0,
                    'sale_date' => now()->subDays(rand(0, 44))->setTime(rand(9, 20), rand(0, 59)),
                ]);
            } catch (\Throwable) {
                // Stock can run out during random seeding; skip that sale.
            }
        }

        foreach ($customers as $customer) {
            $balance = $customer->calculateBalance();

            for ($i = 0; $i < 3 && $balance > 250; $i++) {
                $amount = round(min($balance, rand(250, 2500)), 2);

                $khata->postEntry($customer, [
                    'type' => LedgerEntry::TYPE_PAYMENT,
                    'amount' => $amount,
                    'entry_date' => now()->subDays(rand(0, 20))->toDateString(),
                    'description' => 'Cash received',
                ]);

                $balance -= $amount;
            }
        }

        // A couple of deliberately low items so the alert demo always has data.
        $products->firstWhere('name', 'Sugar 1kg')->forceFill(['stock_qty' => 4])->save();
        $products->firstWhere('name', 'Milk 1L')->forceFill(['stock_qty' => 5])->save();
        $products->firstWhere('name', 'Ghee 1kg')->forceFill(['stock_qty' => 2])->save();

        $lowStock = app(LowStockService::class);

        Product::where('user_id', $user->id)->get()->each(
            fn (Product $product) => $lowStock->evaluate($product)
        );
    }
}
