<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create account — TaskFlow AI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen items-center justify-center bg-slate-50 p-5">
    <main class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <a href="{{ route('login') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700">
            ← Already have an account? Sign in
        </a>

        <div
            class="mt-8 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 text-xl font-bold text-white">
            T
        </div>

        <h1 class="mt-6 text-2xl font-bold tracking-tight">
            Create your account
        </h1>

        <p class="mt-2 text-sm leading-6 text-slate-500">
            Start organizing your work with TaskFlow AI.
        </p>

        @if ($errors->any())
            <div role="alert" class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <p class="font-semibold">Please check your details.</p>
                <ul class="mt-2 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <label for="name" class="mb-2 block text-sm font-medium text-slate-700">
                    Full name
                </label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name"
                    maxlength="255" required autofocus
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    placeholder="Your name">
            </div>

            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">
                    Email address
                </label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    placeholder="you@example.com">
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-slate-700">
                    Password
                </label>
                <input id="password" name="password" type="password" autocomplete="new-password" minlength="8"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    placeholder="At least 8 characters">
            </div>

            <div>
                <label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-700">
                    Confirm password
                </label>
                <input id="password_confirmation" name="password_confirmation" type="password"
                    autocomplete="new-password" minlength="8" required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    placeholder="Enter your password again">
            </div>

            <button type="submit"
                class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">
                Create account
            </button>
        </form>

        <p class="mt-8 text-center text-xs text-slate-400">
            TaskFlow AI · A clearer way to work.
        </p>
    </main>
</body>

</html>
