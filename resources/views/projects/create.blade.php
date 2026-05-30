<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('workspaces.show', $workspace) }}" class="hover:text-gray-700">
                {{ $workspace->name }}
            </a>
            <span>/</span>
            <span class="text-gray-800 font-medium">New project</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">

                    <form method="POST" action="{{ route('workspaces.projects.store', $workspace) }}">
                        @csrf

                        {{-- Name --}}
                        <div>
                            <x-input-label for="name" value="Project name" />
                            <x-text-input
                                id="name"
                                name="name"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('name')"
                                placeholder="E.g. Marketing site, Mobile app..."
                                autofocus
                            />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        {{-- Description --}}
                        <div class="mt-6">
                            <x-input-label for="description" value="Description (optional)" />
                            <textarea
                                id="description"
                                name="description"
                                rows="3"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                placeholder="Briefly describe this project..."
                            >{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        {{-- Color --}}
                        <div class="mt-6">
                            <x-input-label for="color" value="Accent color" />
                            <div class="mt-1 flex items-center gap-3">
                                <input
                                    type="color"
                                    id="color"
                                    name="color"
                                    value="{{ old('color', '#6366f1') }}"
                                    class="h-10 w-16 rounded-md border border-gray-300 cursor-pointer p-1"
                                />
                                <span class="text-sm text-gray-500">Pick a color to identify this project.</span>
                            </div>
                            <x-input-error :messages="$errors->get('color')" class="mt-2" />
                        </div>

                        <div class="mt-6 flex items-center gap-4">
                            <x-primary-button>Create project</x-primary-button>
                            <a href="{{ route('workspaces.show', $workspace) }}"
                               class="text-sm text-gray-600 hover:text-gray-900 underline">
                                Cancel
                            </a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
