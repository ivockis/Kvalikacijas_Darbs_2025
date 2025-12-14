<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Image; // Import Image model
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage; // Import Storage facade
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if it exists
            if ($user->profileImage) {
                Storage::disk('public')->delete($user->profileImage->path);
                $user->profileImage->delete();
            }

            $path = $request->file('profile_image')->store('profile-images', 'public');
            $image = Image::create(['path' => $path]);
            $user->profile_image_id = $image->id;
        } elseif ($request->boolean('remove_profile_image') && $user->profileImage) {
            // Remove profile image if requested
            Storage::disk('public')->delete($user->profileImage->path);
            $user->profileImage->delete();
            $user->profile_image_id = null;
        }
        
        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        // Delete profile image if exists
        if ($user->profileImage) {
            Storage::disk('public')->delete($user->profileImage->path);
            $user->profileImage->delete();
        }

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
