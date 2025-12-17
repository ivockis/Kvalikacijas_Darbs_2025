<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display the specified user's public profile.
     */
    public function show(User $user)
    {
        // Eager load public projects with their cover images
        $projects = $user->projects()
            ->where('is_public', true)
            ->where('is_blocked', false)
            ->with('coverImage') // Eager load cover image for efficiency
            ->latest()
            ->paginate(9); // Paginate for better performance

        return view('users.show', compact('user', 'projects'));
    }
}