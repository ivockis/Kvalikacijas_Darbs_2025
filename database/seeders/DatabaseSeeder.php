<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            ToolSeeder::class,
            ImageSeeder::class,
            UserSeeder::class,
            ProjectSeeder::class, // Must be last, as it depends on all others
        ]);
    }
}
