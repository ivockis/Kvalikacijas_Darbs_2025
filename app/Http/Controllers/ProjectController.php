<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tool;
use App\Models\Project;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    use AuthorizesRequests;

    public function publicIndex(Request $request)
    {
        $categories = Category::all(); // Get all categories for the filter dropdown

        $query = Project::select('projects.*') // Explicitly select all project columns to avoid SQLSTATE[42000] error
                        ->where('is_public', true)
                        ->where('is_blocked', false)
                        ->with('user')
                        ->withCount('likers') // Count the number of likers
                        ->withCount('ratings') // Count the number of ratings
                        ->withAvg('ratings', 'rating') // Calculate the average rating
                        ->groupBy('projects.id'); // Group by project to use having for average rating

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        // Apply category filter
        if ($categoryId = $request->input('category_id')) {
            if ($categoryId === 'liked') {
                if (Auth::check()) {
                    $query->whereHas('likers', function ($q) {
                        $q->where('user_id', Auth::id());
                    });
                } else {
                    // If not authenticated, 'liked' filter means nothing,
                    // so return no projects or handle as appropriate.
                    // For now, let's just make it return no projects.
                    $query->whereRaw('1 = 0'); // Always false condition
                }
            } else {
                $query->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('categories.id', $categoryId);
                });
            }
        }

        // Apply minimum rating filter
        if ($minRating = $request->input('min_rating')) {
            $query->having('ratings_avg_rating', '>=', $minRating)
                  ->having('ratings_count', '>', 0); // Only show projects with at least one rating
        }

        // Apply sorting
        switch ($request->input('sort_by')) {
            case 'oldest':
                $query->oldest('projects.created_at'); // Specify table for clarity
                break;
            case 'estimated_hours_asc':
                $query->orderBy('estimated_hours', 'asc');
                break;
            case 'estimated_hours_desc':
                $query->orderBy('estimated_hours', 'desc');
                break;
            case 'most_liked':
                $query->orderByDesc('likers_count');
                break;
            case 'highest_rated':
                $query->orderByDesc('ratings_avg_rating')->having('ratings_count', '>', 0); // Order by average rating
                break;
            case 'most_rated':
                $query->orderByDesc('ratings_count'); // Order by number of ratings
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            default: // latest
                $query->latest('projects.created_at'); // Specify table for clarity
                break;
        }

        $perPage = $request->input('per_page', 25);
        if ($perPage === 'all') {
            $perPage = 999; // A large number to simulate "all"
        }

        $projects = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('projects._public-project-list-dynamic', compact('projects', 'categories'))->render();
        }

        return view('public-projects', compact('projects', 'categories'));
    }

    public function index(Request $request)
    {
        $categories = Category::all(); // Get all categories for the filter dropdown

        $query = Auth::user()->projects()->select('projects.*') // Explicitly select all project columns to avoid SQLSTATE[42000] error
                        ->withCount('ratings') // Count the number of ratings
                        ->withAvg('ratings', 'rating') // Calculate the average rating
                        ->withCount('likers') // Count the number of likers
                        ->groupBy('projects.id'); // Group by project to use having for average rating

        // Apply search filter
        if ($search = $request->input('search')) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        // Apply category filter
        if ($categoryId = $request->input('category_id')) {
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        // Apply minimum rating filter
        if ($minRating = $request->input('min_rating')) {
            $query->having('ratings_avg_rating', '>=', $minRating)
                  ->having('ratings_count', '>', 0); // Only show projects with at least one rating
        }

        // Apply sorting
        switch ($request->input('sort_by')) {
            case 'oldest':
                $query->oldest('projects.created_at');
                break;
            case 'estimated_hours_asc':
                $query->orderBy('estimated_hours', 'asc');
                break;
            case 'estimated_hours_desc':
                $query->orderBy('estimated_hours', 'desc');
                break;
            case 'most_liked':
                $query->orderByDesc('likers_count');
                break;
            case 'highest_rated':
                $query->orderByDesc('ratings_avg_rating')->having('ratings_count', '>', 0); // Order by average rating
                break;
            case 'most_rated':
                $query->orderByDesc('ratings_count'); // Order by number of ratings
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc');
                break;
            default: // latest
                $query->latest('projects.created_at');
                break;
        }

        $perPage = $request->input('per_page', 25);
        if ($perPage === 'all') {
            $perPage = 999; // A large number to simulate "all"
        }

        $projects = $query->paginate($perPage)->withQueryString();

        if ($request->ajax()) {
            return view('projects._my-project-list-dynamic', compact('projects', 'categories'))->render();
        }

        return view('projects.index', compact('projects', 'categories'));
    }

    public function create()
    {
        $categories = Category::all()->map(function ($category) {
            $category->name = __($category->name);
            return $category;
        });
        $tools = Tool::all();
        return view('projects.create', compact('categories', 'tools'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:100',
                Rule::unique('projects')->where('user_id', Auth::id()),
            ],
            'description' => 'required|string',
            'materials' => 'required|string',
            'estimated_hours' => 'required|integer|min:1|max:1000',
            'is_public' => '',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
            'tools' => 'nullable|array',
            'tools.*' => 'exists:tools,id',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'mimes:jpeg,png,jpg',
        ], [
            'title.unique' => __('You already have a registered project with this name.'),
        ]);

        $project = Auth::user()->projects()->create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'materials' => $validated['materials'],
            'is_public' => $request->has('is_public'),
            'estimated_hours' => $validated['estimated_hours'],
        ]);

        if (isset($validated['categories'])) {
            $project->categories()->sync($validated['categories']);
        }

        if (isset($validated['tools'])) {
            $project->tools()->sync($validated['tools']);
        }

        if ($request->hasFile('images')) {
            $coverIndex = 0; // Default to the first image
            if ($request->filled('cover_image_selection')) {
                // 'new_2' -> '2'
                $coverIndex = (int) str_replace('new_', '', $request->input('cover_image_selection'));
            }
            
            foreach ($request->file('images') as $index => $imageFile) {
                $path = $imageFile->store('project-images', 'public');
                $project->images()->create([
                    'path' => $path,
                    'is_cover' => ($index === $coverIndex)
                ]);
            }
        }

        return redirect(route('projects.index'))->with('status', 'project-created');
    }

    public function show(Project $project)
    {
        if (!$project->is_public && (!Auth::check() || (!Auth::user()->is_admin && Auth::user()->id !== $project->user_id))) {
            abort(403, 'Unauthorized access to this project.');
        }

        $project->load(['user', 'categories', 'tools', 'images', 'likers']);
        $liked = Auth::check() ? $project->likers->contains(Auth::user()->id) : false;
        
        $hasComplained = false;
        if (Auth::check()) {
            $hasComplained = $project->complaints()->where('user_id', Auth::id())->exists();
        }

        // --- RATINGS AND COMMENTS ---
        $project->load([
            'comments' => function ($query) {
                $query->with('user.profileImage')->latest();
            },
            'ratings'
        ]);

        $comments = $project->comments;
        $ratings = $project->ratings;

        $averageRating = $ratings->avg('rating');
        $ratingsCount = $ratings->count();

        $userRating = null;
        if (Auth::check()) {
            $userRating = $ratings->firstWhere('user_id', Auth::id());
        }

        // The 'userComment' variable is intentionally omitted here.
        // The create form in the blade view should not be pre-filled with an existing comment,
        // as a user can have multiple comments. The blade will be adjusted separately to handle this.
        $userComment = null; 

        return view('projects.show', compact(
            'project',
            'liked',
            'hasComplained',
            'comments',
            'averageRating',
            'ratingsCount',
            'userRating',
            'userComment'
        ));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        $categories = Category::all()->map(function ($category) {
            $category->name = __($category->name);
            return $category;
        });
        $tools = Tool::all();
        $project->load('categories'); // Make sure categories are loaded
        $project->categories->transform(function ($category) {
            $category->name = __($category->name);
            return $category;
        });
        return view('projects.edit', compact('project', 'categories', 'tools'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:100',
                Rule::unique('projects')->where('user_id', Auth::id())->ignore($project->id),
            ],
            'description' => 'required|string',
            'materials' => 'required|string',
            'estimated_hours' => 'required|integer|min:1|max:1000',
            'is_public' => '',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
            'tools' => 'nullable|array',
            'tools.*' => 'exists:tools,id',
            'images' => 'nullable|array|max:10',
            'images.*' => 'mimes:jpeg,png,jpg',
            'cover_image_selection' => 'nullable|string',
        ], [
            'title.unique' => __('You already have a registered project with this name.'),
        ]);

        $project->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'materials' => $validated['materials'],
            'is_public' => $request->has('is_public'),
            'estimated_hours' => $validated['estimated_hours'],
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

        $newImageIds = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $path = $imageFile->store('project-images', 'public');
                $projectImage = $project->images()->create([
                    'path' => $path,
                    'is_cover' => false,
                ]);
                $newImageIds[] = $projectImage->id;
            }
        }

        // Handle cover image selection
        if ($request->filled('cover_image_selection')) {
            $selection = $request->input('cover_image_selection');

            if (str_starts_with($selection, 'new_')) {
                // A newly uploaded image was selected as cover
                $newImageIndex = (int) substr($selection, 4);
                if (isset($newImageIds[$newImageIndex])) {
                    $newCoverImageId = $newImageIds[$newImageIndex];
                    $newCoverImage = Image::find($newCoverImageId);
                    if ($newCoverImage) {
                        $project->setCoverImage($newCoverImage); // This part was already working
                    }
                }
            } elseif (str_starts_with($selection, 'existing_')) {
                // An existing image was selected as cover
                $existingImageId = (int) substr($selection, 9);
                
                \Illuminate\Support\Facades\DB::transaction(function () use ($project, $existingImageId) {
                    // 1. Unset all images for this project as cover
                    $project->images()->update(['is_cover' => false]);
                    
                    // 2. Set the selected existing image as the new cover
                    $newCover = $project->images()->find($existingImageId);
                    if ($newCover) {
                        $newCover->is_cover = true;
                        $newCover->save();
                    }
                });
            }
        }

        return redirect(route('projects.index'))->with('status', 'project-updated');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        $project->delete();
        return redirect(route('projects.index'))->with('status', 'project-deleted');
    }

    public function like(Project $project)
    {
        $user = Auth::user();
        $user->likedProjects()->toggle($project->id);

        return response()->json([
            'liked' => $user->likedProjects()->where('project_id', $project->id)->exists(),
            'likes_count' => $project->likers()->count()
        ]);
    }
}