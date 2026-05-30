<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Stats --}}
            <div class="flex items-stretch gap-4">
                <div class="flex-1 flex items-baseline justify-center gap-2 bg-white shadow-sm rounded-lg px-5 py-4">
                    <span class="text-2xl font-bold text-gray-800">{{ $stats['workspaces'] }}</span>
                    <span class="text-sm text-gray-500">Workspace{{ $stats['workspaces'] > 1 ? 's' : '' }}</span>
                </div>
                <div class="flex-1 flex items-baseline justify-center gap-2 bg-white shadow-sm rounded-lg px-5 py-4">
                    <span class="text-2xl font-bold text-indigo-600">{{ $stats['assigned'] }}</span>
                    <span class="text-sm text-gray-500">Assigned task{{ $stats['assigned'] > 1 ? 's' : '' }}</span>
                </div>
                <div class="flex-1 flex items-baseline justify-center gap-2 bg-white shadow-sm rounded-lg px-5 py-4">
                    <span class="text-2xl font-bold {{ $stats['overdue'] > 0 ? 'text-red-500' : 'text-gray-800' }}">
                        {{ $stats['overdue'] }}
                    </span>
                    <span class="text-sm text-gray-500">Overdue</span>
                </div>
            </div>

            {{-- My tasks --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">
                        My tasks
                    </h3>

                    @if ($myTasks->isEmpty())
                        <p class="text-sm text-gray-400">No task is assigned to you.</p>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($myTasks as $task)
                                <li class="flex items-center justify-between py-3 gap-4">
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ route('tasks.show', $task) }}"
                                           class="text-sm font-medium text-gray-900 hover:text-indigo-600 transition-colors truncate block">
                                            {{ $task->title }}
                                        </a>
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            <a href="{{ route('workspaces.show', $task->project->workspace) }}"
                                               class="hover:underline">
                                                {{ $task->project->workspace->name }}
                                            </a>
                                            <span class="mx-1">›</span>
                                            <a href="{{ route('workspaces.projects.show', [$task->project->workspace, $task->project]) }}"
                                               class="hover:underline">
                                                {{ $task->project->name }}
                                            </a>
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        @if ($task->status)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                                                  style="background-color: {{ $task->status->color }}20; color: {{ $task->status->color }}">
                                                {{ $task->status->name }}
                                            </span>
                                        @endif
                                        @if ($task->due_date)
                                            <span class="text-xs {{ $task->due_date->isPast() ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                                                {{ $task->due_date->format('d/m/Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
