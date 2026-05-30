<ul class="divide-y divide-gray-100 border border-gray-100 rounded-lg">
    @foreach ($statusTasks as $task)
        <li class="relative">

            <a href="{{ route('tasks.show', $task) }}"
               class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors">

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ $task->title }}
                    </p>

                    @if ($task->assignee)
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $task->assignee->name }}
                        </p>
                    @endif
                </div>

                <div class="ml-4 flex items-center gap-3 shrink-0">
                    @if ($task->due_date)
                        <span class="text-xs {{ $task->due_date->isPast() ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                            {{ $task->due_date->format('d/m/Y') }}
                        </span>
                    @endif
                </div>
            </a>

            <a href="{{ route('tasks.edit', $task) }}"
               class="absolute right-4 top-1/2 -translate-y-1/2 text-xs text-gray-400 hover:text-gray-600 underline z-10">
                Edit
            </a>

        </li>
    @endforeach
</ul>