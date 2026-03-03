<?php

namespace App\Http\Controllers;

use App\Actions\User\DeleteUserAccountAction;
use App\Actions\User\UpdateUserProfileAction;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Profile/Edit');
    }

    public function update(
        ProfileUpdateRequest $request,
        UpdateUserProfileAction $updateProfile,
    ): RedirectResponse {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $updateProfile->execute(
            $user,
            $request->validated(),
        );

        return to_route('profile.edit');
    }

    public function destroy(
        DeleteAccountRequest $request,
        DeleteUserAccountAction $deleteAccount,
    ): RedirectResponse {
        /** @var \App\Models\User $user */
        $user = $request->user();

        Auth::logout();

        $deleteAccount->execute($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
