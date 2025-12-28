<x-app-layout>
    <head>
        <title>{{ __('Welcome to Craftify!') }}</title>
        <style>
            .hero-banner {
                background-image: url('{{ asset('storage/images/banner.jpg') }}');
                background-size: cover;
                background-position: center;
                height: 90vh; /* Slightly less than full to show there's content below */
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                text-align: center;
                position: relative;
            }
            .hero-banner::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.6); /* Dark overlay */
                z-index: 1;
            }
            .hero-content {
                z-index: 2;
                padding: 2rem;
                max-width: 900px;
            }
        </style>
    </head>
    <div class="hero-banner">
        <div class="hero-content">
            <h1 class="text-5xl md:text-6xl font-extrabold leading-tight mb-4">{{ __('Welcome to Craftify!') }}</h1>
            <p class="text-lg md:text-xl mb-8">{{ __('Your ultimate destination for sharing and discovering inspiring craft projects.') }}</p>
            <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                @guest
                    <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 md:py-4 md:text-lg md:px-10">
                        {{ __('Get Started') }}
                    </a>
                @endguest
                 <a href="{{ route('public.index') }}" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 md:py-4 md:text-lg md:px-10">
                    {{ __('Explore Projects') }}
                </a>
            </div>
        </div>
    </div>

    <div class="bg-gray-900">
        <div class="container mx-auto px-6 py-12">
            @if ($projects->isNotEmpty())
                <h2 class="text-4xl font-bold text-center mb-10 text-white">{{ __('Featured Public Projects') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach ($projects as $project)
                        <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden transform transition duration-300 hover:scale-105 flex flex-col">
                            <a href="{{ route('projects.show', [$project, 'from' => 'welcome']) }}">
                                <img src="{{ $project->cover_image_url }}" alt="{{ $project->title }}" class="w-full h-48 object-cover">
                            </a>
                            <div class="p-6 flex flex-col flex-grow">
                                <div class="flex-grow">
                                    <h3 class="font-bold text-xl mb-2 text-white whitespace-nowrap overflow-hidden text-ellipsis" title="{{ $project->title }}">{{ $project->title }}</h3>
                                    <p class="text-gray-400 text-sm mb-4 overflow-hidden" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">
                                        {{ $project->description }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-between mt-auto">
                                    <span class="text-gray-500 text-xs">{{ __('Author') }}: {{ $project->user->username }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @guest
                    <div class="text-center mt-12 p-8 bg-gray-800 rounded-lg shadow-lg">
                        <h3 class="text-3xl font-bold mb-4 text-white">{{ __('Want to see more projects?') }}</h3>
                        <p class="text-lg text-gray-400 mb-6">{{ __('Register for free to unlock all features, explore unlimited projects, and share your own creations!') }}</p>
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-10 py-4 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 md:text-lg md:px-12">
                            {{ __('Register Now!') }}
                        </a>
                    </div>
                @endguest
            @else
                <p class="text-center text-gray-400 text-lg">{{ __('No public projects available yet.') }}</p>
            @endif
        </div>
    </div>
</x-app-layout>
