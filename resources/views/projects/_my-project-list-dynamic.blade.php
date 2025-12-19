@if ($projects->isEmpty())
    <p class="text-gray-600">{{ __('No projects created yet that match your criteria.') }}</p>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach ($projects as $project)
            <div class="bg-gray-100 rounded-lg shadow-md flex flex-col justify-between">
                <a href="{{ route('projects.show', $project) }}">
                    <img src="{{ $project->cover_image_url }}" alt="{{ $project->title }} Cover Image" class="w-full h-48 object-cover rounded-t-lg">
                </a>
                <div class="p-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-2 whitespace-nowrap overflow-hidden text-ellipsis">
                            <a href="{{ route('projects.show', $project) }}" class="hover:underline">{{ $project->title }}</a>
                        </h3>
                        <p class="text-gray-600 text-sm mb-4 overflow-hidden text-ellipsis line-clamp-2">{{ Str::limit($project->description, 100) }}</p>
                        <p class="text-xs text-gray-500">{{ __('Created:') }} {{ $project->created_at->format('d.m.Y') }}</p>
                        <p class="text-xs text-gray-500">{{ __('Public:') }} {{ $project->is_public ? __('Yes') : __('No') }}</p>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-3 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('View') }}
                        </a>
                        <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-3 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 focus:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Edit') }}
                        </a>
                        <form action="{{ route('projects.destroy', $project) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this project?') }}');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $projects->links() }}
    </div>
@endif