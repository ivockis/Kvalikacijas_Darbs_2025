<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Complaint;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display the user management view.
     */
    public function usersIndex(Request $request)
    {
        $query = User::where('id', '!=', auth()->id());

        // Search functionality
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('username', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Filtering by status
        if ($status = $request->input('status')) {
            if ($status === 'blocked') {
                $query->where('is_blocked', true);
            } elseif ($status === 'admin') {
                $query->where('is_admin', true);
            } elseif ($status === 'active') { // New filter for active users
                $query->where('is_blocked', false);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $request->input('per_page', 10);
        $users = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return $users;
        }

        return view('admin.users.index', compact('users'));
    }

    /**
     * Toggle the is_blocked status of a user.
     */
    public function toggleBlock(User $user)
    {
        // Prevent an admin from blocking themselves (as a safeguard)
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot block yourself.'], 403);
        }
        
        $user->is_blocked = !$user->is_blocked;
        $user->save();

        return response()->json(['message' => 'User status updated.', 'is_blocked' => $user->is_blocked]);
    }

    /**
     * Toggle the is_admin status of a user.
     */
    public function toggleAdmin(User $user)
    {
        // Prevent an admin from removing their own admin status (as a safeguard)
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'You cannot change your own admin status.'], 403);
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        

                return response()->json(['message' => 'User admin status updated.', 'is_admin' => $user->is_admin]);

            }

        

            /**

             * Display the project management view.

             */

            public function projectsIndex(Request $request)

            {

                $query = Project::with('user')->withCount('complaints'); // Eager load relationships
        
                // Search functionality

                if ($search = $request->input('search')) {

                    $query->where(function ($q) use ($search) {

                        $q->where('title', 'like', '%' . $search . '%')
                          ->orWhereHas('user', function ($userQuery) use ($search) {
                              $userQuery->where('username', 'like', '%' . $search . '%');
                          });
                    });

                }
        
                // Filtering by status

                if ($status = $request->input('status')) {

                    if ($status === 'blocked') {

                        $query->where('is_blocked', true);

                    } elseif ($status === 'active') {

                        $query->where('is_blocked', false);

                    } elseif ($status === 'has_complaints') {
                        $query->whereHas('complaints');
                    }

                }
        
                // Sorting

                $sortBy = $request->input('sort_by', 'created_at');

                $sortOrder = $request->input('sort_order', 'desc');

                if ($sortBy === 'complaints_count') {
                    $query->orderBy('complaints_count', $sortOrder);
                } else {
                    $query->orderBy($sortBy, $sortOrder);
                }

        
                $perPage = $request->input('per_page', 10);

                $projects = $query->paginate($perPage)->withQueryString();

        
                if ($request->wantsJson()) {

                    return $projects;

                }
        
                return view('admin.projects.index', compact('projects'));

            }

        

            /**

             * Toggle the is_blocked status of a project.

             */

            public function toggleProjectBlock(Project $project)

            {

                $project->is_blocked = !$project->is_blocked;

                $project->save();

        

                return response()->json(['message' => 'Project status updated.', 'is_blocked' => $project->is_blocked]);

            }

        

            /**

             * Display the complaints for a specific project.

             */

            public function showProjectComplaints(Project $project)

            {

                $project->load(['complaints.user']); // Eager load complaints and the user who made them
                
                [$pendingComplaints, $resolvedComplaints] = $project->complaints->partition(function ($complaint) {
                    return $complaint->status === 'pending';
                });

                return view('admin.projects.complaints', compact('project', 'pendingComplaints', 'resolvedComplaints'));

            }

            /**
             * Approve a complaint.
             */
            public function approveComplaint(Complaint $complaint)
            {
                $complaint->update(['status' => 'approved']);
                return back()->with('status', 'Complaint approved.');
            }
        
            /**
             * Decline a complaint.
             */
            public function declineComplaint(Complaint $complaint)
            {
                $complaint->update(['status' => 'declined']);
                return back()->with('status', 'Complaint declined.');
            }
        }

        