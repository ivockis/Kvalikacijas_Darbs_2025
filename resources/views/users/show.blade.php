<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <img src="{{ $user->profile_image_url }}" alt="{{ $user->username }}" class="h-16 w-16 rounded-full object-cover">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                    {{ $user->username }}
                </h2>
                <p class="text-sm text-gray-600">{{ $user->name }} {{ $user->surname }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-semibold mb-4">Public Projects by {{ $user->username }}</h3>
                    
                    @if ($projects->isEmpty())
                        <p class="text-gray-600">{{ __(':username has not posted any public projects yet.', ['username' => $user->username]) }}</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($projects as $project)
                                <div class="bg-gray-100 rounded-lg shadow-md flex flex-col justify-between">
                                    <a href="{{ route('projects.show', $project) }}">
                                        <img src="{{ $project->cover_image_url }}" alt="{{ $project->title }} Cover Image" class="w-full h-48 object-cover rounded-t-lg">
                                    </a>
                                    <div class="p-6">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-800 mb-2 whitespace-nowrap overflow-hidden text-ellipsis">
                                                <a href="{{ route('projects.show', $project) }}" class="hover:underline">{{ $project->title }}</a>
                                            </h3>
                                            <p class="text-gray-600 text-sm mb-4 overflow-hidden text-ellipsis line-clamp-2">{{ Str::limit($project->description, 100) }}</p>
                                            <p class="text-xs text-gray-500">{{ __('Created:') }} {{ $project->created_at->format('d.m.Y') }}</p>
                                        </div>
                                        <div class="mt-4 flex justify-end">
                                            <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-3 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600">
                                                {{ __('View Project') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8">
                            {{ $projects->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
