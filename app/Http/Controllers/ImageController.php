<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ImageController extends Controller
{
    use AuthorizesRequests;

    public function destroy(Image $image)
    {
        $this->authorize('delete', $image);

        // Delete the image file from storage
        Storage::disk('public')->delete($image->path);

        // Delete the database record
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully.']);
    }

    public function setAsCover(Image $image)
    {
        $this->authorize('update', $image); // Use update policy for images to change its cover status

        // Ensure the image belongs to a project
        if (!$image->project) {
            return response()->json(['message' => 'Image does not belong to a project.'], 400);
        }

        // Use the Project model's setCoverImage method to handle the logic
        $image->project->setCoverImage($image);

        return response()->json(['message' => 'Cover image updated successfully.']);
    }
}
