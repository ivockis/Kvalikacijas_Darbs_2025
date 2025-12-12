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
                            // Initialize with the ID of the current cover, if it exists
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
                        }">
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
                            <x-input-error :messages="$errors->get('creation_time')" class="mt-2" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="categories" :value="__('Categories')" />
                            <select name="categories[]" id="categories" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" multiple>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(in_array($category->id, old('categories', $project->categories->pluck('id')->toArray())))>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="tools" :value="__('Tools')" />
                            <select name="tools[]" id="tools" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" multiple>
                                @foreach ($tools as $tool)
                                    <option value="{{ $tool->id }}" @selected(in_array($tool->id, old('tools', $project->tools->pluck('id')->toArray())))>{{ $tool->name }}</option>
                                @endforeach
                            </select>
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
                                                <label class="cursor-pointer text-white px-2 py-1 rounded-md bg-indigo-600">
                                                    <input type="radio" name="cover_image_selection" :value="'new_' + index" @change="selectedCover = 'new_' + index" class="mr-1">Set as Cover
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
                                                    <label class="cursor-pointer text-white px-2 py-1 rounded-md bg-indigo-600">
                                                        <input type="radio" name="cover_image_selection" :value="'existing_' + {{ $image->id }}" @change="selectedCover = 'existing_' + {{ $image->id }}" :checked="selectedCover === 'existing_{{ $image->id }}'">Set as Cover
                                                    </label>
                                                </div>
                                                @if ($image->is_cover)
                                                    <span class="absolute bottom-1 left-1 bg-blue-500 text-white rounded-md px-2 py-1 text-xs">Cover</span>
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
