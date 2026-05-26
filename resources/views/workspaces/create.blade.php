<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Nouveau workspace
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">

                    <form method="POST" action="{{ route('workspaces.store') }}">
                        @csrf

                        {{-- Nom --}}
                        <div>
                            <x-input-label for="name" value="Nom du workspace" />
                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('name')"
                                placeholder="Ex : Mon équipe, Projet client..."
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
                                placeholder="Décrivez brièvement ce workspace..."
                            >{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mt-6 flex items-center gap-4">
                            <x-primary-button>Créer le workspace</x-primary-button>
                            <a href="{{ route('workspaces.index') }}"
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
