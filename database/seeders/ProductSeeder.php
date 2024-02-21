<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Stock;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $products = [];
        $stocks = [];

        for ($i = 1; $i <= 11; $i++) {
            $product_name = $faker->unique()->word();
            $slug = Str::slug($product_name);
            $minimum_qty = $faker->numberBetween(0, 100);
            $qty = $faker->numberBetween(0, 100);

            $inStock = $qty > $minimum_qty ? true : false;

            $products[] = [
                'product_name' => $product_name,
                'product_code' => 'PRD-' . $i,
                'slug' => $slug,
                'minimum_qty' => $minimum_qty,
                'inStock' => $inStock, 
            ];

            $stocks[] = [
                'product_id' => $i,
                'qty' => $qty,
            ];
        };

        Product::insert($products);
        Stock::insert($stocks);
    }
}
