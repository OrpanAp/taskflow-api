const menuButton = document.querySelector("#menuButton");
const sidebar = document.querySelector("#sidebar");
const overlay = document.querySelector("#sidebarOverlay");

function closeSidebar() {
    sidebar?.classList.add("-translate-x-full");
    overlay?.classList.add("hidden");
}

function openSidebar() {
    sidebar?.classList.remove("-translate-x-full");
    overlay?.classList.remove("hidden");
}

menuButton?.addEventListener("click", () => {
    const isClosed = sidebar?.classList.contains("-translate-x-full");

    if (isClosed) {
        openSidebar();
    } else {
        closeSidebar();
    }
});

overlay?.addEventListener("click", closeSidebar);

sidebar?.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", closeSidebar);
});

const taskList = document.querySelector("#taskList");

if (taskList) {
    const loading = document.querySelector("#taskLoading");
    const empty = document.querySelector("#taskEmpty");
    const error = document.querySelector("#taskError");
    const items = document.querySelector("#taskItems");
    const searchInput = document.querySelector("#taskSearch");
    const statusFilter = document.querySelector("#statusFilter");

    // Task creation form elements
    const newTaskButton = document.querySelector("#newTaskButton");
    const taskFormPanel = document.querySelector("#taskFormPanel");
    const taskForm = document.querySelector("#taskForm");
    const taskTitle = document.querySelector("#taskTitle");
    const taskDescription = document.querySelector("#taskDescription");
    const taskStatus = document.querySelector("#taskStatus");
    const taskFormError = document.querySelector("#taskFormError");
    const saveTaskButton = document.querySelector("#saveTaskButton");
    const cancelTaskButton = document.querySelector("#cancelTaskButton");

    function openTaskForm() {
        if (!taskFormPanel) return;

        taskFormPanel.classList.remove("hidden");
        newTaskButton?.setAttribute("aria-expanded", "true");
        taskFormError?.classList.add("hidden");
        taskTitle?.focus();
    }

    function closeTaskForm() {
        taskFormPanel?.classList.add("hidden");
        newTaskButton?.setAttribute("aria-expanded", "false");
        taskFormError?.classList.add("hidden");
    }

    function showTaskFormError(message) {
        if (!taskFormError) return;

        taskFormError.textContent = message;
        taskFormError.classList.remove("hidden");
    }

    newTaskButton?.addEventListener("click", () => {
        if (taskFormPanel?.classList.contains("hidden")) {
            openTaskForm();
        } else {
            closeTaskForm();
        }
    });

    cancelTaskButton?.addEventListener("click", () => {
        taskForm?.reset();
        closeTaskForm();
    });

    async function loadStats() {
        const response = await fetch("/dashboard/tasks/stats", {
            headers: { Accept: "application/json" },
            credentials: "same-origin",
        });

        if (!response.ok) {
            throw new Error(`Statistics request failed: ${response.status}`);
        }

        const stats = await response.json();

        document.querySelector("#statAll").textContent = stats.all;
        document.querySelector("#statPending").textContent = stats.pending;
        document.querySelector("#statInProgress").textContent =
            stats.in_progress;
        document.querySelector("#statCompleted").textContent = stats.completed;
    }

    async function loadTasks() {
        loading.classList.remove("hidden");
        empty.classList.add("hidden");
        error.classList.add("hidden");
        items.classList.add("hidden");

        const params = new URLSearchParams();
        const search = searchInput.value.trim();
        const status = statusFilter.value;

        if (search) params.set("search", search);
        if (status !== "all") params.set("status", status);

        try {
            const response = await fetch(
                `/dashboard/tasks?${params.toString()}`,
                {
                    headers: { Accept: "application/json" },
                    credentials: "same-origin",
                },
            );

            if (!response.ok) {
                throw new Error(`Request failed: ${response.status}`);
            }

            const result = await response.json();

            items.replaceChildren();

            result.data.forEach((task) => {
                const article = document.createElement("article");
                article.className =
                    "flex flex-col gap-2 py-5 sm:flex-row sm:items-start sm:justify-between";

                const details = document.createElement("div");

                const title = document.createElement("h4");
                title.className = "font-semibold text-slate-900";
                title.textContent = task.title;

                const description = document.createElement("p");
                description.className =
                    "mt-1 whitespace-pre-wrap text-sm leading-6 text-slate-500";
                description.textContent =
                    task.description || "No description provided.";

                details.append(title, description);

                const badge = document.createElement("span");
                const statusLabels = {
                    pending: "Pending",
                    in_progress: "In progress",
                    completed: "Completed",
                };

                badge.className =
                    "w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600";
                badge.textContent = statusLabels[task.status] || task.status;

                article.append(details, badge);
                items.append(article);
            });

            loading.classList.add("hidden");

            if (result.data.length === 0) {
                empty.classList.remove("hidden");
            } else {
                items.classList.remove("hidden");
            }
        } catch (exception) {
            console.error("Failed to load tasks:", exception);
            loading.classList.add("hidden");
            error.classList.remove("hidden");
        }
    }

    // Submit a new task to Laravel.
    taskForm?.addEventListener("submit", async (event) => {
        event.preventDefault();

        taskFormError?.classList.add("hidden");

        const title = taskTitle.value.trim();

        if (!title) {
            showTaskFormError("Please enter a task title.");
            taskTitle.focus();
            return;
        }

        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        if (!csrfToken) {
            showTaskFormError(
                "The security token is missing. Refresh the page and try again.",
            );
            return;
        }

        saveTaskButton.disabled = true;
        saveTaskButton.textContent = "Creating...";
        saveTaskButton.classList.add("cursor-not-allowed", "opacity-60");

        try {
            const response = await fetch("/dashboard/tasks", {
                method: "POST",
                credentials: "same-origin",
                headers: {
                    Accept: "application/json",
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                },
                body: JSON.stringify({
                    title,
                    description: taskDescription.value.trim() || null,
                    status: taskStatus.value,
                }),
            });

            const result = await response.json().catch(() => ({}));

            if (!response.ok) {
                if (response.status === 422 && result.errors) {
                    const firstError = Object.values(result.errors)
                        .flat()
                        .find(Boolean);

                    showTaskFormError(
                        firstError || "Please check your task details.",
                    );
                } else if (response.status === 401 || response.status === 419) {
                    showTaskFormError(
                        "Your session may have expired. Refresh the page and sign in again.",
                    );
                } else {
                    showTaskFormError(
                        result.message ||
                            "Unable to create the task. Please try again.",
                    );
                }

                return;
            }

            // Reset filters so the newly created task is visible.
            searchInput.value = "";
            statusFilter.value = "all";

            taskForm.reset();
            closeTaskForm();

            // Refresh the list and statistics independently.
            const refreshResults = await Promise.allSettled([
                loadTasks(),
                loadStats(),
            ]);

            if (refreshResults.some((item) => item.status === "rejected")) {
                console.error(
                    "One or more dashboard sections failed to refresh.",
                );
            }
        } catch (exception) {
            console.error("Failed to create task:", exception);

            showTaskFormError(
                "A network error occurred. Check your connection and try again.",
            );
        } finally {
            saveTaskButton.disabled = false;
            saveTaskButton.textContent = "Create task";
            saveTaskButton.classList.remove("cursor-not-allowed", "opacity-60");
        }
    });

    let searchTimer;

    searchInput.addEventListener("input", () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadTasks, 300);
    });

    statusFilter.addEventListener("change", loadTasks);

    loadTasks();

    loadStats().catch((exception) => {
        console.error("Failed to load task statistics:", exception);
    });
}
