<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'categories.woodworking', 'categories.jewelry_making', 'categories.knitting', 'categories.crochet', 'categories.painting',
            'categories.sculpting', 'categories.pottery', 'categories.sewing', 'categories.embroidery', 'categories.quilting',
            'categories.origami', 'categories.calligraphy', 'categories.candle_making', 'categories.soap_making', 'categories.leather_craft',
            'categories.paper_craft', 'categories.glass_blowing', 'categories.metalwork', 'categories.weaving', 'categories.photography',
            'categories.digital_art', 'categories.baking', 'categories.gardening', 'categories.home_decor', 'categories.fashion_design', 'categories.for_kids',
        ];

        foreach ($categories as $category) {
            Category::factory()->create(['name' => $category]);
        }
    }
}
