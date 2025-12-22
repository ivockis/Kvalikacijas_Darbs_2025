@if ($projects->isEmpty())
    <p class="text-gray-600">{{ __('No public projects available that match your criteria.') }}</p>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($projects as $project)
            <div class="bg-gray-100 rounded-lg shadow-md flex flex-col justify-between">
                <a href="{{ route('projects.show', $project) }}">
                    <img src="{{ $project->cover_image_url }}" alt="{{ $project->title }} Cover Image" class="w-full h-48 object-cover rounded-t-lg">
                </a>
                <div class="p-6">
                    <div class="flex items-start justify-between"> {{-- Use flex to position title/description/author/created on left, like on right --}}
                        <div class="flex-grow pr-2 min-w-0"> {{-- Added flex-grow, padding, and min-w-0 --}}
                            <h3 class="text-lg font-semibold text-gray-800 mb-2 whitespace-nowrap overflow-hidden text-ellipsis">
                                <a href="{{ route('projects.show', $project) }}" class="hover:underline">{{ $project->title }}</a>
                            </h3>
                            <p class="text-gray-600 text-sm mb-4 overflow-hidden text-ellipsis line-clamp-2">{{ Str::limit($project->description, 100) }}</p>
                            <p class="text-xs text-gray-500">{{ __('Author:') }} <a href="{{ route('users.show', $project->user) }}" class="font-medium text-blue-600 hover:underline">{{ $project->user->name }}</a></p>
                            <p class="text-xs text-gray-500">{{ __('Created:') }} {{ $project->created_at->format('d.m.Y') }}</p>
                        </div>
                        {{-- Like Button --}}
                        <div x-data="{ 
                                    liked: {{ Auth::check() && $project->likers->contains(Auth::id()) ? 'true' : 'false' }}, 
                                    likesCount: {{ $project->likers->count() }},
                                    async toggleLike() {
                                        @auth
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
                                        @else
                                            window.location.href = '{{ route('login') }}'; // Redirect to login if not authenticated
                                        @endauth
                                    }
                                }" class="flex flex-col items-center flex-shrink-0"> {{-- Added flex-shrink-0 --}}
                                    <button @click="toggleLike" class="flex flex-col items-center text-gray-500 hover:text-red-500 focus:outline-none">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" 
                                             :class="{ 'text-red-500': liked, 'text-gray-400': !liked }">
                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                    <span x-text="likesCount" class="text-xs text-gray-600 font-medium"></span>
                        </div>
                    </div>
                    <div class="mt-4 flex justify-between items-center"> {{-- Use justify-between for estimated hours and view project button --}}
                        <p class="text-xs text-gray-500 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $project->estimated_hours }} {{ __('H') }}
                        </p>
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
