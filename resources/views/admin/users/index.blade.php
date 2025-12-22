<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('User Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{
                    users: {{ json_encode($users->items()) }},
                    links: {{ json_encode($users->linkCollection()->toArray()) }},
                    search: '',
                    status: '',
                    sort_by: 'created_at',
                    sort_order: 'desc',
                    per_page: 10,
                    loading: false,
                    confirmingAction: null, // 'admin' or 'block'
                    confirmingUser: null,

                    init() {
                        let params = new URLSearchParams(window.location.search);
                        this.search = params.get('search') || '';
                        this.status = params.get('status') || '';
                        this.sort_by = params.get('sort_by') || 'created_at';
                        this.sort_order = params.get('sort_order') || 'desc';
                        this.per_page = params.get('per_page') || 10;
                        
                        this.$watch('search', () => this.fetchUsers());
                        this.$watch('status', () => this.fetchUsers());
                        this.$watch('sort_by', () => this.fetchUsers());
                        this.$watch('sort_order', () => this.fetchUsers());
                        this.$watch('per_page', () => this.fetchUsers());
                    },

                    fetchUsers(page = 1) {
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

                        fetch(`{{ route('admin.users.index') }}?${params.toString()}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        })
                        .then(response => response.json())
                        .then(data => {
                            this.users = data.data;
                            this.links = data.links;
                        })
                        .finally(() => this.loading = false);
                    },

                    askForConfirmation(user, action) {
                        this.confirmingUser = user;
                        this.confirmingAction = action;
                    },

                    cancelConfirmation() {
                        this.confirmingUser = null;
                        this.confirmingAction = null;
                    },

                    toggleAdmin(user) {
                        fetch(`/admin/users/${user.id}/toggle-admin`, {
                            method: 'PATCH',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        }).then(res => res.json()).then(data => {
                            user.is_admin = data.is_admin;
                            this.cancelConfirmation();
                        });
                    },

                    toggleBlock(user) {
                        fetch(`/admin/users/${user.id}/toggle-block`, {
                            method: 'PATCH',
                            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                        }).then(res => res.json()).then(data => {
                            user.is_blocked = data.is_blocked;
                            this.cancelConfirmation();
                        });
                    },
                    confirmChangeAdminStatusMessage: '{{ __("Confirm change admin status") }}',
                    confirmBlockUnblockUserMessage: '{{ __("Confirm block/unblock user") }}'
                }">
                    <!-- Filters -->
                    <div class="mb-4 flex flex-wrap items-end gap-4">
                        <div class="flex-grow">
                            <label for="search" class="sr-only">Search</label>
                            <input type="text" x-model.debounce.500ms="search" placeholder="{{ __('Search by username or email...') }}" class="w-full rounded-md shadow-sm border-gray-300">
                        </div>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                            <select x-model="status" id="status" class="rounded-md shadow-sm border-gray-300">
                                <option value="">{{ __('All') }}</option>
                                <option value="admin">{{ __('Admins') }}</option>
                                <option value="blocked">{{ __('Blocked') }}</option>
                                <option value="active">{{ __('Active') }}</option>
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

                    <!-- User Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('User') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-32">{{ __('Created At') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase w-32">{{ __('Updated At') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Is Admin') }}</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">{{ __('Is Blocked') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="user in users" :key="user.id">
                                    <tr>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <img :src="user.profile_image_url" alt="" class="h-10 w-10 rounded-full object-cover">
                                                <div class="ml-4">
                                                    <a :href="'/users/' + user.id" class="text-sm font-medium text-blue-600 hover:underline" x-text="`${user.name} ${user.surname}`"></a>
                                                    <div class="text-sm text-gray-500" x-text="user.username"></div>
                                                    <div class="text-sm text-gray-500" x-text="user.email"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 w-32" x-text="new Date(user.created_at).toLocaleString()"></td>
                                        <td class="px-6 py-4 text-sm text-gray-900 w-32" x-text="new Date(user.updated_at).toLocaleString()"></td>
                                        <td class="px-6 py-4">
                                            <span x-show="user.is_blocked" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">{{ __('Blocked') }}</span>
                                            <span x-show="!user.is_blocked" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">{{ __('Active') }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center relative">
                                            <button @click="askForConfirmation(user, 'admin')" :class="{ 'bg-green-500': user.is_admin, 'bg-gray-300': !user.is_admin }" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors">
                                                <span :class="{ 'translate-x-6': user.is_admin, 'translate-x-1': !user.is_admin }" class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"></span>
                                            </button>
                                            <!-- Confirmation Dialog -->
                                            <div x-show="confirmingUser && confirmingUser.id === user.id && confirmingAction === 'admin'" 
                                                 class="absolute z-10 right-0 mt-2 w-64 bg-white border border-gray-300 rounded-lg shadow-lg p-4"
                                                 @click.outside="cancelConfirmation()">
                                                <p class="text-sm text-gray-700" x-text="confirmChangeAdminStatusMessage.replace(':username', confirmingUser.username)"></p>
                                                <div class="mt-4 flex justify-end space-x-2">
                                                    <button @click="cancelConfirmation()" class="px-3 py-1 text-sm text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-md">{{ __('Cancel') }}</button>
                                                    <button @click="toggleAdmin(user)" class="px-3 py-1 text-sm text-white bg-green-600 hover:bg-green-700 rounded-md">{{ __('Yes') }}</button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center relative">
                                            <button @click="askForConfirmation(user, 'block')" :class="{ 'bg-red-500': user.is_blocked, 'bg-gray-300': !user.is_blocked }" class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors">
                                                <span :class="{ 'translate-x-6': user.is_blocked, 'translate-x-1': !user.is_blocked }" class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform"></span>
                                            </button>
                                            <!-- Confirmation Dialog -->
                                            <div x-show="confirmingUser && confirmingUser.id === user.id && confirmingAction === 'block'" 
                                                 class="absolute z-10 right-0 mt-2 w-64 bg-white border border-gray-300 rounded-lg shadow-lg p-4"
                                                 @click.outside="cancelConfirmation()">
                                                <p class="text-sm text-gray-700" x-text="confirmBlockUnblockUserMessage.replace(':username', confirmingUser.username)"></p>
                                                <div class="mt-4 flex justify-end space-x-2">
                                                    <button @click="cancelConfirmation()" class="px-3 py-1 text-sm text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-md">{{ __('Cancel') }}</button>
                                                    <button @click="toggleBlock(user)" class="px-3 py-1 text-sm text-white bg-red-600 hover:bg-red-700 rounded-md">{{ __('Yes') }}</button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
        </div>
    </div>
</x-app-layout>