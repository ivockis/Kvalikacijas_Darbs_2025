<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Project') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form 
                        x-ref="createForm"
                        @submit.prevent="submitForm"
                        x-data="{
                            // Image state
                            newImages: [],
                            imageFiles: [],
                            selectedCoverIndex: null, // Initialized to null

                            // Categories state
                            categories: {
                                open: false,
                                search: '',
                                options: {{ $categories->toJson() }},
                                selected: [],
                                get filteredOptions() {
                                    return this.options.filter(
                                        option => !this.selected.some(s => s.id === option.id) && option.name.toLowerCase().includes(this.search.toLowerCase())
                                    )
                                }
                            },

                            // Tools state
                            tools: {
                                open: false,
                                search: '',
                                options: {{ $tools->toJson() }},
                                selected: [],
                                newToolComment: '',
                                get filteredOptions() {
                                    return this.options.filter(
                                        option => option.approved && !this.selected.some(s => s.id === option.id) && option.name.toLowerCase().includes(this.search.toLowerCase())
                                    )
                                }
                            },

                            // Image Functions
                            handleImageSelect(event) {
                                const files = Array.from(event.target.files);
                                if ((this.imageFiles.length + files.length) > 10) {
                                    alert('You can only upload a maximum of 10 images.');
                                    return;
                                }
                                for (const file of files) {
                                    this.imageFiles.push(file);
                                    let reader = new FileReader();
                                    reader.onload = (e) => this.newImages.push(e.target.result);
                                    reader.readAsDataURL(file);
                                }
                                if (this.selectedCoverIndex === null && this.newImages.length > 0) {
                                    this.selectedCoverIndex = 0; // Automatically set first image as cover
                                }
                            },
                            removeNewImage(index) {
                                this.newImages.splice(index, 1);
                                this.imageFiles.splice(index, 1);
                                if (this.newImages.length === 0) { // If no images left
                                    this.selectedCoverIndex = null;
                                } else if (this.selectedCoverIndex === index) { // If deleted cover
                                    this.selectedCoverIndex = 0; // Set first remaining as new cover
                                } else if (this.selectedCoverIndex > index) { // If deleted before cover
                                    this.selectedCoverIndex--;
                                }
                            },

                            // Tool Functions
                            async createNewTool() {
                                if (!this.tools.search.trim()) return;
                                const response = await fetch('{{ route('tools.store') }}', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    body: JSON.stringify({ name: this.tools.search, comment: this.tools.newToolComment })
                                });
                                if (!response.ok) {
                                    alert('Failed to create tool. It might already exist.');
                                    return;
                                }
                                const newTool = await response.json();
                                this.tools.options.push(newTool);
                                this.tools.selected.push(newTool);
                                this.tools.search = '';
                                this.tools.newToolComment = '';
                                this.tools.open = false;
                            },

                            // Main Form Submission
                            submitForm() {
                                // Create a new FormData object from the form
                                const formData = new FormData(this.$refs.createForm);
                                
                                // Manually remove default image input if files were selected via Alpine
                                if (this.imageFiles.length > 0) {
                                    formData.delete('images[]');
                                }

                                // Append categories and tools
                                this.categories.selected.forEach(cat => formData.append('categories[]', cat.id));
                                this.tools.selected.forEach(tool => formData.append('tools[]', tool.id));

                                // Append image files
                                this.imageFiles.forEach((file, index) => {
                                    formData.append(`images[${index}]`, file);
                                });

                                // Append cover selection
                                if (this.selectedCoverIndex !== null) {
                                    formData.append('cover_image_selection', `new_${this.selectedCoverIndex}`);
                                }

                                // Use fetch to submit the form
                                fetch('{{ route('projects.store') }}', {
                                    method: 'POST',
                                    body: formData,
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                })
                                .then(response => {
                                    if (response.ok) {
                                        window.location.href = '{{ route('projects.index') }}';
                                    } else {
                                        return response.json().then(result => {
                                            // Simple alert for errors, can be improved
                                            const errorMessages = Object.values(result.errors).map(e => e.join('\n')).join('\n');
                                            alert('Validation failed:\n' + errorMessages);
                                        });
                                    }
                                })
                                .catch(error => {
                                    alert('An unexpected error occurred.');
                                    console.error('Submit Error:', error);
                                });
                            }
                        }"
                    >
                        @csrf

                        <!-- All form fields -->
                        <div>
                            <x-input-label for="title">{{ __('Title') }}<span class="text-red-500">*</span></x-input-label>
                            <x-text-input id="title" class="block mt-1 w-full" type="text" name="title" :value="old('title')" required autofocus maxlength="100" />
                        </div>
                        <div class="mt-4">
                            <x-input-label for="description">{{ __('Description') }}<span class="text-red-500">*</span></x-input-label>
                            <textarea id="description" class="block mt-1 w-full rounded-md" name="description" required maxlength="10000">{{ old('description') }}</textarea>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="materials">{{ __('Materials') }}<span class="text-red-500">*</span></x-input-label>
                            <textarea id="materials" class="block mt-1 w-full rounded-md" name="materials" required maxlength="5000">{{ old('materials') }}</textarea>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="estimated_hours">{{ __('Estimated Hours for Creation') }}<span class="text-red-500">*</span></x-input-label>
                            <x-text-input id="estimated_hours" class="block mt-1 w-full" type="number" name="estimated_hours" :value="old('estimated_hours')" required min="1" max="1000" />
                        </div>

                        <!-- Categories -->
                        <div class="mt-4">
                            <x-input-label for="categories-search">{{ __('Categories') }}<span class="text-red-500">*</span></x-input-label>
                            <div class="flex flex-wrap gap-2 mt-2 mb-2">
                                <template x-for="s in categories.selected" :key="s.id">
                                    <span class="inline-flex items-center px-2 py-1 bg-indigo-100 text-indigo-800 text-sm font-medium rounded-full">
                                        <span x-text="s.name"></span>
                                        <button @click="categories.selected = categories.selected.filter(i => i.id !== s.id)" type="button" class="ml-2 text-indigo-500 hover:text-indigo-700">&times;</button>
                                    </span>
                                </template>
                            </div>
                            <div @click.away="categories.open = false" class="relative">
                                <input id="categories-search" type="text" x-model="categories.search" @focus="categories.open = true" @input="categories.open = true" placeholder="Search categories..." class="w-full rounded-md" maxlength="50">
                                <div x-show="categories.open && categories.filteredOptions.length > 0" class="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="option in categories.filteredOptions" :key="option.id">
                                        <div @click="categories.selected.push(option); categories.search = ''; categories.open = false" class="cursor-pointer px-4 py-2 hover:bg-gray-100" x-text="option.name"></div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Tools -->
                        <div class="mt-4">
                            <x-input-label for="tools-search" :value="__('Tools')" />
                            <div class="flex flex-wrap gap-2 mt-2 mb-2">
                                <template x-for="s in tools.selected" :key="s.id">
                                     <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                                        <span x-text="s.name"></span>
                                        <template x-if="s.comment"><span class="ml-1 text-xs" x-text="'(' + s.comment + ')'"></span></template>
                                        <button @click="tools.selected = tools.selected.filter(i => i.id !== s.id)" type="button" class="ml-2 text-green-500 hover:text-green-700">&times;</button>
                                    </span>
                                </template>
                            </div>
                            <div @click.away="tools.open = false" class="relative">
                                <input id="tools-search" type="text" x-model="tools.search" @focus="tools.open = true" @input="tools.open = true" placeholder="Search or add a new tool..." class="w-full rounded-md" maxlength="50">
                                <div x-show="tools.open" class="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="option in tools.filteredOptions" :key="option.id">
                                        <div @click="tools.selected.push(option); tools.search = ''; tools.open = false" class="cursor-pointer px-4 py-2 hover:bg-gray-100">
                                            <span x-text="option.name"></span>
                                            <template x-if="option.comment"><span class="ml-1 text-xs text-gray-500" x-text="'(' + option.comment + ')'"></span></template>
                                        </div>
                                    </template>
                                    <div x-show="tools.search && tools.filteredOptions.length === 0" class="p-4 border-t">
                                        <p class="mb-2">Create new tool: <strong x-text="tools.search"></strong></p>
                                        <input type="text" x-model="tools.newToolComment" placeholder="Optional comment..." class="w-full text-sm rounded-md mb-2" maxlength="50">
                                        <button @click="createNewTool" type="button" class="w-full text-sm px-4 py-2 bg-indigo-600 text-white rounded-md">Create & Add</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Images -->
                        <div class="mt-4">
                            <x-input-label for="images">{{ __('Images') }}<span class="text-red-500">*</span></x-input-label>
                            <input id="images" type="file" name="images[]" class="block mt-1 w-full" multiple @change="handleImageSelect($event)">
                            <x-input-error :messages="$errors->get('images')" class="mt-2" />
                            <x-input-error :messages="$errors->get('images.*')" class="mt-2" />

                            <div class="mt-4" x-show="newImages.length > 0">
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mt-2">
                                    <template x-for="(image, index) in newImages" :key="index">
                                        <div class="relative group">
                                            <img :src="image" class="rounded-lg shadow-md w-full h-32 object-cover">
                                            <button type="button" @click="removeNewImage(index)" class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg></button>
                                            <label class="absolute bottom-1 left-1 cursor-pointer text-white px-2 py-1 rounded-md text-xs" :class="selectedCoverIndex === index ? 'bg-indigo-600' : 'bg-gray-700 opacity-0 group-hover:opacity-100'">
                                                <input type="radio" name="cover_image_selection" :value="index" x-model.number="selectedCoverIndex" class="hidden">
                                                <span x-text="selectedCoverIndex === index ? 'Cover' : 'Set Cover'"></span>
                                            </label>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <label for="is_public" class="inline-flex items-center">
                                <input id="is_public" type="checkbox" class="rounded border-gray-300" name="is_public" value="1">
                                <span class="ms-2 text-sm text-gray-600">{{ __('Make Public') }}</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <x-primary-button class="ms-4" type="submit">
                                {{ __('Create Project') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>