<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Complaint;
use Illuminate\Http\Request;

/**
 * Controller responsible for administrative tasks within the application.
 * This includes managing users (blocking, assigning admin roles) and
 * moderating projects (blocking projects, reviewing complaints).
 */
class AdminController extends Controller
{
    /**
     * Displays the user management view, allowing filtering, searching, and sorting of users.
     * Only accessible to administrators.
     *
     * @param Request $request The HTTP request, containing search, filter, and sort parameters.
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse Returns a view with users data or JSON response for AJAX.
     */
    public function usersIndex(Request $request)
    {
        // Initializes a query to fetch users, excluding the currently authenticated user
        // (to prevent an admin from modifying their own status via this interface).
        $query = User::where('id', '!=', auth()->id());

        // Applies search functionality: searches by username or email.
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Applies filtering based on user status (blocked, admin, active).
        if ($status = $request->input('status')) {
            if ($status === 'blocked') {
                $query->where('is_blocked', true);
            } elseif ($status === 'admin') {
                $query->where('is_admin', true);
            } elseif ($status === 'active') { // Filter for active (not blocked) users.
                $query->where('is_blocked', false);
            }
        }

        // Applies sorting based on specified column and order.
        $sortBy = $request->input('sort_by', 'created_at'); // Default sort by creation date.
        $sortOrder = $request->input('sort_order', 'desc'); // Default sort in descending order.
        $query->orderBy($sortBy, $sortOrder);

        // Paginates the results.
        $perPage = $request->input('per_page', 10); // Default 10 users per page.
        $users = $query->paginate($perPage)->withQueryString(); // Preserves query parameters across pagination links.

        // Returns JSON response if requested (e.g., for AJAX tables).
        if ($request->wantsJson()) {
            return $users;
        }

        // Returns the user management view.
        return view('admin.users.index', compact('users'));
    }

    /**
     * Toggles the `is_blocked` status of a specific user.
     * Only accessible to administrators.
     *
     * @param User $user The User model to be updated.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response with status message.
     */
    public function toggleBlock(User $user)
    {
        // Safeguard: Prevents an administrator from blocking their own account.
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot block yourself.'], 403);
        }
        
        // Toggles the `is_blocked` boolean status.
        $user->is_blocked = !$user->is_blocked;
        $user->save(); // Saves the changes to the database.

        // Returns a JSON response indicating success and the new blocking status.
        return response()->json(['message' => 'User status updated.', 'is_blocked' => $user->is_blocked]);
    }

    /**
     * Toggles the `is_admin` status of a specific user.
     * Only accessible to administrators.
     *
     * @param User $user The User model to be updated.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response with status message.
     */
    public function toggleAdmin(User $user)
    {
        // Safeguard: Prevents an administrator from revoking their own admin status.
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot change your own admin status.'], 403);
        }

        // Toggles the `is_admin` boolean status.
        $user->is_admin = !$user->is_admin;
        $user->save(); // Saves the changes to the database.

        // Returns a JSON response indicating success and the new admin status.
        return response()->json(['message' => 'User admin status updated.', 'is_admin' => $user->is_admin]);
    }

    /**
     * Displays the project management view, allowing filtering, searching, and sorting of projects.
     * Only accessible to administrators.
     *
     * @param Request $request The HTTP request, potentially containing search, filter, and sort parameters.
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse Returns a view with projects data or JSON response for AJAX.
     */
    public function projectsIndex(Request $request)
    {
        // Initializes a query builder for Project models.
        // Eager loads the associated user for each project.
        // Adds counts for total and pending complaints.
        $query = Project::where('is_public', true) // Typically admin manages public projects or all projects, adjusted to public based on code
                        ->with('user')
                        ->withCount('complaints as total_complaints_count')
                        ->withCount(['complaints as pending_complaints_count' => function ($query) {
                            $query->where('status', 'pending');
                        }]);

        // Applies search functionality: searches by project title or associated username.
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('username', 'like', '%' . $search . '%');
                  });
            });
        }

        // Applies filtering based on project status or complaint status.
        $status = $request->input('status');

        // Default filter projects with pending complaints if no status is specified for non-AJAX requests.
        if ($status === null && !$request->wantsJson()) {
            $status = 'pending_complaints';
        }

        if ($status === 'blocked') {
            $query->where('is_blocked', true);
        } elseif ($status === 'active') {
            $query->where('is_blocked', false);
        } elseif ($status === 'has_complaints') {
            $query->whereHas('complaints'); // Projects with any complaints.
        } elseif ($status === 'pending_complaints') {
            $query->whereHas('complaints', function ($q) {
                $q->where('status', 'pending'); // Projects with pending complaints.
            });
        } elseif ($status === 'resolved_complaints') {
            // Projects with complaints, none of which are pending (meaning all are resolved/declined).
            $query->whereDoesntHave('complaints', function ($q) {
                $q->where('status', 'pending');
            })->whereHas('complaints');
        }

        // Applies sorting based on specified column and order.
        $sortBy = $request->input('sort_by', 'created_at'); // Default sort by creation date.
        $sortOrder = $request->input('sort_order', 'desc'); // Default sort in descending order.
        if ($sortBy === 'total_complaints_count' || $sortBy === 'pending_complaints_count') {
            $query->orderBy($sortBy, $sortOrder); // Sorts by complaint counts.
        } else {
            $query->orderBy($sortBy, $sortOrder); // Sorts by other specified columns.
        }

        // Paginates the results.
        $perPage = $request->input('per_page', 10); // Default 10 projects per page.
        $projects = $query->paginate($perPage)->withQueryString(); // Preserves query parameters across pagination links.

        // Returns JSON response if requested.
        if ($request->wantsJson()) {
            return $projects;
        }

        // Returns the project management view.
        return view('admin.projects.index', compact('projects'));
    }

    /**
     * Toggles the `is_blocked` status of a specific project.
     * Only accessible to administrators.
     *
     * @param Project $project The Project model to be updated.
     * @return \Illuminate\Http\JsonResponse Returns a JSON response with status message.
     */
    public function toggleProjectBlock(Project $project)
    {
        // Toggles the `is_blocked` boolean status.
        $project->is_blocked = !$project->is_blocked;
        $project->save(); // Saves the changes to the database.

        // Returns a JSON response indicating success and the new blocking status.
        return response()->json(['message' => 'Project status updated.', 'is_blocked' => $project->is_blocked]);
    }

    /**
     * Displays all complaints for a specific project, categorized by pending and resolved.
     * Only accessible to administrators.
     *
     * @param Project $project The Project model for which to show complaints.
     * @return \Illuminate\View\View Returns a view displaying the project's complaints.
     */
    public function showProjectComplaints(Project $project)
    {
        // Eager loads complaints and the user who made each complaint.
        $project->load(['complaints.user']);
        
        // Partitions complaints into two collections: pending and resolved (approved/declined).
        [$pendingComplaints, $resolvedComplaints] = $project->complaints->partition(function ($complaint) {
            return $complaint->status === 'pending';
        });

        // Returns the view with partitioned complaints data.
        return view('admin.projects.complaints', compact('project', 'pendingComplaints', 'resolvedComplaints'));
    }

    /**
     * Approves a specific complaint, changing its status to 'approved'.
     * Only accessible to administrators.
     *
     * @param Complaint $complaint The Complaint model to be approved.
     * @return \Illuminate\Http\RedirectResponse Redirects back with a status message.
     */
    public function approveComplaint(Complaint $complaint)
    {
        $complaint->update(['status' => 'approved']); // Updates the complaint status.
        return back()->with('status', 'complaint-approved'); // Redirects with success message.
    }
    
    /**
     * Declines a specific complaint, changing its status to 'declined'.
     * Only accessible to administrators.
     *
     * @param Complaint $complaint The Complaint model to be declined.
     * @return \Illuminate\Http\RedirectResponse Redirects back with a status message.
     */
    public function declineComplaint(Complaint $complaint)
    {
        $complaint->update(['status' => 'declined']); // Updates the complaint status.
        return back()->with('status', 'complaint-declined'); // Redirects with success message.
    }
}