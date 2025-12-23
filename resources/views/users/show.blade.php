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
                                    <a href="{{ route('projects.show', $project) }}">
                                        <img src="{{ $project->cover_image_url }}" alt="{{ $project->title }} {{ __("Cover Image") }}" class="w-full h-48 object-cover rounded-t-lg">
                                    </a>
                                    <div class="p-6">
                                        <div class="flex items-start justify-between mb-2">
                                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 whitespace-nowrap overflow-hidden text-ellipsis flex-grow pr-2 min-w-0">
                                                <a href="{{ route('projects.show', $project) }}" class="hover:underline">{{ $project->title }}</a>
                                            </h3>
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
                                                        window.location.href = '{{ route('login') }}';
                                                    @endauth
                                                }
                                            }" class="flex flex-col items-center flex-shrink-0">
                                                <button @click="toggleLike" class="flex flex-col items-center text-gray-500 hover:text-red-500 focus:outline-none">
                                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" 
                                                         :class="{ 'text-red-500': liked, 'text-gray-400': !liked }">
                                                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                                                    </svg>
                                                </button>
                                                <span x-text="likesCount" class="text-xs text-gray-400 font-medium"></span>
                                            </div>
                                        </div>
                                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 overflow-hidden text-ellipsis line-clamp-2">{{ Str::limit($project->description, 100) }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('Created:') }} {{ $project->created_at->format('d.m.Y') }}</p>
                                        <div class="mt-4 flex justify-between items-center">
                                            <p class="text-xs text-gray-400 flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                {{ $project->estimated_hours }} {{ __('H') }}
                                            </p>
                                            <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-3 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600">
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
