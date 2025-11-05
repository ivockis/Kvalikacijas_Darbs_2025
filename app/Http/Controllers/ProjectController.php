<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tool;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProjectController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $projects = Auth::user()->projects()->latest()->get();
        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $tools = Tool::all();
        return view('projects.create', compact('categories', 'tools'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'materials' => 'nullable|string',
            'is_public' => '',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'tools' => 'nullable|array',
            'tools.*' => 'exists:tools,id',
        ]);

        $project = Auth::user()->projects()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'materials' => $validated['materials'] ?? null,
            'is_public' => $request->has('is_public'),
            'creation_time' => now(), // Automātiski iestatām izveides laiku
        ]);

        if (isset($validated['categories'])) {
            $project->categories()->sync($validated['categories']);
        }

        if (isset($validated['tools'])) {
            $project->tools()->sync($validated['tools']);
        }

        return redirect(route('projects.index'))->with('status', 'Project created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        $categories = Category::all();
        $tools = Tool::all();

        return view('projects.edit', compact('project', 'categories', 'tools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'materials' => 'nullable|string',
            'is_public' => '',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'tools' => 'nullable|array',
            'tools.*' => 'exists:tools,id',
        ]);

        $project->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'materials' => $validated['materials'] ?? null,
            'is_public' => $request->has('is_public'),
        ]);

        if (isset($validated['categories'])) {
            $project->categories()->sync($validated['categories']);
        } else {
            $project->categories()->detach();
        }

        if (isset($validated['tools'])) {
            $project->tools()->sync($validated['tools']);
        } else {
            $project->tools()->detach();
        }

        return redirect(route('projects.index'))->with('status', 'Project updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect(route('projects.index'))->with('status', 'Project deleted successfully!');
    }
}
