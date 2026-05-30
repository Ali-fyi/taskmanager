<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('workspaces.show', $workspace) }}" class="hover:text-gray-700">{{ $workspace->name }}</a>
            <span>/</span>
            <a href="{{ route('workspaces.projects.show', [$workspace, $project]) }}" class="hover:text-gray-700">{{ $project->name }}</a>
            <span>/</span>
            <span class="text-gray-800 font-medium">New task</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">

                    <form method="POST" action="{{ route('workspaces.projects.tasks.store', [$workspace, $project]) }}">
                        @csrf

                        {{-- Title --}}
                        <div>
                            <x-input-label for="title" value="Task title" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                                :value="old('title')" placeholder="E.g. Build the home page..." autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        {{-- Description --}}
                        <div class="mt-6">
                            <x-input-label for="description" value="Description (optional)" />
                            <textarea id="description" name="description" rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                placeholder="Add details about this task...">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        {{-- Status --}}
                        <div class="mt-6">
                            <x-input-label for="status_id" value="Status (optional)" />
                            <select id="status_id" name="status_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">— No status —</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}"
                                        {{ old('status_id') == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status_id')" class="mt-2" />
                        </div>

                        {{-- Assigned to --}}
                        <div class="mt-6">
                            <x-input-label for="assigned_to" value="Assigned to (optional)" />
                            <select id="assigned_to" name="assigned_to"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">— Nobody —</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}"
                                        {{ old('assigned_to') == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('assigned_to')" class="mt-2" />
                        </div>

                        {{-- Due date --}}
                        <div class="mt-6">
                            <x-input-label for="due_date" value="Due date (optional)" />
                            <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full"
                                :value="old('due_date')" />
                            <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                        </div>

                        <div class="mt-6 flex items-center gap-4">
                            <x-primary-button>Create task</x-primary-button>
                            <a href="{{ route('workspaces.projects.show', [$workspace, $project]) }}"
                               class="text-sm text-gray-600 hover:text-gray-900 underline">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
