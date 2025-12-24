<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Project Management') }}
        </h2>
    </x-slot>

    <div class="bg-white dark:bg-white">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg" x-data="{
                        projects: {{ json_encode($projects->items()) }},
                        links: {{ json_encode($projects->linkCollection()->toArray()) }},
                        search: '',
                        status: 'pending_complaints',
                        sort_by: 'created_at',
                        sort_order: 'desc',
                        per_page: 10,
                        loading: false,
                        confirmingBlock: null,
                        confirmingProject: null,

                        init() {
                            let params = new URLSearchParams(window.location.search);
                            this.search = params.get('search') || '';
                            this.status = params.get('status') || this.status;
                            this.sort_by = params.get('sort_by') || 'created_at';
                            this.sort_order = params.get('sort_order') || 'desc';
                            this.per_page = params.get('per_page') || 10;
                            
                            this.$watch('search', () => this.fetchProjects());
                            this.$watch('status', () => this.fetchProjects());
                            this.$watch('sort_by', () => this.fetchProjects());
                            this.$watch('sort_order', () => this.fetchProjects());
                            this.$watch('per_page', () => this.fetchProjects());
                        },

                        fetchProjects(page = 1) {
                            this.loading = true;
                            const params = new URLSearchParams({
                                search: this.search,
                                status: this.status,
                                sort_by: this.sort_by,
                                sort_order: this.sort_order,
                                per_page: this.per_page,
                                page: page,
                            });
                            
                            history.pushState(null, '', `?${params.toString()}`);

                            fetch(`{{ route('admin.projects.index') }}?${params.toString()}`, {
                                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                            })
                            .then(response => response.json())
                            .then(data => {
                                this.projects = data.data;
                                this.links = data.links;
                            })
                            .finally(() => this.loading = false);
                        },

                        askForConfirmation(project) {
                            this.confirmingProject = project;
                        },

                        cancelConfirmation() {
                            this.confirmingProject = null;
                        },

                        toggleProjectBlock(project) {
                            fetch(`/admin/projects/${project.id}/toggle-block`, {
                                method: 'PATCH',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                            }).then(res => res.json()).then(data => {
                                project.is_blocked = data.is_blocked;
                                this.cancelConfirmation();
                            });
                        },
                        confirmBlockUnblockProjectMessage: '{{ __("Confirm block/unblock project") }}'
                    }">
                        <!-- Filters -->
                        <div class="p-6">
                            <div class="mb-4 flex flex-wrap items-end gap-4">
                                <div class="flex-grow">
                                    <label for="search" class="sr-only">Search</label>
                                    <input type="text" x-model.debounce.500ms="search" placeholder="{{ __('Search by title or description...') }}" class="w-full rounded-md shadow-sm border-gray-300">
                                </div>
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                                    <select x-model="status" id="status" class="rounded-md shadow-sm border-gray-300">
                                        <option value="">{{ __('All') }}</option>
                                        <option value="blocked">{{ __('Blocked') }}</option>
                                        <option value="active">{{ __('Active') }}</option>
                                        <option value="pending_complaints">{{ __('Pending Complaints') }}</option>
                                        <option value="resolved_complaints">{{ __('Resolved Complaints') }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="per_page" class="block text-sm font-medium text-gray-700">{{ __('Per Page') }}</label>
                                    <select x-model="per_page" id="per_page" class="rounded-md shadow-sm border-gray-300">
                                        <option value="10">10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Project Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Project') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Author') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                                        <th @click="sort_by = 'total_complaints_count'; sort_order = sort_order === 'asc' ? 'desc' : 'asc'" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase cursor-pointer">{{ __('Complaints') }}</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Is Blocked') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="project in projects" :key="project.id">
                                        <tr>
                                            <td class="px-6 py-4">
                                                <a :href="'/projects/' + project.id" class="flex items-center group"> 
                                                    <img :src="project.cover_image_url" alt="" class="h-20 w-32 object-cover rounded-lg">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900 group-hover:underline" x-text="project.title"></div> 
                                                        <div class="text-sm text-gray-500" x-text="project.description.substring(0, 50) + '...'"></div>
                                                    </div>
                                                </a>
                                            </td>
                                            <td class="px-6 py-4">
                                                <a :href="'/users/' + project.user.id" class="text-sm text-blue-600 hover:underline" x-text="project.user.username"></a>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span x-show="project.is_blocked" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ __('Blocked') }}</span>
                                                <span x-show="!project.is_blocked" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ __('Active') }}</span>
                                            </td>
                                            <td class="px-6 py-4">
                                                <template x-if="project.total_complaints_count > 0">
                                                    <a :href="`/admin/projects/${project.id}/complaints`" 
                                                    :class="{ 'text-green-600': project.pending_complaints_count === 0, 'text-red-600': project.pending_complaints_count > 0 }"
                                                    class="hover:underline" 
                                                    x-text="project.total_complaints_count"></a>
                                                </template>
                                                <template x-if="project.total_complaints_count === 0">
                                                    <span x-text="project.total_complaints_count"></span>
                                                </template>
                                            </td>
                                            <td class="px-6 py-4 text-center relative">
                                                <button @click="askForConfirmation(project)" :class="{ 'bg-red-500': project.is_blocked, 'bg-gray-300': !project.is_blocked }" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors">
                                                    <span :class="{ 'translate-x-6': project.is_blocked, 'translate-x-1': !project.is_blocked }" class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"></span>
                                                </button>
                                                <!-- Confirmation Dialog -->
                                                <div x-show="confirmingProject && confirmingProject.id === project.id" 
                                                    class="absolute z-10 right-0 mt-2 w-64 bg-white border border-gray-300 rounded-lg shadow-lg p-4"
                                                    @click.outside="cancelConfirmation()">
                                                    <p class="text-sm text-gray-700" x-text="confirmBlockUnblockProjectMessage.replace(':title', confirmingProject.title)"></p>
                                                    <div class="mt-4 flex justify-end space-x-2">
                                                        <button @click="cancelConfirmation()" class="px-3 py-1 text-sm text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-md">{{ __('Cancel') }}</button>
                                                        <button @click="toggleProjectBlock(project)" class="px-3 py-1 text-sm text-white bg-red-600 hover:bg-red-700 rounded-md">{{ __('Yes') }}</button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <div class="p-6">
                            <nav class="flex items-center justify-between" role="navigation">
                                <div class="flex-1 flex justify-between sm:hidden">
                                    <!-- Mobile pagination buttons -->
                                </div>
                                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                    <div>
                                    <div>
                                        <span class="relative z-0 inline-flex shadow-sm rounded-md">
                                            <template x-for="(link, index) in links" :key="index">
                                                <button @click.prevent="fetchProjects(new URL(link.url).searchParams.get('page'))"
                                                        :disabled="!link.url"
                                                        :class="{'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active, 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active}"
                                                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                                        x-html="link.label">
                                                </button>
                                            </template>
                                        </span>
                                    </div>
                                </div>
                            </nav>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</x-app-layout>
