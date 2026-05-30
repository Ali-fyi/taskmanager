<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('workspaces.show', $workspace) }}" class="hover:text-gray-700">{{ $workspace->name }}</a>
            <span>/</span>
            <a href="{{ route('workspaces.statuses.index', $workspace) }}" class="hover:text-gray-700">Statuses</a>
            <span>/</span>
            <span class="text-gray-800 font-medium">New</span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 sm:p-8">

                    <form method="POST" action="{{ route('workspaces.statuses.store', $workspace) }}">
                        @csrf

                        <div>
                            <x-input-label for="name" value="Status name" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name')" placeholder="E.g. In review, Blocked..." autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-6">
                            <x-input-label for="color" value="Color" />
                            <div class="mt-1 flex items-center gap-3">
                                <input type="color" id="color" name="color"
                                    value="{{ old('color', '#6366f1') }}"
                                    class="h-10 w-16 rounded-md border border-gray-300 cursor-pointer p-1" />
                                <span class="text-sm text-gray-500">Identification color for the status.</span>
                            </div>
                            <x-input-error :messages="$errors->get('color')" class="mt-2" />
                        </div>

                        <div class="mt-6">
                            <x-input-label for="position" value="Position (display order)" />
                            <x-text-input id="position" name="position" type="number" min="0"
                                class="mt-1 block w-32" :value="old('position', 0)" />
                            <x-input-error :messages="$errors->get('position')" class="mt-2" />
                        </div>

                        <div class="mt-6 flex items-center gap-4">
                            <x-primary-button>Create status</x-primary-button>
                            <a href="{{ route('workspaces.statuses.index', $workspace) }}"
                               class="text-sm text-gray-600 hover:text-gray-900 underline">Cancel</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
