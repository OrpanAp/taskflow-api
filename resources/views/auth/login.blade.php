<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign in — TaskFlow AI</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="flex min-h-screen items-center justify-center bg-slate-50 p-5">
    <main class="w-full max-w-md rounded-3xl border border-slate-200 bg-white p-8 shadow-sm sm:p-10">
        <a href="/" class="text-sm font-semibold text-indigo-600">
            ← Back to dashboard
        </a>

        <div
            class="mt-8 flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-600 text-xl font-bold text-white">
            T
        </div>

        <h1 class="mt-6 text-2xl font-bold tracking-tight">
            Welcome back
        </h1>

        <p class="mt-2 text-sm leading-6 text-slate-500">
            Sign in to your TaskFlow AI workspace.
        </p>

        @if (session('status'))
            <div role="status" class="mt-6 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

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

        <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-medium text-slate-700">
                    Email address
                </label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username"
                    required autofocus
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    placeholder="you@example.com">
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-medium text-slate-700">
                    Password
                </label>
                <input id="password" name="password" type="password" autocomplete="current-password" required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100"
                    placeholder="Enter your password">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1"
                    class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Remember me
            </label>

            <button type="submit"
                class="w-full rounded-xl bg-indigo-600 px-4 py-3 font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">
                Sign in
            </button>

            <p class="mt-6 text-center text-sm text-slate-500">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">
                    Create one
                </a>
            </p>
        </form>

        <p class="mt-8 text-center text-xs text-slate-400">
            TaskFlow AI · A clearer way to work.
        </p>
    </main>
</body>

</html>
