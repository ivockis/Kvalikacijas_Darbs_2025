<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Public Projects Gallery') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-200">
                    <form id="filter-form" method="GET" action="{{ route('public.index') }}" class="mb-6 flex flex-wrap gap-4 items-end">
                        <div class="flex-grow">
                            <x-input-label for="search" :value="__('Search by Title')" class="sr-only" />
                            <x-text-input id="search" name="search" type="text" placeholder="{{ __('Search projects...') }}" value="{{ request('search') }}" class="w-full" />
                        </div>

                        <div>
                            <x-input-label for="category_id" :value="__('Filter by Category')" class="sr-only" />
                            <select id="category_id" name="category_id" class="block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="">{{ __('All Categories') }}</option>
                                <option value="liked" @selected(request('category_id') == 'liked')>{{ __('Liked') }}</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>


                        <div>
                            <x-input-label for="sort_by" :value="__('Sort By')" class="sr-only" />
                            <select id="sort_by" name="sort_by" class="block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <option value="latest" @selected(request('sort_by') == 'latest')>{{ __('Latest') }}</option>
                                <option value="oldest" @selected(request('sort_by') == 'oldest')>{{ __('Oldest') }}</option>
                                <option value="title_asc" @selected(request('sort_by') == 'title_asc')>{{ __('Title (A-Z)') }}</option>
                                <option value="title_desc" @selected(request('sort_by') == 'title_desc')>{{ __('Title (Z-A)') }}</option>
                                <option value="estimated_hours_asc" @selected(request('sort_by') == 'estimated_hours_asc')>{{ __('Estimated Hours (Asc)') }}</option>
                                <option value="estimated_hours_desc" @selected(request('sort_by') == 'estimated_hours_desc')>{{ __('Estimated Hours (Desc)') }}</option>
                                <option value="most_liked" @selected(request('sort_by') == 'most_liked')>{{ __('Most Liked') }}</option>
                                <option value="highest_rated" @selected(request('sort_by') == 'highest_rated')>{{ __('Highest Rated') }}</option>

                            </select>
                        </div>

                        <div>
                            <x-input-label for="per_page" :value="__('Items per page')" class="sr-only" />
                            <x-per-page-selector />
                        </div>
                        
                        <div id="reset-filters-wrapper" class="hidden">
                            <a href="{{ route('public.index') }}" id="reset-filters-btn" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Reset Filters') }}
                            </a>
                        </div>
                    </form>

                    <div id="project-list-container">
                        @include('projects._public-project-list-dynamic')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
