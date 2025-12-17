<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $project->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-2xl font-bold">{{ $project->title }}</h3>
                        <div class="flex items-center space-x-2">
                            @can('update', $project)
                                <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-4 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 focus:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Edit') }}
                                </a>
                            @endcan
                            @can('delete', $project)
                                <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this project?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
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
                                                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                                                     x-data="{ 
                                                        reason: '',
                                                        complaintOptions: [
                                                            'Inappropriate or offensive content.',
                                                            'Spam or misleading information.',
                                                            'Copyright or intellectual property violation.',
                                                            'Dangerous or harmful instructions.'
                                                        ]
                                                     }">
                                                    <form method="POST" action="{{ route('projects.complain', $project) }}">
                                                        @csrf
                                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                                                {{ __('Report Project') }}: {{ $project->title }}
                                                            </h3>
                                                            <div class="mt-4">
                                                                <p class="text-sm text-gray-500 mb-2">
                                                                    {{ __('Select a reason or write your own:') }}
                                                                </p>
                                                                <div class="flex flex-wrap gap-2 mb-4">
                                                                    <template x-for="option in complaintOptions" :key="option">
                                                                        <button @click.prevent="reason = option" type="button" class="px-3 py-1 text-xs text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-full" x-text="option"></button>
                                                                    </template>
                                                                </div>
                                                                <textarea name="reason" x-model="reason" rows="4" class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required minlength="10" placeholder="{{ __('Or provide your own reason here...') }}"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                                                                {{ __('Submit Report') }}
                                                            </button>
                                                            <button @click="open = false" type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
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

                    <p class="text-gray-600 mb-4">{{ $project->description }}</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                        <div>
                            <p class="font-semibold">{{ __('Author:') }} <a href="{{ route('users.show', $project->user) }}" class="text-blue-600 hover:underline">{{ $project->user->username }}</a></p>
                            <p class="font-semibold">{{ __('Created:') }} {{ $project->created_at->format('d.m.Y H:i') }}</p>
                            <p class="font-semibold">{{ __('Public:') }} {{ $project->is_public ? __('Yes') : __('No') }}</p>
                        </div>
                        <div>
                            @if($project->materials)
                                <p class="font-semibold">{{ __('Materials:') }}</p>
                                <p>{{ $project->materials }}</p>
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
                        <div class="mt-6">
                            <p class="font-semibold">{{ __('Images:') }}</p>
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-2">
                                @foreach ($project->images as $image)
                                    <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $project->title }} image" class="rounded-lg shadow-md w-full h-32 object-cover">
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-8">
                        @auth
                            <form method="POST" action="{{ route('projects.like', $project) }}">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    @if ($liked)
                                        {{ __('Unlike') }} ({{ $project->likers->count() }})
                                    @else
                                        {{ __('Like') }} ({{ $project->likers->count() }})
                                    @endif
                                </button>
                            </form>
                        @else
                            <span class="text-gray-600">{{ $project->likers->count() }} {{ __('Likes') }}</span>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div
        x-data="{ show: {{ session('status') === 'complaint-submitted' ? 'true' : 'false' }} }"
        x-init="() => { if (show) setTimeout(() => show = false, 3000) }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform translate-y-2"
        class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg"
        style="display: none;"
    >
        {{ __('Report submitted successfully!') }}
    </div>
</x-app-layout>
