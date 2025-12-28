<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between w-full"> {{-- Added w-full for full width --}}
            <div class="flex items-center space-x-4">
                <img src="{{ $user->profile_image_url }}" alt="{{ $user->username }}" class="h-16 w-16 rounded-full object-cover">
                <div>
                    <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                        {{ $user->username }}
                    </h2>
                    {{-- Conditional display for name, surname, and email --}}
                    @if (Auth::check() && Auth::user()->is_admin)
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $user->name }} {{ $user->surname }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ $user->email }}</p>
                    @endif
                </div>
            </div>
            <a href="#" onclick="history.back()" class="inline-flex items-center px-2 py-1 bg-gray-700 dark:bg-gray-700 border border-gray-600 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-200 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-600 dark:hover:bg-gray-600">
                &laquo; {{ __('Back') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-gray-800 dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-200">
                    <h3 class="text-xl font-semibold dark:text-gray-200 mb-4">{{ __('Public Projects by User') }} {{ $user->username }}</h3>
                    
                    @if ($projects->isEmpty())
                        <p class="text-gray-600 dark:text-gray-400">{{ __(':username has not posted any public projects yet.', ['username' => $user->username]) }}</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($projects as $project)
                                <div class="bg-gray-700 dark:bg-gray-700 rounded-lg shadow-md dark:shadow-lg flex flex-col justify-between transform transition duration-300 hover:scale-105">
                                    <a href="{{ route('projects.show', [$project, 'from' => 'user_profile', 'from_id' => $user->id]) }}">
                                        <img src="{{ $project->cover_image_url }}" alt="{{ $project->title }} {{ __("Cover Image") }}" class="w-full h-48 object-cover rounded-t-lg">
                                    </a>
                                    <div class="p-6">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2 whitespace-nowrap overflow-hidden text-ellipsis">
                                                <a href="{{ route('projects.show', [$project, 'from' => 'user_profile', 'from_id' => $user->id]) }}" class="hover:underline">{{ $project->title }}</a>
                                            </h3>
                                            <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 h-10 overflow-hidden text-ellipsis line-clamp-2">{{ Str::limit($project->description, 100) }}</p>
                                            <div x-data="{ 
                                                        liked: {{ Auth::check() && $project->likers->contains(Auth::id()) ? 'true' : 'false' }}, 
                                                        likesCount: {{ $project->likers_count }},
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
                                                                window.location.href = '{{ route('login') }}';
                                                            @endauth
                                                        }
                                                    }" class="flex justify-between items-end text-xs text-gray-400 dark:text-gray-300 mb-2">
                                                                                                <p class="flex items-center">
                                                                                                    {{ __('Created:') }} <span class="ml-1">{{ $project->created_at->format('d.m.Y') }}</span>
                                                                                                </p>
                                                <div class="flex flex-col items-end space-y-1">
                                                    <p class="flex items-center">
                                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                        {{ $project->estimated_hours }} {{ __('H') }}
                                                    </p>
                                                    <div class="flex items-center space-x-2">
                                                        @if ($project->ratings_count > 0)
                                                            <p class="flex items-center">
                                                                <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.929 8.72c-.783-.57-.381-1.81.588-1.81h3.462a1 1 0 00.95-.69l1.07-3.292z"></path>
                                                                </svg>
                                                                <span class="ml-1">{{ number_format($project->ratings_avg_rating, 1) }}</span>
                                                                <span class="ml-1 text-gray-500">({{ $project->ratings_count }})</span>
                                                            </p>
                                                        @endif
                                                        <button @click="toggleLike" class="flex items-center text-gray-500 hover:text-blue-500 focus:outline-none">
                                                            <svg class="w-4 h-4 mr-1" :class="{ 'text-blue-500': liked, 'text-gray-400': !liked }" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"></path>
                                                            </svg>
                                                        </button>
                                                        <span x-text="likesCount"></span>
                                                    </div>
                                                </div>
                                            </div>
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
