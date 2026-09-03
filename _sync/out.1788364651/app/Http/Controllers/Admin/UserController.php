<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::orderBy('username')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:6'],
        ]);
        $data['is_admin'] = true;

        // The 'password' => 'hashed' cast on the model hashes this automatically.
        User::create($data);

        return back()->with('status', 'User "'.$data['username'].'" created.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        // Leave the password field blank to keep the current one.
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('status', 'User "'.$user->username.'" updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['delete' => 'You can’t delete the account you’re currently signed in with.']);
        }

        if (User::count() <= 1) {
            return back()->withErrors(['delete' => 'You can’t delete the last remaining user — the admin would be locked out.']);
        }

        $username = $user->username;
        $user->delete();

        return back()->with('status', 'User "'.$username.'" removed.');
    }
}
