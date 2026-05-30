<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm">
                <a href="{{ route('workspaces.show', $workspace) }}"
                   class="text-gray-500 hover:text-gray-700">
                    {{ $workspace->name }}
                </a>
                <span class="text-gray-400">/</span>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full"
                          style="background-color: {{ $project->color }}"></span>
                    <span class="font-semibold text-gray-800 text-xl">{{ $project->name }}</span>
                </div>
            </div>
            @can('update', $project)
                <a href="{{ route('workspaces.projects.edit', [$workspace, $project]) }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition ease-in-out duration-150">
                    Edit
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash message --}}
            @if (session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Project description --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">Description</h3>
                    <p class="mt-2 text-gray-900">
                        {{ $project->description ?? 'No description.' }}
                    </p>
                </div>
            </div>

            {{-- Tasks grouped by status --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                            Tasks ({{ $tasks->count() }})
                        </h3>
                        <a href="{{ route('workspaces.projects.tasks.create', [$workspace, $project]) }}"
                           class="inline-flex items-center px-3 py-1.5 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                            + New task
                        </a>
                    </div>

                    {{-- Filters --}}
                    <form method="GET" action="{{ route('workspaces.projects.show', [$workspace, $project]) }}"
                          class="mb-6 flex flex-wrap items-end gap-3">

                        <div class="flex-1 min-w-36">
                            <label class="block text-xs text-gray-500 mb-1">Search</label>
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Task title..."
                                   class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" />
                        </div>

                        <div class="min-w-36">
                            <label class="block text-xs text-gray-500 mb-1">Status</label>
                            <select name="status_id"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}"
                                        {{ request('status_id') == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="min-w-36">
                            <label class="block text-xs text-gray-500 mb-1">Assigned to</label>
                            <select name="assigned_to"
                                    class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm">
                                <option value="">All</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}"
                                        {{ request('assigned_to') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                    class="inline-flex items-center px-3 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                                Filter
                            </button>
                            @if (request()->hasAny(['search', 'status_id', 'assigned_to']))
                                <a href="{{ route('workspaces.projects.show', [$workspace, $project]) }}"
                                   class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition ease-in-out duration-150">
                                    Reset
                                </a>
                            @endif
                        </div>
                    </form>

                    @if ($tasks->isEmpty())
                        <p class="text-sm text-gray-500">No task yet.</p>
                    @else
                        <div class="space-y-6">
                            {{-- Tasks without status --}}
                            @php $unsortedTasks = $tasks->whereNull('status_id'); @endphp
                            @if ($unsortedTasks->isNotEmpty())
                                <div>
                                    <h4 class="text-xs font-semibold uppercase tracking-wide mb-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-500">
                                            No status
                                        </span>
                                    </h4>
                                    @include('projects.partials.task-list', ['statusTasks' => $unsortedTasks])
                                </div>
                            @endif

                            {{-- Tasks grouped by workspace status --}}
                            @foreach ($statuses as $status)
                                @php $statusTasks = $tasks->where('status_id', $status->id); @endphp
                                @if ($statusTasks->isNotEmpty())
                                    <div>
                                        <h4 class="text-xs font-semibold uppercase tracking-wide mb-2">
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded"
                                                  style="background-color: {{ $status->color }}20; color: {{ $status->color }}">
                                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $status->color }}"></span>
                                                {{ $status->name }}
                                            </span>
                                        </h4>
                                        @include('projects.partials.task-list', ['statusTasks' => $statusTasks])
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Danger zone --}}
            @can('delete', $project)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-red-600 uppercase tracking-wide">Danger zone</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Deleting the project is irreversible.
                        </p>
                        <form method="POST"
                              action="{{ route('workspaces.projects.destroy', [$workspace, $project]) }}"
                              onsubmit="return confirm('Permanently delete this project?')">
                            @csrf
                            @method('DELETE')
                            <x-danger-button class="mt-4">
                                Delete project
                            </x-danger-button>
                        </form>
                    </div>
                </div>
            @endcan

        </div>
    </div>
</x-app-layout>
