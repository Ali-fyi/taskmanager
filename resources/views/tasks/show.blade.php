<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <a href="{{ route('workspaces.show', $workspace) }}" class="hover:text-gray-700">{{ $workspace->name }}</a>
                <span>/</span>
                <a href="{{ route('workspaces.projects.show', [$workspace, $project]) }}" class="hover:text-gray-700">{{ $project->name }}</a>
                <span>/</span>
                <span class="text-gray-800 font-medium truncate max-w-xs">{{ $task->title }}</span>
            </div>
            @can('update', $task)
                <a href="{{ route('tasks.edit', $task) }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition ease-in-out duration-150">
                    Modifier
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4">

                    {{-- Statut --}}
                    <div class="flex items-center gap-3">
                        @if ($task->status)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-medium"
                                  style="background-color: {{ $task->status->color }}20; color: {{ $task->status->color }}">
                                <span class="w-2 h-2 rounded-full" style="background-color: {{ $task->status->color }}"></span>
                                {{ $task->status->name }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-500">
                                Sans statut
                            </span>
                        @endif
                        @if ($task->due_date)
                            <span class="text-xs {{ $task->due_date->isPast() ? 'text-red-500 font-medium' : 'text-gray-400' }}">
                                Échéance : {{ $task->due_date->format('d/m/Y') }}
                            </span>
                        @endif
                    </div>

                    {{-- Description --}}
                    <div>
                        <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wide">Description</h3>
                        <p class="mt-1 text-gray-900 text-sm whitespace-pre-line">
                            {{ $task->description ?? 'Aucune description.' }}
                        </p>
                    </div>

                    {{-- Assigné --}}
                    <div>
                        <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wide">Assigné à</h3>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $task->assignee?->name ?? 'Personne' }}
                        </p>
                    </div>

                </div>
            </div>

            {{-- Commentaires --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">
                        Commentaires ({{ $task->comments->count() }})
                    </h3>

                    {{-- Liste des commentaires --}}
                    @if ($task->comments->isNotEmpty())
                        <ul class="space-y-4 mb-6">
                            @foreach ($task->comments as $comment)
                                <li class="flex gap-3">
                                    <div class="flex-1 bg-gray-50 rounded-lg px-4 py-3">
                                        <div class="flex items-center justify-between mb-1">
                                            <span class="text-xs font-semibold text-gray-700">
                                                {{ $comment->author->name }}
                                            </span>
                                            <span class="text-xs text-gray-400">
                                                {{ $comment->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-800 whitespace-pre-line">{{ $comment->body }}</p>
                                    </div>
                                    @can('delete', $comment)
                                        <form method="POST" action="{{ route('comments.destroy', $comment) }}"
                                              onsubmit="return confirm('Supprimer ce commentaire ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-xs text-gray-400 hover:text-red-500 transition-colors mt-1">
                                                ✕
                                            </button>
                                        </form>
                                    @endcan
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-400 mb-6">Aucun commentaire pour l'instant.</p>
                    @endif

                    {{-- Formulaire d'ajout --}}
                    <form method="POST" action="{{ route('tasks.comments.store', $task) }}">
                        @csrf
                        <textarea
                            name="body"
                            rows="3"
                            placeholder="Ajouter un commentaire..."
                            class="block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm"
                        >{{ old('body') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                        <div class="mt-3 flex justify-end">
                            <x-primary-button>Commenter</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Zone danger --}}
            @can('delete', $task)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-red-600 uppercase tracking-wide">Zone de danger</h3>
                        <form method="POST" action="{{ route('tasks.destroy', $task) }}"
                              onsubmit="return confirm('Supprimer cette tâche ?')">
                            @csrf
                            @method('DELETE')
                            <x-danger-button class="mt-4">Supprimer la tâche</x-danger-button>
                        </form>
                    </div>
                </div>
            @endcan

        </div>
    </div>
</x-app-layout>
