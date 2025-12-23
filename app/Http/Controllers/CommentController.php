<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Added this import

class CommentController extends Controller
{
    use AuthorizesRequests; // Added this trait

    /**
     * Store a newly created comment in storage.
     * (This method might be redundant if comments are always submitted with ratings,
     * but keeping for potential separate comment submission or future use.)
     */
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $project->comments()->create([
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        return back()->with('status', 'comment-posted');
    }



    /**
     * Update the specified comment in storage.
     */
    public function update(Request $request, Comment $comment)
    {
        // If the request is specifically to toggle visibility
        if ($request->has('is_visible')) {
            $this->authorize('toggleVisibility', $comment);

            $validated = $request->validate([
                'is_visible' => 'required|boolean',
            ]);

            $comment->update(['is_visible' => $validated['is_visible']]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Comment visibility updated successfully!', 'comment' => $comment]);
            }
            return back()->with('status', 'comment-visibility-updated');
        } else {
            // Original logic for updating comment text
            $this->authorize('update', $comment);

            $validated = $request->validate([
                'comment' => 'required|string|max:2000',
            ]);

            $comment->update(['comment' => $validated['comment']]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Comment updated successfully!', 'comment' => $comment]);
            }

            return redirect()->route('projects.show', $comment->project)->with('status', 'comment-updated');
        }
    }

    /**
     * Remove the specified comment from storage.
     */
    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        
        $comment->delete();

        return back()->with('status', 'comment-deleted');
    }
}