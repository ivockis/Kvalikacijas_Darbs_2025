<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RatingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Store a newly created or updated rating in storage.
     */
    public function store(Request $request, Project $project)
    {
        // Prevent user from rating their own project
        if ($project->user_id === Auth::id()) {
            return back()->with('error', 'You cannot rate your own project.');
        }

        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
        ]);

        // Update or create rating
        $project->ratings()->updateOrCreate(
            ['user_id' => Auth::id()],
            ['rating' => $validated['rating']]
        );

        return back()->with('status', 'Your rating has been saved successfully!');
    }

    /**
     * Remove the authenticated user's rating for the specified project.
     */
    public function destroy(Project $project)
    {
        // Find the rating by the current user for this project
        $rating = $project->ratings()->where('user_id', Auth::id())->first();

        // Authorize deletion of the rating
        if ($rating) {
            $this->authorize('delete', $rating); // This will use the RatingPolicy
            $rating->delete();
        }

        return back()->with('status', 'Your rating has been removed.');
    }
}