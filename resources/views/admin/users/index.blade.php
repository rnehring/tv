@extends('layouts.admin')
@section('title', 'Users')

@section('content')
    {{-- Add a user (full width, blue-tinted so it stands apart) --}}
    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-800/60 dark:bg-blue-950/40">
        <h3 class="mb-4 text-base font-semibold">Add a User</h3>
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            <div class="grid gap-3 md:grid-cols-4">
                <label class="text-sm">Full name
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Jane Smith" required
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm dark:border-gray-600 dark:bg-gray-800">
                </label>
                <label class="text-sm">Username
                    <input type="text" name="username" value="{{ old('username') }}" placeholder="e.g. jsmith" required autocomplete="off"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm dark:border-gray-600 dark:bg-gray-800">
                    <span class="mt-1 block text-xs text-gray-400">Letters, numbers, dashes and underscores.</span>
                </label>
                <label class="text-sm">Email <span class="text-gray-400">(optional)</span>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm dark:border-gray-600 dark:bg-gray-800">
                </label>
                <label class="text-sm">Password
                    <input type="text" name="password" placeholder="at least 6 characters" required autocomplete="new-password"
                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white p-2.5 text-sm dark:border-gray-600 dark:bg-gray-800">
                </label>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_admin" value="1" {{ old('is_admin') ? 'checked' : '' }}
                           class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Administrator <span class="text-gray-400">(can add &amp; remove users)</span>
                </label>
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-medium text-white hover:bg-blue-700">Add User</button>
            </div>
        </form>
    </div>

    <hr class="my-6 border-gray-200 dark:border-gray-700">

    <h3 class="mb-4 text-base font-semibold">Existing Users <span class="text-gray-400">({{ $users->count() }})</span></h3>
    <div class="grid gap-4 xl:grid-cols-2">
        @forelse ($users as $user)
            @php $isSelf = auth()->id() === $user->id; @endphp
            <div>
                <form method="POST" action="{{ route('admin.users.update', $user) }}"
                      class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-700 dark:bg-gray-800">
                    @csrf @method('PUT')
                    <div class="mb-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-sm font-semibold dark:bg-gray-700">
                                {{ strtoupper(substr($user->username, 0, 1)) }}
                            </span>
                            <span class="font-medium">{{ '@'.$user->username }}</span>
                            @if ($user->is_admin)
                                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900 dark:text-emerald-300">Admin</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">User</span>
                            @endif
                            @if ($isSelf)<span class="rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300">You</span>@endif
                        </div>
                        <span class="text-xs text-gray-400">Added {{ $user->created_at?->format('M j, Y') }}</span>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-sm">Full name
                            <input type="text" name="name" value="{{ $user->name }}" required
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"></label>
                        <label class="text-sm">Username
                            <input type="text" name="username" value="{{ $user->username }}" required autocomplete="off"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"></label>
                        <label class="text-sm sm:col-span-2">Email <span class="text-gray-400">(optional)</span>
                            <input type="email" name="email" value="{{ $user->email }}"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"></label>
                        <label class="text-sm sm:col-span-2">New password <span class="text-gray-400">(leave blank to keep current)</span>
                            <input type="text" name="password" placeholder="••••••••" autocomplete="new-password"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-gray-50 p-2 text-sm dark:border-gray-600 dark:bg-gray-700"></label>
                    </div>

                    <div class="mt-3">
                        @if ($isSelf)
                            {{-- Preserve own admin rights so you can never lock yourself out --}}
                            <input type="hidden" name="is_admin" value="1">
                            <label class="flex items-center gap-2 text-sm text-gray-400">
                                <input type="checkbox" checked disabled class="h-4 w-4 rounded border-gray-300 text-blue-600">
                                Administrator <span>(you — your own access can’t be removed here)</span>
                            </label>
                        @else
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="is_admin" value="1" {{ $user->is_admin ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                Administrator <span class="text-gray-400">(can add &amp; remove users)</span>
                            </label>
                        @endif
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-700">
                        @unless ($isSelf)
                            <button type="submit" form="delete-{{ $user->id }}"
                                    class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:hover:bg-gray-700">
                                Delete User
                            </button>
                        @else
                            <span></span>
                        @endunless
                        <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">Save</button>
                    </div>
                </form>

                @unless ($isSelf)
                    {{-- Delete form kept separate so its button can live inside the card footer --}}
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" id="delete-{{ $user->id }}"
                          data-confirm="Delete the user “{{ $user->username }}”? They’ll no longer be able to sign in.">
                        @csrf @method('DELETE')
                    </form>
                @endunless
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 p-10 text-center text-gray-400 dark:border-gray-600 xl:col-span-2">
                No users yet. Add one above.
            </div>
        @endforelse
    </div>
@endsection
