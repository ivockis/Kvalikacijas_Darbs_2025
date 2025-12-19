<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Projects') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-semibold text-lg text-gray-800">{{ __('My Projects') }}</h3>
                        <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Add New Project') }}
                        </a>
                    </div>

                    <form id="filter-form" method="GET" action="{{ route('projects.index') }}" class="mb-6 flex flex-wrap gap-4 items-end">
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
                            </select>
                        </div>

                        <div>
                            <x-input-label for="per_page" :value="__('Items per page')" class="sr-only" />
                            <x-per-page-selector />
                        </div>
                        
                        <div id="reset-filters-wrapper" class="hidden">
                            <a href="{{ route('projects.index') }}" id="reset-filters-btn" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Reset Filters') }}
                            </a>
                        </div>
                    </form>

                    <div id="project-list-container">
                        @include('projects._my-project-list-dynamic')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>