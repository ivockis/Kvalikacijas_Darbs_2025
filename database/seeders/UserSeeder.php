<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Image;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $images = Image::all();

        $this->command->info('Seeding Users...');

        $userData = [
            ['name' => 'Dans', 'surname' => 'Ozols', 'username' => 'dans', 'email' => 'dans@example.com', 'isAdmin' => true, 'isBlocked' => false],
            ['name' => 'Enriko', 'surname' => 'Liepins', 'username' => 'enriko', 'email' => 'enriko@example.com', 'isAdmin' => false, 'isBlocked' => false],
            ['name' => 'Jana', 'surname' => 'Berzina', 'username' => 'jana', 'email' => 'jana@example.com', 'isAdmin' => false, 'isBlocked' => false],
            ['name' => 'Juta', 'surname' => 'Kalnina', 'username' => 'juta', 'email' => 'juta@example.com', 'isAdmin' => false, 'isBlocked' => false],
            ['name' => 'Marta', 'surname' => 'Saulite', 'username' => 'marta', 'email' => 'marta@example.com', 'isAdmin' => false, 'isBlocked' => false],
            ['name' => 'Vilnis', 'surname' => 'Eglitis', 'username' => 'vilnis', 'email' => 'vilnis@example.com', 'isAdmin' => false, 'isBlocked' => true],
        ];

        foreach ($userData as $ud) {
            $user = User::factory()->create([
                'name' => $ud['name'],
                'surname' => $ud['surname'],
                'username' => $ud['username'],
                'email' => $ud['email'],
                'password' => Hash::make('Password123'),
                'is_admin' => $ud['isAdmin'],
                'is_blocked' => $ud['isBlocked'],
            ]);

            $image = $images->firstWhere(fn($img) => Str::contains(mb_strtolower($img->path), mb_strtolower('user_' . $ud['name'])));
            if ($image) {
                $user->profile_image_id = $image->id;
                $user->save();
            }
        }
        
        // Create 2 more users without projects or specific images
        User::factory(2)->create();
    }
}