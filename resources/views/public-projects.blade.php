<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Public Projects Gallery') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Search, Filter, Sort Form -->
                    <form method="GET" action="{{ route('public.index') }}" class="mb-6 flex flex-wrap gap-4 items-end">
                        <div class="flex-grow">
                            <x-input-label for="search" :value="__('Search by Title')" class="sr-only" />
                            <x-text-input id="search" name="search" type="text" placeholder="{{ __('Search projects...') }}" value="{{ request('search') }}" class="w-full" />
                        </div>

                        <div>
                            <x-input-label for="category_id" :value="__('Filter by Category')" class="sr-only" />
                            <select id="category_id" name="category_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">{{ __('All Categories') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="sort_by" :value="__('Sort By')" class="sr-only" />
                            <select id="sort_by" name="sort_by" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="latest" @selected(request('sort_by') == 'latest')>{{ __('Latest') }}</option>
                                <option value="oldest" @selected(request('sort_by') == 'oldest')>{{ __('Oldest') }}</option>
                                <option value="title_asc" @selected(request('sort_by') == 'title_asc')>{{ __('Title (A-Z)') }}</option>
                                <option value="title_desc" @selected(request('sort_by') == 'title_desc')>{{ __('Title (Z-A)') }}</option>
                                {{-- <option value="most_liked" @selected(request('sort_by') == 'most_liked')>{{ __('Most Liked') }}</option> --}} {{-- Implement when likes sorting is ready --}}
                            </select>
                        </div>

                        <div>
                            <x-primary-button type="submit">{{ __('Apply Filters') }}</x-primary-button>
                        </div>
                        
                        @if(request('search') || request('category_id') || request('sort_by') != 'latest')
                            <div>
                                <a href="{{ route('public.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    {{ __('Reset Filters') }}
                                </a>
                            </div>
                        @endif
                    </form>

                    @if ($projects->isEmpty())
                        <p class="text-gray-600">{{ __('No public projects available yet.') }}</p>
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
                                        <p class="text-xs text-gray-500">{{ __('Author:') }} <a href="{{ route('users.show', $project->user) }}" class="font-medium text-blue-600 hover:underline">{{ $project->user->name }}</a></p>
                                            <p class="text-xs text-gray-500">{{ __('Created:') }} {{ $project->created_at->format('d.m.Y') }}</p>
                                        </div>
                                        <div class="mt-4 flex justify-end">
                                            <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-3 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
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