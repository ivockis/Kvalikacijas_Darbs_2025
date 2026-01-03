<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Controller responsible for managing project ratings.
 * Includes functionality for storing/updating and deleting ratings.
 */
class RatingController extends Controller
{
    use AuthorizesRequests; // Provides authorization methods, utilizing Policy classes.

    /**
     * Stores a newly created rating or updates an existing one in storage.
     *
     * @param Request $request The HTTP request containing the rating data.
     * @param Project $project The Project model for which the rating is being stored/updated.
     * @return \Illuminate\Http\RedirectResponse Redirects back to the previous page with a status message.
     */
    public function store(Request $request, Project $project)
    {
        // Prevents a user from rating their own project for fairness.
        if ($project->user_id === Auth::id()) {
            return back()->with('error', 'You cannot rate your own project.'); // Returns an error message.
        }

        // Validates the incoming request data.
        // The rating must be required, an integer, and between 1 and 5.
        $validated = $request->validate([
            'rating' => 'required|integer|between:1,5',
        ]);

        // Updates an existing rating or creates a new one.
        // The combination of 'user_id' and 'project_id' is used to find or create a unique record.
        // This ensures a single user can only submit one rating per project.
        $project->ratings()->updateOrCreate(
            ['user_id' => Auth::id()], // Criteria to find the record (current user's rating for this project).
            ['rating' => $validated['rating']] // Data to be updated or created.
        );

        // Redirects back to the previous page with a success status message.
        return back()->with('status', 'rating-saved');
    }

    /**
     * Removes the authenticated user's rating for the specified project.
     *
     * @param Project $project The Project model from which the rating is to be deleted.
     * @return \Illuminate\Http\RedirectResponse Redirects back to the previous page with a status message.
     */
    public function destroy(Project $project)
    {
        // Finds the current user's rating for this specific project.
        $rating = $project->ratings()->where('user_id', Auth::id())->first();

        // Checks if a rating exists and if the user is authorized to delete it.
        if ($rating) {
            $this->authorize('delete', $rating); // Authorizes the delete operation using RatingPolicy.
            $rating->delete(); // Deletes the rating.
        }

        // Redirects back to the previous page with a success status message.
        return back()->with('status', 'rating-removed');
    }
}
