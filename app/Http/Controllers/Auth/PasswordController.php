<?php

namespace App\Http\Controllers\Auth;

use App\Actions\User\UpdateUserPasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    public function update(
        UpdatePasswordRequest $request,
        UpdateUserPasswordAction $updatePassword,
    ): RedirectResponse {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $validated = $request->validated();

        $updatePassword->execute(
            $user,
            $validated['password'],
        );

        return back();
    }
}
