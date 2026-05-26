<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('workspaces.show', $workspace) }}" class="hover:text-gray-700">{{ $workspace->name }}</a>
            <span>/</span>
            <a href="{{ route('workspaces.projects.show', [$workspace, $project]) }}" class="hover:text-gray-700">{{ $project->name }}</a>
            <span>/</span>
            <a href="{{ route('tasks.show', $task) }}" class="hover:text-gray-700 truncate max-w-xs">{{ $task->title }}</a>
            <span>/</span>
            <span class="text-gray-800 font-medium">Modifier</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">

                    <form method="POST" action="{{ route('tasks.update', $task) }}">
                        @csrf
                        @method('PATCH')

                        {{-- Titre --}}
                        <div>
                            <x-input-label for="title" value="Titre" />
                            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                                :value="old('title', $task->title)" autofocus />
                            <x-input-error :messages="$errors->get('title')" class="mt-2" />
                        </div>

                        {{-- Description --}}
                        <div class="mt-6">
                            <x-input-label for="description" value="Description (optionnelle)" />
                            <textarea id="description" name="description" rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            >{{ old('description', $task->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        {{-- Statut --}}
                        <div class="mt-6">
                            <x-input-label for="status_id" value="Statut (optionnel)" />
                            <select id="status_id" name="status_id"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">— Aucun statut —</option>
                                @foreach ($statuses as $status)
                                    <option value="{{ $status->id }}"
                                        {{ old('status_id', $task->status_id) == $status->id ? 'selected' : '' }}>
                                        {{ $status->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('status_id')" class="mt-2" />
                        </div>

                        {{-- Assigné à --}}
                        <div class="mt-6">
                            <x-input-label for="assigned_to" value="Assigné à (optionnel)" />
                            <select id="assigned_to" name="assigned_to"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">— Personne —</option>
                                @foreach ($members as $member)
                                    <option value="{{ $member->id }}"
                                        {{ old('assigned_to', $task->assigned_to) == $member->id ? 'selected' : '' }}>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('assigned_to')" class="mt-2" />
                        </div>

                        {{-- Date d'échéance --}}
                        <div class="mt-6">
                            <x-input-label for="due_date" value="Date d'échéance (optionnelle)" />
                            <x-text-input id="due_date" name="due_date" type="date" class="mt-1 block w-full"
                                :value="old('due_date', $task->due_date?->format('Y-m-d'))" />
                            <x-input-error :messages="$errors->get('due_date')" class="mt-2" />
                        </div>

                        <div class="mt-6 flex items-center gap-4">
                            <x-primary-button>Enregistrer</x-primary-button>
                            <a href="{{ route('tasks.show', $task) }}"
                               class="text-sm text-gray-600 hover:text-gray-900 underline">Annuler</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
