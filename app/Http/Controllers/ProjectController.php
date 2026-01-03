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

/**
 * Controller responsible for managing project-related operations.
 * This includes displaying projects, handling CRUD operations,
 * managing likes, and handling image uploads.
 */
class ProjectController extends Controller
{
    use AuthorizesRequests; // Enables authorization checks using policies.

    /**
     * Displays a list of public projects with filtering, sorting, and pagination.
     * This method is accessible to all users, including guests.
     *
     * @param Request $request The HTTP request, potentially containing search, filter, and sort parameters.
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse Returns a view with projects or JSON for AJAX requests.
     */
    public function publicIndex(Request $request)
    {
        // Fetches all categories to populate the filter dropdown in the view.
        $categories = Category::all();

        // Initializes a query builder for Project models.
        // Explicitly selects 'projects.*' to avoid potential column ambiguity issues
        // when using aggregate functions and grouping.
        $query = Project::select('projects.*')
                        ->where('is_public', true)  // Only show projects marked as public.
                        ->where('is_blocked', false) // Exclude blocked projects.
                        ->with('user')              // Eager load the associated user for each project to prevent N+1 query problem.
                        ->withCount('likers')       // Adds a 'likers_count' column to the results, counting users who liked the project.
                        ->withCount('ratings')      // Adds a 'ratings_count' column, counting how many ratings each project has.
                        ->withAvg('ratings', 'rating') // Adds a 'ratings_avg_rating' column, calculating the average rating.
                        ->groupBy('projects.id');   // Groups results by project ID to allow `having` clauses for aggregates.

        // Applies a search filter based on the project title.
        if ($search = $request->input('search')) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        // Applies a category filter.
        if ($categoryId = $request->input('category_id')) {
            // Checks for a special 'liked' category ID for authenticated users.
            if ($categoryId === 'liked') {
                if (Auth::check()) {
                    // Filters to show only projects liked by the current authenticated user.
                    $query->whereHas('likers', function ($q) {
                        $q->where('user_id', Auth::id());
                    });
                } else {
                    // If not authenticated, the 'liked' filter is effectively meaningless,
                    // so an always-false condition is applied to return no projects.
                    $query->whereRaw('1 = 0');
                }
            } else {
                // Filters projects based on selected category ID.
                $query->whereHas('categories', function ($q) use ($categoryId) {
                    $q->where('categories.id', $categoryId);
                });
            }
        }

        // Applies a minimum average rating filter.
        if ($minRating = $request->input('min_rating')) {
            $query->having('ratings_avg_rating', '>=', $minRating) // Filters projects with an average rating greater than or equal to `minRating`.
                  ->having('ratings_count', '>', 0);               // Ensures only projects with at least one rating are considered.
        }

        // Applies sorting based on user's selection.
        switch ($request->input('sort_by')) {
            case 'oldest':
                $query->oldest('projects.created_at'); // Sorts by oldest projects first.
                break;
            case 'estimated_hours_asc':
                $query->orderBy('estimated_hours', 'asc'); // Sorts by estimated hours in ascending order.
                break;
            case 'estimated_hours_desc':
                $query->orderBy('estimated_hours', 'desc'); // Sorts by estimated hours in descending order.
                break;
            case 'most_liked':
                $query->orderByDesc('likers_count'); // Sorts by the number of likes in descending order.
                break;
            case 'highest_rated':
                $query->orderByDesc('ratings_avg_rating')->having('ratings_count', '>', 0); // Sorts by highest average rating.
                break;
            case 'most_rated':
                $query->orderByDesc('ratings_count'); // Sorts by the number of ratings in descending order.
                break;
            case 'title_asc':
                $query->orderBy('title', 'asc'); // Sorts by title in ascending alphabetical order.
                break;
            case 'title_desc':
                $query->orderBy('title', 'desc'); // Sorts by title in descending alphabetical order.
                break;
            default: // Default sorting is by latest created projects.
                $query->latest('projects.created_at');
                break;
        }

        // Determines the number of projects per page for pagination.
        $perPage = $request->input('per_page', 25);
        if ($perPage === 'all') {
            $perPage = 999; // A large number to simulate "all" projects if requested.
        }

        // Executes the query and paginates the results, preserving query string parameters.
        $projects = $query->paginate($perPage)->withQueryString();

        // Handles AJAX requests for dynamic content loading.
        if ($request->ajax()) {
            return view('projects._public-project-list-dynamic', compact('projects', 'categories'))->render();
        }

        // Returns the main public projects view with the fetched data.
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