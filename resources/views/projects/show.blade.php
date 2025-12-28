<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between no-print">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $project->title }}
            </h2>
            <a href="{{ route('public.index') }}" class="inline-flex items-center px-2 py-1 bg-gray-600 border border-gray-500 rounded-md font-semibold text-xs text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-500">
                &laquo; {{ __('Back') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-200" x-data="{ confirmingDelete: null }">
                    <div class="mb-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <h3 class="text-2xl font-bold max-w-lg break-words">{{ $project->title }}</h3>
                                @if ($project->is_blocked && Auth::id() === $project->user_id)
                                    <span class="ml-4 px-2 py-1 bg-red-600 text-white text-xs font-semibold rounded-full">
                                        {{ __('Project is blocked') }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center space-x-2 no-print">

                                <!-- Like Button -->
                                @auth
                                    <div x-data="{ 
                                        liked: {{ $liked ? 'true' : 'false' }}, 
                                        likesCount: {{ $project->likers->count() }},
                                        async toggleLike() {
                                            const response = await fetch('{{ route('projects.like', $project) }}', {
                                                method: 'POST',
                                                headers: {
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                }
                                            });
                                            const data = await response.json();
                                            this.liked = data.liked;
                                            this.likesCount = data.likes_count;
                                        }
                                    }" class="flex items-center space-x-2">
                                        <button @click="toggleLike" class="flex items-center space-x-1 text-gray-500 hover:text-blue-500 focus:outline-none">
                                            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20" 
                                                 :class="{ 'text-blue-500': liked, 'text-gray-400': !liked }">
                                                <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"></path>
                                            </svg>
                                        </button>
                                        <span x-text="likesCount" class="text-gray-400 font-medium"></span>
                                    </div>
                                @else
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                        </svg>
                                        <span class="text-gray-400 font-medium">{{ $project->likers->count() }}</span>
                                    </div>
                                @endauth

                                <div class="w-px h-6 bg-gray-600 mx-2"></div> <!-- Divider -->

                                @can('update', $project)
                                    <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 focus:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Edit') }}
                                    </a>
                                @endcan
                                @can('delete', $project)
                                    <form id="delete-project-form" action="{{ route('projects.destroy', $project) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="confirmingDelete = true" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                            {{ __('Delete') }}
                                        </button>
                                    </form>
                                @endcan
                                
                               
                                
                                <!-- Report Project Button -->
                                @auth
                                    @if(Auth::id() !== $project->user_id)
                                        <div x-data="{ open: false }" 
                                             x-init="$watch('open', value => { if(value) document.body.classList.add('overflow-y-hidden'); else document.body.classList.remove('overflow-y-hidden'); })">
                                            <button 
                                                @click="open = {{ $hasComplained ? 'false' : 'true' }}" 
                                                :disabled="{{ $hasComplained ? 'true' : 'false' }}"
                                                class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-600 focus:bg-yellow-600 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                                :class="{ 'opacity-50 cursor-not-allowed': {{ $hasComplained ? 'true' : 'false' }} }"
                                                title="{{ $hasComplained ? __('You have already reported this project.') : '' }}"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6H8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" /></svg>
                                                {{ $hasComplained ? __('Reported') : __('Report') }}
                                            </button>

                                            <!-- Modal -->
                                            <div x-show="open" @keydown.escape.window="open = false" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" style="display: none;">
                                                <div class="flex items-end justify-center min-h-screen px-4 text-center md:items-center sm:block sm:p-0">
                                                    <div x-show="open" @click="open = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                                                    <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                                                         class="inline-block align-bottom bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                                         x-data="{ 
                                                            reason: '',
                                                            complaintOptions: [
                                                                '{{ __("Inappropriate or offensive content.") }}',
                                                                '{{ __("Spam or misleading information.") }}',
                                                                '{{ __("Copyright or intellectual property violation.") }}',
                                                                '{{ __("Dangerous or harmful instructions.") }}'
                                                            ]
                                                         }">
                                                        <form method="POST" action="{{ route('projects.complain', $project) }}">
                                                            @csrf
                                                            <div class="bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                                <h3 class="text-lg leading-6 font-medium text-gray-200" id="modal-title">
                                                                    {{ __('Report Project') }}: {{ $project->title }}
                                                                </h3>
                                                                <div class="mt-4">
                                                                    <p class="text-sm text-gray-400 mb-2">
                                                                        {{ __('Select a reason or write your own:') }}
                                                                    </p>
                                                                    <div class="flex flex-wrap gap-2 mb-4">
                                                                        <template x-for="option in complaintOptions" :key="option">
                                                                            <button @click.prevent="reason = option" type="button" class="px-3 py-1 text-xs text-gray-300 bg-gray-600 hover:bg-gray-500 rounded-full" x-text="option"></button>
                                                                        </template>
                                                                    </div>
                                                                    <textarea name="reason" x-model="reason" rows="4" class="w-full rounded-md shadow-sm border-gray-600 bg-gray-700 text-gray-300 focus:border-indigo-500 focus:ring-indigo-500" required minlength="10" maxlength="1000" placeholder="{{ __('Or provide your own reason here...') }}"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                                    {{ __('Submit Report') }}
                                                                </button>
                                                                <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-500 shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-gray-300 hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                                                    {{ __('Cancel') }}
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endauth
                            </div>
                        </div>
                        <!-- Average Rating Display -->
                        <div class="flex items-center space-x-2 mb-4 no-print">
                            <div class="flex items-center">
                                @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-5 h-5 @if ($i <= round($averageRating ?? 0)) text-yellow-400 @else text-gray-300 @endif" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.963a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.446a1 1 0 00-.364 1.118l1.287 3.963c.3.921-.755 1.688-1.54 1.118l-3.368-2.446a1 1 0 00-1.175 0l-3.368 2.446c-.784.57-1.838-.197-1.539-1.118l1.287-3.963a1 1 0 00-.364-1.118L2.063 9.39c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69L9.049 2.927z"/>
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-sm text-gray-400">
                                @if(($ratingsCount ?? 0) > 0)
                                    {{ number_format($averageRating ?? 0, 1) }} ({{ $ratingsCount ?? 0 }}) 
                                @else
                                    {{ __('(0)') }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <p class="text-gray-400 mb-4 whitespace-pre-wrap">{{ $project->description }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <p class="font-semibold">{{ __('Author:') }} <a href="{{ route('users.show', $project->user) }}" class="text-blue-600 hover:underline">{{ $project->user->username }}</a></p>
                            <p class="font-semibold">{{ __('Created:') }} {{ $project->created_at->format('d.m.Y H:i') }}</p>
                            <p class="font-semibold">{{ __('Public:') }} {{ $project->is_public ? __('Yes') : __('No') }}</p>
                            <p class="font-semibold">{{ __('Estimated Hours:') }} {{ $project->estimated_hours }}</p>
                        </div>
                        <div>
                            @if($project->materials)
                                <p class="font-semibold">{{ __('Materials:') }}</p>
                                <p class="whitespace-pre-wrap">{{ $project->materials }}</p>
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
                        <div class="mt-6" x-data="{
                            lightboxOpen: false,
                            currentImageIndex: 0,
                            allImages: {{ json_encode($project->images->map(fn($image) => asset('storage/' . $image->path))->toArray()) }},
                            get currentImage() { return this.allImages[this.currentImageIndex] },
                            get currentImageAlt() { return '{{ $project->title }} image' }, // Alt text could be dynamic if images had titles

                            openLightbox(index) {
                                this.currentImageIndex = index;
                                this.lightboxOpen = true;
                            },
                            nextImage() {
                                if (this.currentImageIndex < this.allImages.length - 1) {
                                    this.currentImageIndex++;
                                }
                            },
                            prevImage() {
                                if (this.currentImageIndex > 0) {
                                    this.currentImageIndex--;
                                }
                            }
                        }">
                            <p class="font-semibold">{{ __('Images:') }}</p>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-2">
                                @foreach ($project->images as $index => $image)
                                    <img src="{{ asset('storage/' . $image->path) }}"
                                         alt="{{ $project->title }} image"
                                         class="rounded-lg shadow-md w-full h-32 object-cover cursor-pointer"
                                         @click="openLightbox({{ $index }})">
                                @endforeach
                            </div>

                            <!-- Image Lightbox Modal -->
                            <div x-show="lightboxOpen"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75 no-print"
                                 style="display: none;"
                                 @click.away="lightboxOpen = false"
                                 @keydown.escape.window="lightboxOpen = false">
                                <div class="relative w-11/12 h-11/12 md:w-3/4 md:h-3/4 flex items-center justify-center">
                                    <img :src="currentImage" :alt="currentImageAlt" class="w-full h-full object-contain">

                                    <!-- Navigation Buttons -->
                                    <template x-if="allImages.length > 1">
                                        <div>
                                            <button @click.stop="prevImage()"
                                                    :disabled="currentImageIndex === 0"
                                                    class="absolute left-4 top-1/2 -translate-y-1/2 bg-gray-800 bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 disabled:opacity-50 disabled:cursor-not-allowed">
                                                &#10094; <!-- Left arrow -->
                                            </button>
                                            <button @click.stop="nextImage()"
                                                    :disabled="currentImageIndex === allImages.length - 1"
                                                    class="absolute right-4 top-1/2 -translate-y-1/2 bg-gray-800 bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-75 disabled:opacity-50 disabled:cursor-not-allowed">
                                                &#10095; <!-- Right arrow -->
                                            </button>
                                        </div>
                                    </template>

                                    <button @click="lightboxOpen = false" class="absolute top-4 right-4 text-white text-3xl focus:outline-none">
                                        &times;
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Ratings and Comments Section -->
                    <div class="mt-8 border-t border-gray-700 pt-8 no-print">
                        <h3 class="text-2xl font-bold mb-4">{{ __('Ratings & Comments') }}</h3>



                        @auth
                            @if(Auth::id() !== $project->user_id)
                                                            <!-- Your Rating Section -->
                                                            <div class="bg-gray-700 p-6 rounded-lg mb-6" x-data="{
                                                                hoverRating: 0,
                                                                selectedRating: {{ optional($userRating)->rating ?? 0 }},
                                                                hasUserRating: {{ optional($userRating)->rating ? 'true' : 'false' }},
                                                                get starColor() {
                                                                    return (star) => {
                                                                        // If user has rated, display their rating
                                                                        if (this.hasUserRating) {
                                                                            return star <= this.selectedRating ? 'text-yellow-400' : 'text-gray-300';
                                                                        }
                                                                        // If no rating, allow hover and selection
                                                                        if (this.hoverRating > 0) {
                                                                            return star <= this.hoverRating ? 'text-yellow-400' : 'text-gray-300';
                                                                        }
                                                                        return star <= this.selectedRating ? 'text-yellow-400' : 'text-gray-300';
                                                                    }
                                                                }
                                                            }">
                                                                <h4 class="text-lg font-semibold mb-2">{{ __('Your Rating') }}</h4>
                                                                <p class="text-sm text-gray-400 mb-2">{{ optional($userRating)->rating ? __('You have already rated this project. To change it, remove your current rating and post a new one.') : __('Select a rating for this project.') }}</p>
                                
                                                                <form :action="hasUserRating ? '{{ route('ratings.destroy', $project) }}' : '{{ route('ratings.store', $project) }}'" method="POST" onsubmit="return hasUserRating ? confirm('Are you sure you want to remove your rating?') : true;">
                                                                    @csrf
                                                                    <template x-if="hasUserRating">@method('DELETE')</template>
                                
                                                                    <!-- Star Rating Input -->
                                                                    <div class="flex items-center space-x-1 mb-4" :class="{'pointer-events-none': hasUserRating}">
                                                                        <template x-for="i in 5" :key="i">
                                                                            <svg @mouseover="!hasUserRating && (hoverRating = i)"
                                                                                 @mouseleave="!hasUserRating && (hoverRating = 0)"
                                                                                 @click="!hasUserRating && (selectedRating = i)"
                                                                                 class="w-8 h-8 transition-colors duration-150"
                                                                                 :class="{'cursor-pointer': !hasUserRating, 'text-yellow-400': starColor(i) === 'text-yellow-400', 'text-gray-300': starColor(i) === 'text-gray-300'}"
                                                                                 fill="currentColor" viewBox="0 0 20 20">
                                                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.963a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.368 2.446a1 1 0 00-.364 1.118l1.287 3.963c.3.921-.755 1.688-1.54 1.118l-3.368-2.446a1 1 0 00-1.175 0l-3.368 2.446c-.784.57-1.838-.197-1.539-1.118l1.287-3.963a1 1 0 00-.364-1.118L2.063 9.39c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69L9.049 2.927z"/>
                                                                            </svg>
                                                                        </template>
                                                                    </div>
                                                                    <input type="hidden" name="rating" :value="selectedRating">
                                
                                                                    <div class="flex items-center justify-between mt-4">
                                                                        <button type="submit"
                                                                                x-show="selectedRating > 0 || hasUserRating"
                                                                                :class="hasUserRating ? 'bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:ring-red-500' : 'bg-indigo-500 hover:bg-indigo-600 focus:bg-indigo-600 active:bg-indigo-700 focus:ring-indigo-500'"
                                                                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150"
                                                                                x-text="hasUserRating ? '{{ __('Remove My Rating') }}' : '{{ __('Post Rating') }}'">
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                            @endif
                            <!-- Your Comment Section -->
                            <div class="bg-gray-700 p-6 rounded-lg mb-6" x-data="{ commentInput: '' }">
                                <h4 class="text-lg font-semibold mb-2">{{ __('Post a Comment') }}</h4>
                                <form action="{{ route('comments.store', $project) }}" method="POST">
                                    @csrf
                                    <textarea name="comment" rows="4" class="w-full rounded-md shadow-sm border-gray-600 bg-gray-800 text-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="{{ __('Share your thoughts about this project...') }}" maxlength="2000" x-model="commentInput"></textarea>
                                    <x-input-error class="mt-2" :messages="$errors->get('comment')" />

                                    <div class="flex items-center justify-end mt-4">
                                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150" x-show="commentInput.trim() !== ''">
                                            {{ __('Post Comment') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @else
                            <p class="text-gray-400 mb-6">{{ __('Log in to rate and comment.') }}</p>
                        @endauth

                        <!-- Comments List -->
                        <div class="mt-8 space-y-6">
                            @forelse ($comments as $comment)
                                @php
                                    $isProjectOwner = Auth::check() && (Auth::id() === $project->user_id);
                                @endphp
                                @if ($comment->is_visible || $isProjectOwner)
                                    <div class="flex space-x-4" 
                                         :class="{ 'opacity-50': !commentVisible && {{ $isProjectOwner ? 'true' : 'false' }} }" 
                                         id="comment-{{ $comment->id }}" 
                                         x-data="{ isEditing: false, commentBody: '{{ $comment->comment }}', confirmingCommentDeletion: null, commentVisible: {{ $comment->is_visible ? 'true' : 'false' }} }">
                                        <img src="{{ $comment->user->profile_image_url }}" alt="{{ $comment->user->username }}" class="h-10 w-10 rounded-full object-cover">
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <a href="{{ route('users.show', $comment->user) }}" class="font-semibold text-gray-200">{{ $comment->user->username }}</a>
                                                    @if ($comment->user_id == $project->user_id)
                                                        <span class="ml-2 px-2 py-0.5 bg-indigo-100 text-indigo-800 text-xs font-semibold rounded-full">{{ __('Author') }}</span>
                                                    @endif
                                                    <span class="text-xs text-gray-400 ml-2">{{ $comment->created_at->diffForHumans() }}</span>
                                                    <span x-show="!commentVisible && {{ $isProjectOwner ? 'true' : 'false' }}" class="ml-2 px-2 py-0.5 bg-red-100 text-red-800 text-xs font-semibold rounded-full">{{ __('Hidden') }}</span>
                                                </div>
                                                <div class="flex items-center space-x-2">
                                                    @canany(['update', 'delete'], $comment)
                                                        {{-- Edit Button --}}
                                                        @can('update', $comment)
                                                            <button x-show="!isEditing" @click="isEditing = true" class="text-indigo-600 hover:text-indigo-900 text-sm">{{ __('Edit') }}</button>
                                                        @endcan
                                                        {{-- Delete Button --}}
                                                        @can('delete', $comment)
                                                            <button x-show="!isEditing" @click="confirmingCommentDeletion = true" class="text-red-600 hover:text-red-900 text-sm">{{ __('Delete') }}</button>
                                                        @endcan
                                                    @endcanany

                                                    @auth
                                                        @if ($isProjectOwner)
                                                            <div x-data="{
                                                                toggleVisibility() {
                                                                    fetch('{{ route('comments.update', $comment) }}', {
                                                                        method: 'PATCH',
                                                                        headers: {
                                                                            'Content-Type': 'application/json',
                                                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                                            'Accept': 'application/json'
                                                                        },
                                                                        body: JSON.stringify({ is_visible: !this.commentVisible })
                                                                    }).then(response => {
                                                                        if (response.ok) {
                                                                            this.commentVisible = !this.commentVisible;
                                                                            // Optionally, show a success message
                                                                        } else {
                                                                            response.json().then(data => {
                                                                                alert(data.message || 'Error toggling comment visibility.');
                                                                            });
                                                                        }
                                                                    }).catch(error => {
                                                                        console.error('Error:', error);
                                                                        alert('An error occurred.');
                                                                    });
                                                                }
                                                            }">
                                                                <button @click="toggleVisibility()"
                                                                        class="text-sm font-medium"
                                                                        :class="commentVisible ? 'text-orange-500 hover:text-orange-600' : 'text-green-500 hover:text-green-600'"
                                                                        x-text="commentVisible ? '{{ __('Hide') }}' : '{{ __('Show') }}'">
                                                                </button>
                                                            </div>
                                                        @endif
                                                    @endauth
                                                </div>
                                            </div>
                                            <!-- Display mode -->
                                            <p x-show="!isEditing" class="text-gray-300 mt-1" x-text="commentBody"></p>

                                            <!-- Edit mode -->
                                            <form x-show="isEditing" method="POST" action="{{ route('comments.update', $comment) }}" @submit.prevent="fetch(event.target.action, {
                                                method: 'PATCH',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                },
                                                body: JSON.stringify({ comment: commentBody })
                                            }).then(response => {
                                                if (response.ok) {
                                                    isEditing = false;
                                                    // Optionally, you can show a success message here
                                                } else {
                                                    // Handle errors (e.g., show validation errors)
                                                    response.json().then(data => {
                                                        alert(data.message || 'Error updating comment.');
                                                    });
                                                }
                                            }).catch(error => {
                                                console.error('Error:', error);
                                                alert('An error occurred.');
                                            })">
                                                @csrf
                                                @method('patch')
                                                <textarea x-model="commentBody" rows="3" class="w-full rounded-md shadow-sm border-gray-600 bg-gray-800 text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 mt-1" maxlength="2000"></textarea>
                                                <div class="flex items-center gap-2 mt-2">
                                                    <button type="submit" class="px-3 py-1 bg-indigo-500 text-white text-xs rounded hover:bg-indigo-600" x-bind:disabled="commentBody.trim() === ''" :class="{ 'opacity-50 cursor-not-allowed': commentBody.trim() === '' }">{{ __('Save') }}</button>
                                                    <button type="button" @click="isEditing = false; commentBody = '{{ ($comment->comment) }}'" class="px-3 py-1 bg-gray-600 text-gray-300 text-xs rounded hover:bg-gray-500">{{ __('Cancel') }}</button>
                                                </div>
                                            </form>

                                            <!-- Confirmation Modal for Comment Deletion -->
                                            <div x-show="confirmingCommentDeletion" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75 no-print" style="display: none;">
                                                <div @click.away="confirmingCommentDeletion = null" class="bg-gray-800 rounded-lg p-6 shadow-xl w-1/3 mx-auto">
                                                    <h3 class="text-lg font-semibold mb-4">{{ __('Confirm Deletion') }}</h3>
                                                    <p class="mb-4">{{ __('Are you sure you want to delete this comment?') }}</p>
                                                    <div class="flex justify-end space-x-4">
                                                        <button type="button" @click="confirmingCommentDeletion = null" class="px-4 py-2 bg-gray-600 text-gray-300 rounded-md">{{ __('Cancel') }}</button>
                                                        <form action="{{ route('comments.destroy', $comment) }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md">{{ __('Delete') }}</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @empty
                                <p class="text-gray-400">{{ __('No comments yet!') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <!-- Confirmation Modal -->
                    <div x-show="confirmingDelete !== null" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75 no-print" style="display: none;">
                        <div @click.away="confirmingDelete = null" class="bg-gray-800 rounded-lg p-6 shadow-xl w-1/3 mx-auto">
                            <h3 class="text-lg font-semibold mb-4">{{ __('Confirm Deletion') }}</h3>
                            <p class="mb-4">{{ __('Are you sure you want to delete this project? This action cannot be undone.') }}</p>
                            <div class="flex justify-end space-x-4">
                                <button type="button" @click="confirmingDelete = null" class="px-4 py-2 bg-gray-600 text-gray-300 rounded-md">{{ __('Cancel') }}</button>
                                <button type="button" @click="document.getElementById('delete-project-form').submit()" class="px-4 py-2 bg-red-600 text-white rounded-md">{{ __('Delete') }}</button>
                            </div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>
</x-app-layout>
