<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    /**
     * Store a newly created complaint in storage.
     */
    public function store(Request $request, Project $project)
    {
        // Authorization: A user cannot complain about their own project.
        if ($project->user_id === Auth::id()) {
            return back()->with('error', 'You cannot report your own project.');
        }

        // Check for existing complaint
        $existingComplaint = Complaint::where('user_id', Auth::id())
                                      ->where('project_id', $project->id)
                                      ->exists();

        if ($existingComplaint) {
            return back()->with('error', 'You have already submitted a report for this project.');
        }

        // Validation
        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:1000',
        ]);

        // Create the complaint
        $project->complaints()->create([
            'user_id' => Auth::id(),
            'reason' => $validated['reason'],
            'status' => 'pending', // Default status
        ]);

        return back()->with('status', 'complaint-submitted');
    }
}