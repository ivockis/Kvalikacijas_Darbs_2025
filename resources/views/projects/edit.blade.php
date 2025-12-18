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
                    <form method="POST" action="{{ route('projects.update', $project) }}" enctype="multipart/form-data"
                        x-data="{
                            newImages: [],
                            currentTotalImages: {{ $project->images->count() }},
                            selectedCover: '{{ optional($project->images->firstWhere('is_cover'))->id ? 'existing_' . optional($project->images->firstWhere('is_cover'))->id : '' }}',

                            handleImageSelect(event) {
                                const files = Array.from(event.target.files);
                                if ((this.currentTotalImages + files.length) > 10) {
                                    alert('You can only have a maximum of 10 images in total.');
                                    event.target.value = '';
                                    return;
                                }
                                this.newImages = [];
                                files.forEach(file => {
                                    let reader = new FileReader();
                                    reader.onload = (e) => this.newImages.push(e.target.result);
                                    reader.readAsDataURL(file);
                                });
                            },
                            async deleteImage(imageId, element) {
                                if (!confirm('Are you sure you want to delete this image?')) return;
                                const response = await fetch(`/images/${imageId}`, {
                                    method: 'DELETE',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                });
                                if (response.ok) {
                                    element.remove();
                                    this.currentTotalImages--;
                                    if (this.selectedCover === `existing_${imageId}`) {
                                        this.selectedCover = '';
                                    }
                                } else {
                                    alert('Failed to delete image.');
                                }
                            }
                        }"
                    >
                        @csrf
                        @method('patch')

                        <!-- Title, Description, etc. -->
                        <div>
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title', $project->title)" required autofocus maxlength="100" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required maxlength="10000">{{ old('description', $project->description) }}</textarea>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="materials" :value="__('Materials')" />
                            <textarea id="materials" name="materials" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required maxlength="5000">{{ old('materials', $project->materials) }}</textarea>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="estimated_hours" :value="__('Estimated Hours for Creation')" />
                            <x-text-input id="estimated_hours" class="block mt-1 w-full" type="number" name="estimated_hours" :value="old('estimated_hours', $project->estimated_hours)" required min="1" max="1000" />
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
                                <input id="categories-search" type="text" x-model="search" @focus="open = true" @input="open = true" placeholder="Search categories..." class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" maxlength="50">
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
                                newToolComment: '',
                                get filteredOptions() {
                                    return this.options.filter(
                                        option => option.approved && !this.selected.some(s => s.id === option.id) && option.name.toLowerCase().includes(this.search.toLowerCase())
                                    )
                                },
                                async createNewTool() {
                                    if (!this.search.trim()) return;
                                    const response = await fetch('{{ route('tools.store') }}', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                        body: JSON.stringify({ name: this.search, comment: this.newToolComment })
                                    });
                                    if (!response.ok) {
                                        alert('Failed to create tool. It might already exist.');
                                        return;
                                    }
                                    const newTool = await response.json();
                                    this.options.push(newTool);
                                    this.selected.push(newTool);
                                    this.search = '';
                                    this.newToolComment = '';
                                    this.open = false;
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
                                <input id="tools-search" type="text" x-model="search" @focus="open = true" @input="open = true" placeholder="Search or add a new tool..." class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" maxlength="50">
                                <div x-show="open" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="option in filteredOptions" :key="option.id">
                                        <div @click="selected.push(option); search = ''; open = false" class="cursor-pointer px-4 py-2 hover:bg-gray-100">
                                            <span x-text="option.name"></span>
                                            <template x-if="option.comment">
                                                <span class="ml-1 text-xs text-gray-500" x-text="'(' + option.comment + ')'"></span>
                                            </template>
                                        </div>
                                    </template>
                                    <!-- Create new tool UI -->
                                    <div x-show="search && filteredOptions.length === 0" class="p-4 border-t">
                                        <p class="mb-2">Create new tool: <strong x-text="search"></strong></p>
                                        <input type="text" x-model="newToolComment" placeholder="Optional comment..." class="w-full text-sm rounded-md shadow-sm border-gray-300 mb-2" maxlength="50">
                                        <button @click="createNewTool" type="button" class="w-full text-sm px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-500">Create & Add</button>
                                    </div>
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('tools')" class="mt-2" />
                        </div>

                        <!-- Images Section -->
                        <div>
                            <!-- Image Upload -->
                            <div class="mt-4">
                                <x-input-label for="images" :value="__('Upload New Images')" />
                                <input id="images" name="images[]" type="file" class="block mt-1 w-full" multiple @change="handleImageSelect($event)">
                            </div>

                            <!-- New Image Previews -->
                            <div class="mt-4" x-show="newImages.length > 0">
                                <h4 class="font-semibold text-md text-gray-700">New Previews</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mt-2">
                                    <template x-for="(image, index) in newImages" :key="index">
                                        <div class="relative group">
                                            <img :src="image" class="rounded-lg shadow-md w-full h-32 object-cover">
                                            <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100">
                                                <label class="absolute bottom-1 left-1 cursor-pointer text-white px-2 py-1 rounded-md text-xs" :class="selectedCover === 'new_' + index ? 'bg-indigo-600' : 'bg-gray-700 opacity-0 group-hover:opacity-100'">
                                                    <input type="radio" name="cover_image_selection" :value="'new_' + index" x-model="selectedCover" class="hidden">
                                                    <span x-text="selectedCover === 'new_' + index ? 'Cover' : 'Set Cover'"></span>
                                                </label>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Current Images -->
                            @if ($project->images->isNotEmpty())
                                <div class="mt-6">
                                    <h4 class="font-semibold text-md text-gray-700">Current Images</h4>
                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mt-2">
                                        @foreach ($project->images as $image)
                                            <div class="relative group" x-ref="image-{{ $image->id }}">
                                                <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $project->title }}" class="rounded-lg shadow-md w-full h-32 object-cover">
                                                <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100">
                                                    <button type="button" @click="deleteImage({{ $image->id }}, $refs['image-{{ $image->id }}'])" class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                                                    <label class="absolute bottom-1 left-1 cursor-pointer text-white px-2 py-1 rounded-md text-xs" :class="selectedCover === 'existing_{{ $image->id }}' ? 'bg-indigo-600' : 'bg-gray-700 opacity-0 group-hover:opacity-100'">
                                                        <input type="radio" name="cover_image_selection" :value="'existing_' + {{ $image->id }}" x-model="selectedCover" class="hidden">
                                                        <span x-text="selectedCover === 'existing_{{ $image->id }}' ? 'Cover' : 'Set Cover'"></span>
                                                    </label>
                                                </div>
                                                @if ($image->is_cover)
                                                    <span class="absolute bottom-1 right-1 bg-blue-500 text-white rounded-md px-2 py-1 text-xs">Cover</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        
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