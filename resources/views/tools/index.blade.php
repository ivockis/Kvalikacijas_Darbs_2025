<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Tools') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" 
                     x-data="{
                        tools: {{ $tools->toJson() }},
                        newTool: { name: '', comment: '' },
                        searchResults: [],
                        searchOpen: false,
                        notification: { show: false, message: '', type: 'success' },
                        confirmingDelete: null,

                        init() {
                            this.$watch('newTool.name', (value) => {
                                if (value.length > 2) { this.searchTools(); } 
                                else { this.searchResults = []; this.searchOpen = false; }
                            });
                        },

                        showNotification(message, type = 'success') {
                            this.notification.message = message;
                            this.notification.type = type;
                            this.notification.show = true;
                            setTimeout(() => this.notification.show = false, 3000);
                        },

                        async searchTools() {
                            if (!this.newTool.name.trim()) return;
                            const response = await fetch(`{{ route('tools.search') }}?query=${this.newTool.name}`);
                            this.searchResults = await response.json();
                            this.searchOpen = this.searchResults.length > 0;
                        },

                        selectTool(tool) {
                            this.newTool.name = tool.name;
                            this.newTool.comment = tool.comment || '';
                            this.searchOpen = false;
                        },

                        async addTool() {
                            if (!this.newTool.name.trim()) {
                                this.showNotification('{{ __("Tool name cannot be empty.") }}', 'error');
                                return;
                            }
                            if (this.tools.some(t => t.name.toLowerCase() === this.newTool.name.trim().toLowerCase())) {
                                this.showNotification('{{ __("A tool with this name already exists.") }}', 'error');
                                return;
                            }
                            try {
                                const response = await fetch('{{ route('tools.store') }}', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    body: JSON.stringify({ name: this.newTool.name.trim(), comment: this.newTool.comment.trim(), approved: false })
                                });
                                if (!response.ok) {
                                    const data = await response.json();
                                    throw new Error(data.errors?.name?.[0] || '{{ __("Failed to add tool.") }}');
                                }
                                const createdTool = await response.json();
                                this.tools.unshift(createdTool);
                                this.newTool = { name: '', comment: '' };
                                this.searchOpen = false;
                                this.showNotification('{{ __("Tool added successfully.") }}');
                            } catch (error) {
                                this.showNotification(error.message, 'error');
                            }
                        },
                        
                        async confirmDelete() {
                            try {
                                const response = await fetch(`/tools/${this.confirmingDelete}`, {
                                    method: 'DELETE',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                });
                                if (!response.ok) {
                                    const data = await response.json();
                                    throw new Error(data.message || '{{ __("Failed to delete tool.") }}');
                                }
                                this.tools = this.tools.filter(t => t.id !== this.confirmingDelete);
                                this.showNotification('{{ __("Tool deleted successfully.") }}');
                            } catch (error) {
                                this.showNotification(error.message, 'error');
                            } finally {
                                this.confirmingDelete = null;
                            }
                        },

                        async toggleApproval(tool) {
                             try {
                                const response = await fetch(`/tools/${tool.id}/toggle-approval`, {
                                    method: 'PATCH',
                                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                });
                                if (!response.ok) { throw new Error('{{ __("Failed to update approval status.") }}'); }
                                const updatedTool = await response.json();
                                tool.approved = updatedTool.approved;
                                this.showNotification('{{ __("Approval status updated.") }}');
                            } catch (error) {
                                this.showNotification(error.message, 'error');
                            }
                        }
                     }"
                >
                    <!-- Notification -->
                    <div x-show="notification.show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 transform translate-y-0" x-transition:leave-end="opacity-0 transform translate-y-2" class="fixed bottom-4 right-4 p-4 rounded-lg shadow-lg text-white" :class="notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'" style="display: none;">
                        <p x-text="notification.message"></p>
                    </div>

                    <!-- Confirmation Modal -->
                    <div x-show="confirmingDelete !== null" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-500 bg-opacity-75">
                        <div @click.away="confirmingDelete = null" class="bg-white rounded-lg p-6 shadow-xl">
                            <p class="mb-4">{{ __('Confirm delete tool.') }}</p>
                            <div class="flex justify-end space-x-4">
                                <button @click="confirmingDelete = null" class="px-4 py-2 bg-gray-300 rounded-md">{{ __('Cancel') }}</button>
                                <button @click="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-md">{{ __('Delete') }}</button>
                            </div>
                        </div>
                    </div>

                    <!-- Add new tool form -->
                    <div class="mb-6 p-4 border rounded-lg">
                        <h3 class="font-bold text-lg mb-2">{{ __('Add New Tool') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="relative" @click.away="searchOpen = false">
                                <input type="text" x-model.debounce.300ms="newTool.name" @focus="searchOpen = true" placeholder="{{ __('Search or Create a Tool Name') }}" class="w-full rounded-md shadow-sm border-gray-300" maxlength="50">
                                <div x-show="searchOpen && searchResults.length > 0" class="absolute z-10 w-full mt-1 bg-white border rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    <template x-for="result in searchResults" :key="result.id">
                                        <div @click="selectTool(result)" class="cursor-pointer px-4 py-2 hover:bg-gray-100">
                                            <p x-text="result.name"></p>
                                            <p class="text-xs text-gray-500" x-text="result.comment"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                            <input type="text" x-model="newTool.comment" placeholder="{{ __('Optional Comment') }}" class="w-full md:col-span-2 rounded-md shadow-sm border-gray-300" maxlength="50">
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button @click="addTool" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">{{ __('Add') }}</button>
                        </div>
                    </div>

                    <!-- Tools Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Tool') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Comment') }}</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Projects') }}</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Approved') }}</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="tool in tools" :key="tool.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div x-text="tool.name" class="font-medium text-gray-900"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div x-text="tool.comment || '-'" class="text-gray-600"></div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500" x-text="tool.projects_count"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <button @click="toggleApproval(tool)" class="relative inline-flex items-center h-6 rounded-full w-11" :class="tool.approved ? 'bg-green-500' : 'bg-gray-300'">
                                                <span class="sr-only">{{ __('Toggle Approval') }}</span>
                                                <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform" :class="{ 'translate-x-6': tool.approved, 'translate-x-1': !tool.approved }"></span>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                              <button @click="confirmingDelete = tool.id" :disabled="tool.projects_count > 0" :class="{ 'text-gray-400 cursor-not-allowed': tool.projects_count > 0, 'text-red-600 hover:text-red-900': tool.projects_count === 0 }" :title="tool.projects_count > 0 ? 'Cannot delete: tool is in use' : 'Delete tool'">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
