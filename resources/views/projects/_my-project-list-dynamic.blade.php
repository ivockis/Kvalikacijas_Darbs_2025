<div x-data="{ confirmingDelete: null, deleteFormId: null }" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @if ($projects->isEmpty())
        <p class="text-gray-600">{{ __('No projects created yet that match your criteria.') }}</p>
    @else
        @foreach ($projects as $project)
            <div class="bg-gray-700 dark:bg-gray-700 rounded-lg shadow-md dark:shadow-lg flex flex-col justify-between transform transition duration-300 hover:scale-105">
                <a href="{{ route('projects.show', $project) }}">
                    <img src="{{ $project->cover_image_url }}" alt="{{ $project->title }} Cover Image" class="w-full h-48 object-cover rounded-t-lg">
                </a>
                <div class="p-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2 whitespace-nowrap overflow-hidden text-ellipsis">
                            <a href="{{ route('projects.show', $project) }}" class="hover:underline">{{ $project->title }}</a>
                        </h3>
                        <p class="text-gray-600 dark:text-gray-400 text-sm mb-4 overflow-hidden text-ellipsis line-clamp-2">{{ Str::limit($project->description, 100) }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-300">{{ __('Created:') }} {{ $project->created_at->format('d.m.Y') }}</p>
                        <p class="text-xs text-gray-400 dark:text-gray-300">{{ __('Public:') }} {{ $project->is_public ? __('Yes') : __('No') }}</p>
                    </div>
                    <div class="mt-4 flex justify-end space-x-2">
                        <a href="{{ route('projects.show', $project) }}" class="inline-flex items-center px-3 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('View') }}
                        </a>
                        <a href="{{ route('projects.edit', $project) }}" class="inline-flex items-center px-3 py-2 bg-indigo-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-600 focus:bg-indigo-600 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Edit') }}
                        </a>
                        <form id="delete-form-{{ $project->id }}" action="{{ route('projects.destroy', $project) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="button" @click="confirmingDelete = {{ $project->id }}; deleteFormId = 'delete-form-{{ $project->id }}';" class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Delete') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    @endif

    <div class="mt-8">
        {{ $projects->links() }}
    </div>

    <!-- Confirmation Modal -->
    <div x-show="confirmingDelete !== null" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75" style="display: none;">
        <div @click.away="confirmingDelete = null; deleteFormId = null" class="bg-gray-800 dark:bg-gray-800 rounded-lg p-6 shadow-xl w-1/3 mx-auto">
            <h3 class="text-lg font-semibold dark:text-gray-200 mb-4">{{ __('Confirm Deletion') }}</h3>
            <p class="mb-4 dark:text-gray-400">{{ __('Are you sure you want to delete this project? This action cannot be undone.') }}</p>
            <div class="flex justify-end space-x-4">
                <button type="button" @click="confirmingDelete = null; deleteFormId = null" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-white rounded-md">{{ __('Cancel') }}</button>
                <button type="button" @click="document.getElementById(deleteFormId).submit()" class="px-4 py-2 bg-red-600 text-white rounded-md">{{ __('Delete') }}</button>
            </div>
        </div>
    </div>
</div>