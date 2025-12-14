<?php

namespace Database\Seeders;

use App\Models\Image;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sourcePath = base_path('database/seeders/Users_and_projects');
        $destinationBase = 'seeded_images'; // Path within the public disk

        // Ensure destination directory exists and is clean
        Storage::disk('public')->deleteDirectory($destinationBase);
        Storage::disk('public')->makeDirectory($destinationBase);

        if (File::isDirectory($sourcePath)) {
            $files = File::files($sourcePath);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                $destinationPath = $destinationBase . '/' . $filename;

                // Copy file to public disk
                Storage::disk('public')->put($destinationPath, File::get($file->getRealPath()));

                // Create an image record with only the path.
                // project_id and user_id will be associated later.
                Image::create([
                    'path' => $destinationPath,
                ]);
            }
        }
    }
}