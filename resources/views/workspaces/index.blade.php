<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                My workspaces
            </h2>
            <a href="{{ route('workspaces.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                + New workspace
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Flash message --}}
            @if (session('success'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if ($workspaces->isEmpty())
                {{-- Empty state --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-12 text-center">
                        <p class="text-gray-500 text-sm">You don't have any workspace yet.</p>
                        <a href="{{ route('workspaces.create') }}"
                           class="mt-4 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                            Create my first workspace
                        </a>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($workspaces as $workspace)
                        <a href="{{ route('workspaces.show', $workspace) }}"
                           class="block bg-white overflow-hidden shadow-sm sm:rounded-lg hover:shadow-md transition-shadow duration-150">
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <h3 class="font-semibold text-gray-900 truncate">
                                        {{ $workspace->name }}
                                    </h3>
                                    {{-- User's role badge in this workspace --}}
                                    @php $role = $workspace->pivot->role; @endphp
                                    <span class="ml-2 shrink-0 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $role === 'owner' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $role === 'owner' ? 'Owner' : 'Member' }}
                                    </span>
                                </div>
                                @if ($workspace->description)
                                    <p class="mt-2 text-sm text-gray-500 line-clamp-2">
                                        {{ $workspace->description }}
                                    </p>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
