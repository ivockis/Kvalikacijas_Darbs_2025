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
            'Woodworking', 'Jewelry Making', 'Knitting', 'Crochet', 'Painting',
            'Sculpting', 'Pottery', 'Sewing', 'Embroidery', 'Quilting',
            'Origami', 'Calligraphy', 'Candle Making', 'Soap Making', 'Leather Craft',
            'Paper Craft', 'Glass Blowing', 'Metalwork', 'Weaving', 'Photography',
            'Digital Art', 'Baking', 'Gardening', 'Home Decor', 'Fashion Design', 'For Kids',
        ];

        foreach ($categories as $category) {
            Category::factory()->create(['name' => $category]);
        }
    }
}