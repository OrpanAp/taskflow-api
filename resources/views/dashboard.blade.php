<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TaskFlow AI — Workspace</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-slate-50 text-slate-900">
    <div class="min-h-screen md:flex">
        <aside id="sidebar"
            class="fixed inset-y-0 left-0 z-30 flex w-64 -translate-x-full flex-col border-r border-slate-200 bg-white transition-transform duration-200 md:translate-x-0">
            <div class="flex h-20 items-center gap-3 border-b border-slate-100 px-6">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white">
                    T
                </div>
                <div>
                    <h1 class="text-lg font-bold tracking-tight">TaskFlow <span class="text-indigo-600">AI</span></h1>
                    <p class="text-xs text-slate-500">Your work, in flow.</p>
                </div>
            </div>

            <nav class="flex-1 space-y-2 px-4 py-6">
                <p class="mb-3 px-3 text-xs font-semibold uppercase tracking-widest text-slate-400">
                    Workspace
                </p>
                <a href="#dashboard"
                    class="flex items-center gap-3 rounded-xl bg-indigo-50 px-3 py-3 text-sm font-semibold text-indigo-700">
                    <span>▦</span> Overview
                </a>
                <a href="#tasks"
                    class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-100">
                    <span>☷</span> My Tasks
                </a>
                <p class="mb-3 mt-9 px-3 text-xs font-semibold uppercase tracking-widest text-slate-400">
                    Preferences
                </p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit"
                        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-600 transition hover:bg-red-50 hover:text-red-600">
                        <span aria-hidden="true">↪</span>
                        <span>Sign out</span>
                    </button>
                </form>
            </nav>

            <div class="m-4 rounded-2xl bg-slate-900 p-4 text-white">
                <p class="text-sm font-semibold">Make progress daily.</p>
                <p class="mt-2 text-xs leading-5 text-slate-300">
                    Organize your work, focus on priorities, and keep moving forward.
                </p>
                <div class="mt-4 border-t border-slate-700 pt-4">
                    <p class="text-xs leading-5 text-slate-300">
                        Every completed task is progress. Keep moving forward, one step at a time.
                    </p>
                </div>
            </div>
        </aside>

        <div id="sidebarOverlay" class="fixed inset-0 z-20 hidden bg-slate-950/40 md:hidden"></div>

        <main id="dashboard" class="min-w-0 flex-1 md:ml-64">
            <header
                class="sticky top-0 z-10 flex h-20 items-center justify-between border-b border-slate-200 bg-white/90 px-5 backdrop-blur sm:px-8">
                <div class="flex items-center gap-3">
                    <button id="menuButton" type="button" aria-label="Open navigation"
                        class="rounded-lg border border-slate-200 px-3 py-2 text-slate-600 md:hidden">
                        ☰
                    </button>
                    <div>
                        <p class="text-xs font-medium text-slate-500">Workspace / Overview</p>
                        <h2 class="mt-1 text-lg font-bold tracking-tight sm:text-xl">Dashboard</h2>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold">
                            Welcome, {{ auth()->user()->name }}
                        </p>
                        <p class="text-xs text-slate-500">Your productivity workspace</p>
                    </div>

                    <div
                        class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-100 font-bold text-indigo-700">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                </div>
            </header>

            <div class="mx-auto max-w-7xl space-y-8 p-5 sm:p-8">
                <section
                    class="overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-700 via-indigo-600 to-violet-600 p-6 text-white shadow-lg shadow-indigo-100 sm:p-9">
                    <div class="max-w-2xl">
                        <p class="text-sm font-medium text-indigo-100">YOUR PERSONAL WORKSPACE</p>
                        <h3 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">
                            Make space for what matters.
                        </h3>
                        <p class="mt-3 max-w-xl text-sm leading-6 text-indigo-100 sm:text-base">
                            Plan your work, track your progress, and turn your next big idea into small, achievable
                            steps.
                        </p>
                        <a href="#tasks"
                            class="mt-6 inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-semibold text-indigo-700 shadow-sm transition hover:bg-indigo-50">
                            Explore your tasks <span aria-hidden="true">→</span>
                        </a>
                    </div>
                </section>

                <section aria-label="Task statistics" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-500">All tasks</span>
                            <span class="rounded-xl bg-indigo-50 px-3 py-2 text-indigo-600">▦</span>
                        </div>
                        <p id="statAll" class="mt-5 text-3xl font-bold">—</p>
                        <p class="mt-2 text-xs text-slate-500">Your total tasks</p>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-500">Pending</span>
                            <span class="rounded-xl bg-amber-50 px-3 py-2 text-amber-600">◷</span>
                        </div>
                        <p id="statPending" class="mt-5 text-3xl font-bold">—</p>
                        <p class="mt-2 text-xs text-slate-500">Waiting to get started</p>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-500">In progress</span>
                            <span class="rounded-xl bg-sky-50 px-3 py-2 text-sky-600">↗</span>
                        </div>
                        <p id="statInProgress" class="mt-5 text-3xl font-bold">—</p>
                        <p class="mt-2 text-xs text-slate-500">Currently underway</p>
                    </article>

                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-slate-500">Completed</span>
                            <span class="rounded-xl bg-emerald-50 px-3 py-2 text-emerald-600">✓</span>
                        </div>
                        <p id="statCompleted" class="mt-5 text-3xl font-bold">—</p>
                        <p class="mt-2 text-xs text-slate-500">Tasks you've finished</p>
                    </article>
                </section>

                <section id="aiAssistant" class="rounded-2xl border border-violet-200 bg-white shadow-sm">
                    <div class="border-b border-violet-100 bg-violet-50/60 p-5 sm:p-6">
                        <p class="text-xs font-semibold uppercase tracking-widest text-violet-600">
                            Local AI · Ollama
                        </p>
                        <h3 class="mt-2 text-lg font-bold text-slate-900">AI task assistant</h3>
                        <p class="mt-1 text-sm leading-6 text-slate-600">
                            Describe what you need to accomplish. Review and edit the suggestion before approving it.
                        </p>
                    </div>

                    <div class="space-y-5 p-5 sm:p-6">
                        <form id="aiProposalForm" class="space-y-3">
                            <label for="aiPrompt" class="block text-sm font-semibold text-slate-700">
                                What do you need help planning?
                            </label>
                            <textarea id="aiPrompt" rows="3" minlength="3" maxlength="2000" required
                                placeholder="Example: Finish my Laravel API authentication tests and document the endpoints."
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-50"></textarea>

                            <div id="aiProposalError" role="alert"
                                class="hidden rounded-xl border border-red-100 bg-red-50 p-3 text-sm text-red-700">
                            </div>

                            <div id="aiProposalSuccess" role="status" aria-live="polite"
                                class="hidden rounded-xl border border-emerald-100 bg-emerald-50 p-3 text-sm text-emerald-700">
                            </div>

                            <button id="generateProposalButton" type="submit"
                                class="rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700 focus:outline-none focus:ring-4 focus:ring-violet-100">
                                Generate proposal
                            </button>
                        </form>

                        <div id="aiProposalReview"
                            class="hidden space-y-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:p-5">
                            <div>
                                <h4 class="font-semibold text-slate-900">Review your proposal</h4>
                                <p class="mt-1 text-sm text-slate-500">
                                    Nothing will be saved until you approve it.
                                </p>
                            </div>

                            <div>
                                <label for="aiProposalTitle" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Task title
                                </label>
                                <input id="aiProposalTitle" type="text" maxlength="200" required
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-50">
                            </div>

                            <div>
                                <label for="aiProposalDescription"
                                    class="mb-2 block text-sm font-semibold text-slate-700">
                                    Description
                                </label>
                                <textarea id="aiProposalDescription" rows="4" maxlength="10000"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-violet-400 focus:ring-4 focus:ring-violet-50"></textarea>
                            </div>

                            <p class="text-xs text-slate-500">Status: Pending</p>

                            <div class="flex flex-wrap gap-3">
                                <button id="approveProposalButton" type="button"
                                    class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                                    Approve and create task
                                </button>
                                <button id="discardProposalButton" type="button"
                                    class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                                    Discard
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section id="tasks" class="rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div
                        class="flex flex-col justify-between gap-4 border-b border-slate-100 p-5 sm:flex-row sm:items-center sm:p-6">
                        <div>
                            <h3 class="text-lg font-bold">Your tasks</h3>
                            <p class="mt-1 text-sm text-slate-500">Manage your tasks, track progress, and stay
                                organized.</p>
                        </div>
                        <button id="newTaskButton" type="button" aria-controls="taskFormPanel"
                            aria-expanded="false"
                            class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                            + New task
                        </button>
                    </div>
                    <div id="taskFormPanel" class="hidden border-b border-slate-100 bg-slate-50/70 p-5 sm:p-6">
                        <form id="taskForm" class="space-y-4">
                            @csrf

                            <div>
                                <label for="taskTitle" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Task title <span class="text-red-500">*</span>
                                </label>
                                <input id="taskTitle" name="title" type="text" maxlength="200" required
                                    placeholder="What do you need to accomplish?"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-50">
                            </div>

                            <div>
                                <label for="taskDescription" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Description <span class="font-normal text-slate-400">(optional)</span>
                                </label>
                                <textarea id="taskDescription" name="description" rows="3" maxlength="10000"
                                    placeholder="Add details about this task..."
                                    class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-indigo-400 focus:ring-4 focus:ring-indigo-50"></textarea>
                            </div>

                            <div>
                                <label for="taskStatus" class="mb-2 block text-sm font-semibold text-slate-700">
                                    Initial status
                                </label>
                                <select id="taskStatus" name="status"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none focus:border-indigo-400 sm:max-w-xs">
                                    <option value="pending">Pending</option>
                                    <option value="in_progress">In progress</option>
                                    <option value="completed">Completed</option>
                                </select>
                            </div>

                            <div id="taskFormError" role="alert"
                                class="hidden rounded-xl border border-red-100 bg-red-50 p-3 text-sm text-red-700">
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <button id="saveTaskButton" type="submit"
                                    class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-100">
                                    Create task
                                </button>

                                <button id="cancelTaskButton" type="button"
                                    class="rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-100">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:p-6">
                        <div class="relative flex-1">
                            <label for="taskSearch" class="sr-only">Search tasks</label>
                            <input id="taskSearch" type="search" placeholder="Search your tasks..."
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:ring-4 focus:ring-indigo-50">
                        </div>
                        <label for="statusFilter" class="sr-only">Filter by status</label>
                        <select id="statusFilter"
                            class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 outline-none focus:border-indigo-400">
                            <option value="all">All statuses</option>
                            <option value="pending">Pending</option>
                            <option value="in_progress">In progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>


                    <div id="taskList" aria-live="polite" class="border-t border-slate-100 px-5 py-8 sm:px-6">
                        <div id="taskLoading" class="py-8 text-center text-sm text-slate-500">
                            Loading your tasks...
                        </div>

                        <div id="taskEmpty" class="hidden py-8 text-center">
                            <div
                                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-2xl text-indigo-600">
                                ☷
                            </div>
                            <h4 class="mt-4 font-semibold">No tasks found</h4>
                            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                                No tasks match your current filters. Try a different search or status,
                                or create a new task to get started.
                            </p>
                        </div>

                        <div id="taskError" role="alert"
                            class="hidden rounded-xl bg-red-50 p-4 text-sm text-red-700">
                            Unable to load your tasks. Please refresh the page and try again.
                        </div>

                        <div id="taskItems" class="hidden divide-y divide-slate-100">
                        </div>
                    </div>

                </section>

                <footer class="pb-2 text-center text-xs text-slate-400">
                    TaskFlow AI · A clearer way to work.
                </footer>
            </div>
        </main>
    </div>
</body>

</html>
