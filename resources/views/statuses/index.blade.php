<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-gray-500">
                <a href="{{ route('workspaces.show', $workspace) }}" class="hover:text-gray-700">{{ $workspace->name }}</a>
                <span>/</span>
                <span class="text-gray-800 font-medium">Statuts</span>
            </div>
            @can('update', $workspace)
                <a href="{{ route('workspaces.statuses.create', $workspace) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                    + Nouveau statut
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if ($statuses->isEmpty())
                        <p class="text-sm text-gray-500">Aucun statut défini.</p>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($statuses as $status)
                                <li class="flex items-center justify-between py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="w-4 h-4 rounded-full shrink-0"
                                              style="background-color: {{ $status->color }}"></span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $status->name }}</p>
                                            <p class="text-xs text-gray-400">Position {{ $status->position }}</p>
                                        </div>
                                    </div>
                                    @can('update', $status)
                                        <div class="flex items-center gap-3">
                                            <a href="{{ route('workspaces.statuses.edit', [$workspace, $status]) }}"
                                               class="text-xs text-gray-500 hover:text-gray-800 underline">
                                                Modifier
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('workspaces.statuses.destroy', [$workspace, $status]) }}"
                                                  onsubmit="return confirm('Supprimer ce statut ? Les tâches associées n\'auront plus de statut.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-xs text-red-500 hover:text-red-700 underline">
                                                    Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    @endcan
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
