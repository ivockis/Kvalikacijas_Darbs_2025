<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $project->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold">{{ $project->title }}</h3>
                        <div class="flex space-x-2">
                            @can('update', $project)
                                <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 focus:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Edit') }}
                                </a>
                            @endcan
                            @can('delete', $project)
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this project?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Delete') }}
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>

                    <p class="text-gray-600 mb-4">{{ $project->description }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <p class="font-semibold">{{ __('Author:') }} <a href="#" class="text-blue-600">{{ $project->user->name }}</a></p>
                            <p class="font-semibold">{{ __('Created:') }} {{ $project->created_at->format('d.m.Y H:i') }}</p>
                            <p class="font-semibold">{{ __('Public:') }} {{ $project->is_public ? __('Yes') : __('No') }}</p>
                        </div>
                        <div>
                            @if($project->materials)
                                <p class="font-semibold">{{ __('Materials:') }}</p>
                                <p>{{ $project->materials }}</p>
                            @endif
                        </div>
                    </div>

                    @if ($project->categories->isNotEmpty())
                        <div class="mt-6">
                            <p class="font-semibold">{{ __('Categories:') }}</p>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach ($project->categories as $category)
                                    <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-indigo-900 dark:text-indigo-300">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($project->tools->isNotEmpty())
                        <div class="mt-6">
                            <p class="font-semibold">{{ __('Tools:') }}</p>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach ($project->tools as $tool)
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">
                                        {{ $tool->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($project->images->isNotEmpty())
                        <div class="mt-6">
                            <p class="font-semibold">{{ __('Images:') }}</p>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-2">
                                @foreach ($project->images as $image)
                                    <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $project->title }} image" class="rounded-lg shadow-md w-full h-32 object-cover">
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-8 flex items-center space-x-4">
                        @auth
                            <form method="POST" action="{{ route('projects.like', $project) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    @if ($liked)
                                        {{ __('Unlike') }} ({{ $project->likers->count() }})
                                    @else
                                        {{ __('Like') }} ({{ $project->likers->count() }})
                                    @endif
                                </button>
                            </form>
                        @else
                            <span class="text-gray-600">{{ $project->likers->count() }} {{ __('Likes') }}</span>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
