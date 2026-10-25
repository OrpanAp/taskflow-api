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

const aiProposalForm = document.querySelector("#aiProposalForm");

if (aiProposalForm) {
    const aiPrompt = document.querySelector("#aiPrompt");
    const aiError = document.querySelector("#aiProposalError");
    const aiSuccess = document.querySelector("#aiProposalSuccess");
    const aiReview = document.querySelector("#aiProposalReview");
    const aiTitle = document.querySelector("#aiProposalTitle");
    const aiDescription = document.querySelector("#aiProposalDescription");
    const generateButton = document.querySelector("#generateProposalButton");
    const approveButton = document.querySelector("#approveProposalButton");
    const discardButton = document.querySelector("#discardProposalButton");

    function showAiError(message) {
        aiSuccess.classList.add("hidden");
        aiSuccess.textContent = "";
        aiError.textContent = message;
        aiError.classList.remove("hidden");
    }

    function clearAiError() {
        aiError.textContent = "";
        aiError.classList.add("hidden");
    }

    function showAiSuccess(message) {
        clearAiError();
        aiSuccess.textContent = message;
        aiSuccess.classList.remove("hidden");
    }

    function clearAiSuccess() {
        aiSuccess.textContent = "";
        aiSuccess.classList.add("hidden");
    }

    function setButtonBusy(button, busy, busyText, normalText) {
        button.disabled = busy;
        button.textContent = busy ? busyText : normalText;
        button.classList.toggle("cursor-not-allowed", busy);
        button.classList.toggle("opacity-60", busy);
    }

    function resetAiProposal() {
        aiReview.classList.add("hidden");
        aiTitle.value = "";
        aiDescription.value = "";
        clearAiError();
        clearAiSuccess();
    }

    async function postJson(url, payload) {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        if (!csrfToken) {
            throw new Error(
                "The security token is missing. Refresh the page and try again.",
            );
        }

        const response = await fetch(url, {
            method: "POST",
            credentials: "same-origin",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            body: JSON.stringify(payload),
        });

        const result = await response.json().catch(() => ({}));

        if (!response.ok) {
            if (response.status === 401 || response.status === 419) {
                throw new Error(
                    "Your session may have expired. Refresh the page and sign in again.",
                );
            }

            if (response.status === 422 && result.errors) {
                const firstError = Object.values(result.errors)
                    .flat()
                    .find(Boolean);

                throw new Error(firstError || "Please check your input.");
            }

            throw new Error(
                result.message || "The request failed. Please try again.",
            );
        }

        return result;
    }

    aiProposalForm.addEventListener("submit", async (event) => {
        event.preventDefault();
        clearAiError();
        clearAiSuccess();
        aiReview.classList.add("hidden");

        const prompt = aiPrompt.value.trim();

        if (prompt.length < 3 || prompt.length > 2000) {
            showAiError(
                "Please enter a request between 3 and 2000 characters.",
            );
            aiPrompt.focus();
            return;
        }

        setButtonBusy(
            generateButton,
            true,
            "Generating proposal...",
            "Generate proposal",
        );

        try {
            const result = await postJson("/dashboard/ai/task-proposals", {
                prompt,
            });

            if (
                !result.proposal ||
                typeof result.proposal.title !== "string" ||
                !result.proposal.title.trim()
            ) {
                throw new Error("The AI returned an invalid task proposal.");
            }

            aiTitle.value = result.proposal.title;
            aiDescription.value = result.proposal.description ?? "";
            aiReview.classList.remove("hidden");
        } catch (exception) {
            showAiError(
                exception instanceof Error
                    ? exception.message
                    : "Unable to generate a proposal. Please try again.",
            );
        } finally {
            setButtonBusy(
                generateButton,
                false,
                "Generating proposal...",
                "Generate proposal",
            );
        }
    });

    approveButton.addEventListener("click", async () => {
        clearAiError();
        clearAiSuccess();

        const title = aiTitle.value.trim();
        const description = aiDescription.value.trim();

        if (!title || title.length > 200) {
            showAiError("Enter a task title of 1–200 characters.");
            aiTitle.focus();
            return;
        }

        if (description.length > 10000) {
            showAiError("The description cannot exceed 10000 characters.");
            aiDescription.focus();
            return;
        }

        setButtonBusy(
            approveButton,
            true,
            "Creating task...",
            "Approve and create task",
        );

        try {
            await postJson("/dashboard/tasks", {
                title,
                description: description || null,
                status: "pending",
            });

            // Refresh the dashboard so the new task is visible.
            const searchInput = document.querySelector("#taskSearch");
            const statusFilter = document.querySelector("#statusFilter");

            if (searchInput) searchInput.value = "";
            if (statusFilter) statusFilter.value = "all";

            resetAiProposal();
            aiPrompt.value = "";

            if (refreshDashboardAfterAiSave) {
                const refreshResults = await refreshDashboardAfterAiSave();

                if (refreshResults.some((item) => item.status === "rejected")) {
                    console.error(
                        "One or more dashboard sections failed to refresh.",
                    );
                }
            }

            showAiSuccess("Task created successfully.");
        } catch (exception) {
            showAiError(
                exception instanceof Error
                    ? exception.message
                    : "Unable to create the task. Please try again.",
            );
        } finally {
            setButtonBusy(
                approveButton,
                false,
                "Creating task...",
                "Approve and create task",
            );
        }
    });

    discardButton.addEventListener("click", resetAiProposal);
}

let refreshDashboardAfterAiSave = null;
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
    const taskFormHeading = document.querySelector("#taskFormHeading");
    const taskNotice = document.querySelector("#taskNotice");
    let editingTaskId = null;

    function showTaskNotice(message, isError = false) {
        if (!taskNotice) return;

        taskNotice.textContent = message;
        taskNotice.classList.remove(
            "hidden",
            "border-emerald-100",
            "bg-emerald-50",
            "text-emerald-700",
            "border-red-100",
            "bg-red-50",
            "text-red-700",
        );
        taskNotice.classList.add(
            ...(isError
                ? ["border-red-100", "bg-red-50", "text-red-700"]
                : ["border-emerald-100", "bg-emerald-50", "text-emerald-700"]),
        );
    }

    function clearTaskNotice() {
        taskNotice?.classList.add("hidden");
        if (taskNotice) taskNotice.textContent = "";
    }

    function resetTaskForm() {
        taskForm?.reset();
        editingTaskId = null;
        if (saveTaskButton) saveTaskButton.textContent = "Create task";
        if (taskFormHeading) taskFormHeading.textContent = "Create a task";
        if (newTaskButton) newTaskButton.textContent = "+ New task";
        clearTaskNotice();
    }

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
            resetTaskForm();
            openTaskForm();
        } else {
            closeTaskForm();
        }
    });

    cancelTaskButton?.addEventListener("click", () => {
        resetTaskForm();
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

                const taskActions = document.createElement("div");
                taskActions.className = "flex shrink-0 flex-wrap items-center gap-2";

                const editButton = document.createElement("button");
                editButton.type = "button";
                editButton.textContent = "Edit";
                editButton.setAttribute("aria-label", `Edit task: ${task.title}`);
                editButton.className =
                    "rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700";
                editButton.addEventListener("click", () => {
                    clearTaskNotice();
                    editingTaskId = task.id;
                    taskTitle.value = task.title ?? "";
                    taskDescription.value = task.description ?? "";
                    taskStatus.value = task.status ?? "pending";
                    saveTaskButton.textContent = "Save changes";
                    if (taskFormHeading) taskFormHeading.textContent = "Edit task";
                    if (newTaskButton) newTaskButton.textContent = "+ New task";
                    openTaskForm();
                    taskFormPanel?.scrollIntoView({ behavior: "smooth", block: "start" });
                });

                const deleteButton = document.createElement("button");
                deleteButton.type = "button";
                deleteButton.textContent = "Delete";
                deleteButton.setAttribute("aria-label", `Delete task: ${task.title}`);
                deleteButton.className =
                    "rounded-lg border border-red-100 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50";
                deleteButton.addEventListener("click", async () => {
                    if (!window.confirm(`Delete “${task.title}”? This action cannot be undone.`)) {
                        return;
                    }

                    clearTaskNotice();
                    deleteButton.disabled = true;
                    deleteButton.textContent = "Deleting...";

                    try {
                        const csrfToken = document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content");

                        if (!csrfToken) {
                            throw new Error("The security token is missing. Refresh the page and try again.");
                        }

                        const response = await fetch(`/dashboard/tasks/${encodeURIComponent(task.id)}`, {
                            method: "DELETE",
                            credentials: "same-origin",
                            headers: {
                                Accept: "application/json",
                                "X-CSRF-TOKEN": csrfToken,
                            },
                        });
                        const result = await response.json().catch(() => ({}));

                        if (!response.ok) {
                            if (response.status === 401 || response.status === 419) {
                                throw new Error("Your session may have expired. Refresh the page and sign in again.");
                            }
                            throw new Error(result.message || "Unable to delete this task.");
                        }

                        if (editingTaskId === task.id) {
                            resetTaskForm();
                            closeTaskForm();
                        }

                        await Promise.all([loadTasks(), loadStats()]);
                        showTaskNotice(result.message || "Task deleted successfully.");
                    } catch (exception) {
                        showTaskNotice(
                            exception instanceof Error ? exception.message : "Unable to delete this task.",
                            true,
                        );
                        deleteButton.disabled = false;
                        deleteButton.textContent = "Delete";
                    }
                });

                const statusAndActions = document.createElement("div");
                statusAndActions.className = "flex flex-wrap items-center gap-2 sm:justify-end";
                statusAndActions.append(badge, editButton, deleteButton);

                article.append(details, statusAndActions);
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

    refreshDashboardAfterAiSave = () =>
        Promise.allSettled([loadTasks(), loadStats()]);

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

        const isEditing = editingTaskId !== null;
        saveTaskButton.disabled = true;
        saveTaskButton.textContent = isEditing ? "Saving..." : "Creating...";
        saveTaskButton.classList.add("cursor-not-allowed", "opacity-60");

        try {
            const response = await fetch(
                isEditing
                    ? `/dashboard/tasks/${encodeURIComponent(editingTaskId)}`
                    : "/dashboard/tasks",
                {
                method: isEditing ? "PUT" : "POST",
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
                },
            );

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

            // Reset filters so the saved task remains visible after refresh.
            searchInput.value = "";
            statusFilter.value = "all";

            resetTaskForm();
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

            showTaskNotice(result.message || (isEditing ? "Task updated successfully." : "Task created successfully."));
        } catch (exception) {
            console.error("Failed to save task:", exception);

            showTaskFormError(
                "A network error occurred. Check your connection and try again.",
            );
        } finally {
            saveTaskButton.disabled = false;
            saveTaskButton.textContent = editingTaskId !== null ? "Save changes" : "Create task";
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
