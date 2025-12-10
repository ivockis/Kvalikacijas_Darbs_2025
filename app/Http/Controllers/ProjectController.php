<?php

// Verified update: 2025-12-08 15:15:00

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tool;
use App\Models\Project;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
     * Display a listing of all public projects.
     */
    public function publicIndex()
    {
        $projects = Project::where('is_public', true)->latest()->paginate(12);
        return view('public-projects', compact('projects'));
    }

    public function create()
    {
        $categories = Category::all();
        $tools = Tool::all();
        return view('projects.create', compact('categories', 'tools'));
    }

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
            'images' => 'required|array|min:1|max:10', // At least 1 image is required for cover
            'images.*' => 'mimes:jpeg,png,jpg',
            'cover_image_selection' => 'required_with:images|integer|min:0', // Index of the cover image in the 'images' array
        ]);

        $project = Auth::user()->projects()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'materials' => $validated['materials'] ?? null,
            'is_public' => $request->has('is_public'),
            'creation_time' => now(),
        ]);

        if (isset($validated['categories'])) {
            $project->categories()->sync($validated['categories']);
        }

        if (isset($validated['tools'])) {
            $project->tools()->sync($validated['tools']);
        }

        if ($request->hasFile('images')) {
            $imageIndex = 0;
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('project-images', 'public');
                $isCover = ($imageIndex == $validated['cover_image_selection']);
                $project->images()->create([
                    'path' => $path,
                    'is_cover' => $isCover,
                ]);
                $imageIndex++;
            }
        }

        return redirect(route('projects.index'))->with('status', 'Project created successfully!');
    }

    public function show(Project $project)
    {
        if (!$project->is_public && (!Auth::check() || (!Auth::user()->is_admin && Auth::user()->id !== $project->user_id))) {
            abort(403, 'Unauthorized access to this project.');
        }

        $project->load(['user', 'categories', 'tools', 'images', 'likers']);
        $liked = Auth::check() ? $project->likers->contains(Auth::user()->id) : false;

        return view('projects.show', compact('project', 'liked'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        $categories = Category::all();
        $tools = Tool::all();
        return view('projects.edit', compact('project', 'categories', 'tools'));
    }

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
            'images' => 'nullable|array|max:10',
            'images.*' => 'mimes:jpeg,png,jpg',
            'cover_image_selection' => 'required|string', // Can be 'new_X' or 'existing_Y'
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

        $newImagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('project-images', 'public');
                $projectImage = $project->images()->create([
                    'path' => $path,
                    'is_cover' => false, // Set to false initially, will be updated based on selection
                ]);
                $newImagePaths[] = $projectImage->id;
            }
        }

        // Handle cover image selection
        if ($request->filled('cover_image_selection')) {
            $selection = $request->input('cover_image_selection');
            if (str_starts_with($selection, 'new_')) {
                // Selected a newly uploaded image as cover
                $newImageIndex = (int) substr($selection, 4);
                if (isset($newImagePaths[$newImageIndex])) {
                    $newCoverImage = Image::find($newImagePaths[$newImageIndex]);
                    if ($newCoverImage) {
                        $project->setCoverImage($newCoverImage);
                    }
                }
            } elseif (str_starts_with($selection, 'existing_')) {
                // Selected an existing image as cover
                $existingImageId = (int) substr($selection, 9);
                $existingCoverImage = $project->images()->find($existingImageId);
                if ($existingCoverImage) {
                    $project->setCoverImage($existingCoverImage);
                }
            }
        } else {
            // If no cover is selected, and project has no cover, set the first existing image as cover
            if (!$project->images()->where('is_cover', true)->exists()) {
                $firstImage = $project->images()->first();
                if ($firstImage) {
                    $project->setCoverImage($firstImage);
                }
            }
        }

        return redirect(route('projects.index'))->with('status', 'Project updated successfully!');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return redirect(route('projects.index'))->with('status', 'Project deleted successfully!');
    }

    public function like(Project $project)
    {
        Auth::user()->likedProjects()->toggle($project->id);
        return back()->with('status', 'Project like status updated!');
    }
}
