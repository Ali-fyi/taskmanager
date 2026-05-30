<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate">
                {{ $workspace->name }}
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('workspaces.statuses.index', $workspace) }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition ease-in-out duration-150">
                    Statuses
                </a>
                @can('update', $workspace)
                    <a href="{{ route('workspaces.edit', $workspace) }}"
                       class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 transition ease-in-out duration-150">
                        Edit
                    </a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Flash message --}}
            @if (session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Workspace information --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">About</h3>
                    <p class="mt-2 text-gray-900">
                        {{ $workspace->description ?? 'No description.' }}
                    </p>
                </div>
            </div>

            {{-- Members --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">
                        Members ({{ $workspace->members->count() }})
                    </h3>

                    {{-- Member removal error --}}
                    @if ($errors->has('member'))
                        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                            {{ $errors->first('member') }}
                        </div>
                    @endif

                    <ul class="divide-y divide-gray-100">
                        @foreach ($workspace->members as $member)
                            <li class="flex items-center justify-between py-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $member->email }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        {{ $member->pivot->role === 'owner' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $member->pivot->role === 'owner' ? 'Owner' : 'Member' }}
                                    </span>
                                    @can('manageMembers', $workspace)
                                        @if ($member->id !== $workspace->owner_id)
                                            <form method="POST"
                                                  action="{{ route('workspaces.members.destroy', [$workspace, $member]) }}"
                                                  onsubmit="return confirm('Remove {{ $member->name }} from the workspace?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="text-xs text-red-500 hover:text-red-700 transition-colors">
                                                    Remove
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Invitation form (owner only) --}}
                    @can('manageMembers', $workspace)
                        <div class="mt-5 pt-5 border-t border-gray-100">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Invite a member</h4>

                            @if ($errors->has('invite_email'))
                                <div class="mb-3 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
                                    {{ $errors->first('invite_email') }}
                                </div>
                            @endif

                            <form method="POST"
                                  action="{{ route('workspaces.members.store', $workspace) }}"
                                  class="flex gap-2">
                                @csrf
                                <input type="email"
                                       name="email"
                                       value="{{ old('email') }}"
                                       placeholder="email@example.com"
                                       required
                                       class="flex-1 border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm @error('email') border-red-300 @enderror">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                                    Invite
                                </button>
                            </form>

                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    @endcan
                </div>
            </div>

            {{-- Projects --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide">
                            Projects ({{ $workspace->projects->count() }})
                        </h3>
                        <a href="{{ route('workspaces.projects.create', $workspace) }}"
                           class="inline-flex items-center px-3 py-1.5 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150">
                            + New project
                        </a>
                    </div>

                    @if ($workspace->projects->isEmpty())
                        <p class="text-sm text-gray-500">No project yet.</p>
                    @else
                        <ul class="divide-y divide-gray-100">
                            @foreach ($workspace->projects as $project)
                                <li class="py-3">
                                    <a href="{{ route('workspaces.projects.show', [$workspace, $project]) }}"
                                       class="flex items-center gap-3 group">
                                        <span class="w-3 h-3 rounded-full shrink-0"
                                              style="background-color: {{ $project->color }}"></span>
                                        <span class="text-sm font-medium text-gray-900 group-hover:text-indigo-600 transition-colors">
                                            {{ $project->name }}
                                        </span>
                                        @if ($project->description)
                                            <span class="text-xs text-gray-400 truncate hidden sm:block">
                                                {{ $project->description }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Recent activity --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">
                        Recent activity
                    </h3>

                    @if ($activities->isEmpty())
                        <p class="text-sm text-gray-400">No activity yet.</p>
                    @else
                        <ul class="space-y-3">
                            @foreach ($activities as $activity)
                                @php
                                    $subjectName = match($activity->subject_type) {
                                        'App\\Models\\Project' =>
                                            $activity->subject?->name
                                            ?? $activity->properties['attributes']['name']
                                            ?? '(deleted)',
                                        'App\\Models\\Task' =>
                                            $activity->subject?->title
                                            ?? $activity->properties['attributes']['title']
                                            ?? '(deleted)',
                                        'App\\Models\\Comment' => 'comment',
                                        default => '',
                                    };
                                @endphp
                                <li class="flex items-start gap-3 text-sm">
                                    <span class="mt-0.5 w-2 h-2 rounded-full bg-indigo-400 shrink-0"></span>
                                    <div class="flex-1 min-w-0">
                                        <span class="font-medium text-gray-800">
                                            {{ $activity->causer?->name ?? 'System' }}
                                        </span>
                                        <span class="text-gray-600"> — {{ $activity->description }}</span>
                                        <span class="text-gray-500"> : {{ $subjectName }}</span>
                                    </div>
                                    <span class="text-xs text-gray-400 shrink-0">
                                        {{ $activity->created_at->diffForHumans() }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            {{-- Danger zone — deletion (owner only) --}}
            @can('delete', $workspace)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-red-200">
                    <div class="p-6">
                        <h3 class="text-sm font-medium text-red-600 uppercase tracking-wide">Danger zone</h3>
                        <p class="mt-2 text-sm text-gray-600">
                            Deleting the workspace is irreversible.
                        </p>
                        <form method="POST" action="{{ route('workspaces.destroy', $workspace) }}"
                              onsubmit="return confirm('Permanently delete this workspace?')">
                            @csrf
                            @method('DELETE')
                            <x-danger-button class="mt-4">
                                Delete workspace
                            </x-danger-button>
                        </form>
                    </div>
                </div>
            @endcan

        </div>
    </div>
</x-app-layout>
