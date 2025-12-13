<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ToolController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Tool::class);
        $tools = Tool::withCount('projects')->latest()->get();
        return view('tools.index', compact('tools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Tool::class);
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'comment' => 'nullable|string|max:50',
            'approved' => 'sometimes|boolean',
        ]);
        $tool = Tool::create($validated);
        // Load the projects_count for the new tool
        $tool->loadCount('projects');
        return response()->json($tool);
    }

    /**
     * Update the specified resource in storage.
     */
    public function destroy(Tool $tool)
    {
        $this->authorize('delete', $tool);

        if ($tool->projects()->exists()) {
            return response()->json(['message' => 'Cannot delete tool because it is attached to one or more projects.'], 422);
        }

        $tool->delete();
        return response()->json(['message' => 'Tool deleted successfully.']);
    }

    /**
     * Search for tools by name.
     */
    public function search(Request $request)
    {
        $this->authorize('viewAny', Tool::class); // User needs to be authorized to view tools
        $query = $request->input('query');
        $tools = Tool::where('name', 'like', "%{$query}%")
                     ->limit(10) // Limit results for performance
                     ->get(['id', 'name', 'comment']); // Select only necessary fields
        return response()->json($tools);
    }

    /**
     * Toggle the approval status of a tool.
     */
    public function toggleApproval(Tool $tool)
    {
        $this->authorize('update', $tool); // Reuse the update policy for approval
        $tool->approved = !$tool->approved;
        $tool->save();
        return response()->json($tool);
    }
}