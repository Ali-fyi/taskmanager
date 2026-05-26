<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('workspaces.show', $workspace) }}" class="hover:text-gray-700">
                {{ $workspace->name }}
            </a>
            <span>/</span>
            <span class="text-gray-800 font-medium">Nouveau projet</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">

                    <form method="POST" action="{{ route('workspaces.projects.store', $workspace) }}">
                        @csrf

                        {{-- Nom --}}
                        <div>
                            <x-input-label for="name" value="Nom du projet" />
                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('name')"
                                placeholder="Ex : Site vitrine, Application mobile..."
                                autofocus
                            />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        {{-- Description --}}
                        <div class="mt-6">
                            <x-input-label for="description" value="Description (optionnelle)" />
                            <textarea
                                id="description"
                                name="description"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                placeholder="Décrivez brièvement ce projet..."
                            >{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        {{-- Couleur --}}
                        <div class="mt-6">
                            <x-input-label for="color" value="Couleur d'accentuation" />
                            <div class="mt-1 flex items-center gap-3">
                                <input
                                    type="color"
                                    id="color"
                                    name="color"
                                    value="{{ old('color', '#6366f1') }}"
                                    class="h-10 w-16 rounded-md border border-gray-300 cursor-pointer p-1"
                                />
                                <span class="text-sm text-gray-500">Choisissez une couleur pour identifier ce projet.</span>
                            </div>
                            <x-input-error :messages="$errors->get('color')" class="mt-2" />
                        </div>

                        <div class="mt-6 flex items-center gap-4">
                            <x-primary-button>Créer le projet</x-primary-button>
                            <a href="{{ route('workspaces.show', $workspace) }}"
                               class="text-sm text-gray-600 hover:text-gray-900 underline">
                                Annuler
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
