<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Project') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('projects.update', $project) }}" enctype="multipart/form-data">
                        @csrf
                        @method('patch')

                        <!-- Title, Description, etc. -->
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $project->title)" required autofocus />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('description', $project->description) }}</textarea>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="materials" :value="__('Materials')" />
                            <textarea id="materials" name="materials" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('materials', $project->materials) }}</textarea>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="creation_time" :value="__('Time for Creation')" />
                            <x-text-input id="creation_time" class="block mt-1 w-full" type="text" name="creation_time" :value="old('creation_time', $project->creation_time)" required />
                        </div>
                        <!-- Categories -->
                        <div class="mt-4"
                            x-data="{
                                open: false,
                                search: '',
                                options: {{ $categories->toJson() }},
                                selected: {{ $project->categories->toJson() }},
                                get filteredOptions() {
                                    return this.options.filter(
                                        option => !this.selected.some(s => s.id === option.id) && option.name.toLowerCase().includes(this.search.toLowerCase())
                                    )
                                }
                            }"
                        >
                            <x-input-label for="categories-search" :value="__('Categories')" />
                            <!-- Hidden inputs for submission -->
                            <template x-for="s in selected" :key="s.id">
                                <input type="hidden" name="categories[]" :value="s.id">
                            </template>
                            <!-- Selected Tags -->
                            <div class="flex flex-wrap gap-2 mt-2 mb-2">
                                <template x-for="s in selected" :key="s.id">
                                    <span class="inline-flex items-center px-2 py-1 bg-indigo-100 text-indigo-800 text-sm font-medium rounded-full">
                                        <span x-text="s.name"></span>
                                        <button @click="selected = selected.filter(i => i.id !== s.id)" type="button" class="ml-2 text-indigo-500 hover:text-indigo-700">
                                            &times;
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <!-- Search Input -->
                            <div @click.away="open = false" class="relative">
                                <input id="categories-search" type="text" x-model="search" @focus="open = true" @input="open = true" placeholder="Search categories..." class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <div x-show="open && filteredOptions.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="option in filteredOptions" :key="option.id">
                                        <div @click="selected.push(option); search = ''; open = false" class="cursor-pointer px-4 py-2 hover:bg-gray-100" x-text="option.name"></div>
                                    </template>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('categories')" class="mt-2" />
                        </div>
                        <!-- Tools -->
                        <div class="mt-4"
                            x-data="{
                                open: false,
                                search: '',
                                options: {{ $tools->toJson() }},
                                selected: {{ $project->tools->toJson() }},
                                get filteredOptions() {
                                    return this.options.filter(
                                        option => !this.selected.some(s => s.id === option.id) && option.name.toLowerCase().includes(this.search.toLowerCase())
                                    )
                                }
                            }"
                        >
                            <x-input-label for="tools-search" :value="__('Tools')" />
                            <!-- Hidden inputs for submission -->
                            <template x-for="s in selected" :key="s.id">
                                <input type="hidden" name="tools[]" :value="s.id">
                            </template>
                            <!-- Selected Tags -->
                            <div class="flex flex-wrap gap-2 mt-2 mb-2">
                                <template x-for="s in selected" :key="s.id">
                                    <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                        <span x-text="s.name"></span>
                                        <template x-if="s.comment">
                                            <span class="ml-1 text-xs" x-text="'(' + s.comment + ')'"></span>
                                        </template>
                                        <button @click="selected = selected.filter(i => i.id !== s.id)" type="button" class="ml-2 text-green-500 hover:text-green-700">
                                            &times;
                                        </button>
                                    </span>
                                </template>
                            </div>
                            <!-- Search Input -->
                            <div @click.away="open = false" class="relative">
                                <input id="tools-search" type="text" x-model="search" @focus="open = true" @input="open = true" placeholder="Search tools..." class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <div x-show="open && filteredOptions.length > 0" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="option in filteredOptions" :key="option.id">
                                        <div @click="selected.push(option); search = ''; open = false" class="cursor-pointer px-4 py-2 hover:bg-gray-100">
                                            <span x-text="option.name"></span>
                                            <template x-if="option.comment">
                                                <span class="ml-1 text-xs text-gray-500" x-text="'(' + option.comment + ')'"></span>
                                            </template>
                                        </template>
                                    </div>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('tools')" class="mt-2" />
                        </div>

                        <!-- Images Section... -->
                        
                        <!-- Is Public -->
                        <div class="mt-4">
                            <label for="is_public" class="inline-flex items-center">
                                <input id="is_public" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="is_public" @checked(old('is_public', $project->is_public))>
                                <span class="ms-2 text-sm text-gray-600">{{ __('Make Public') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4">
                                {{ __('Update Project') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>