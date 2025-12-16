<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Complaints for Project: ') }} <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:underline">{{ $project->title }}</a>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('admin.projects.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-300">
                    &laquo; {{ __('Back to Project List') }}
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Pending Complaints -->
                    <h3 class="text-xl font-semibold mb-4 text-yellow-600">Pending Complaints</h3>
                    <div class="space-y-4">
                        @forelse($pendingComplaints as $complaint)
                            <div class="border rounded-lg p-4 bg-yellow-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-800">
                                            Reported by: <span class="text-blue-700">{{ $complaint->user->username }}</span>
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $complaint->created_at->format('d.m.Y H:i') }}
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        <form method="POST" action="{{ route('admin.complaints.approve', $complaint) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3 py-1 text-xs font-medium text-white bg-green-600 hover:bg-green-700 rounded-md">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.complaints.decline', $complaint) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3 py-1 text-xs font-medium text-white bg-red-600 hover:bg-red-700 rounded-md">Decline</button>
                                        </form>
                                    </div>
                                </div>
                                <p class="mt-2 text-gray-700">
                                    <strong>Reason:</strong> {{ $complaint->reason }}
                                </p>
                            </div>
                        @empty
                            <p>{{ __('No pending complaints for this project.') }}</p>
                        @endforelse
                    </div>

                    <!-- Resolved Complaints -->
                    <h3 class="text-xl font-semibold mt-8 mb-4 border-t pt-6 text-gray-500">Resolved Complaints</h3>
                    <div class="space-y-4">
                        @forelse($resolvedComplaints as $complaint)
                            <div class="border rounded-lg p-4 bg-gray-50">
                                <div class="flex justify-between items-center">
                                    <p class="font-semibold text-gray-700">
                                        Reported by: <span class="text-blue-700">{{ $complaint->user->username }}</span>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        {{ $complaint->created_at->format('d.m.Y H:i') }}
                                    </p>
                                </div>
                                <p class="mt-2 text-gray-600">
                                    <strong>Reason:</strong> {{ $complaint->reason }}
                                </p>
                                <div class="mt-2">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($complaint->status === 'approved') bg-green-100 text-green-800 @endif
                                        @if($complaint->status === 'declined') bg-red-100 text-red-800 @endif
                                    ">
                                        {{ ucfirst($complaint->status) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p>{{ __('No resolved complaints for this project.') }}</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
