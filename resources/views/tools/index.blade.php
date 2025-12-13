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

                        // Function to add a new tool
                        async addTool() {
                            if (!this.newTool.name) {
                                alert('New tool name cannot be empty.');
                                return;
                            }

                            try {
                                const response = await fetch('/tools', {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({ name: this.newTool.name, comment: this.newTool.comment, approved: false }) // New tools are unapproved by default
                                });

                                if (!response.ok) {
                                    const errorData = await response.json();
                                    const errorMessage = errorData.message || 'Failed to add tool.';
                                    throw new Error(errorMessage);
                                }

                                const createdTool = await response.json();
                                this.tools.unshift(createdTool); // Add to the top of the list
                                this.newTool = { name: '', comment: '' }; // Clear input fields
                                alert('Tool added successfully.');
                            } catch (error) {
                                alert(`Error: ${error.message}`);
                            }
                        },
                        
                        // Function to delete a tool
                        async deleteTool(toolId) {
                            if (!confirm('Are you sure you want to delete this tool?')) {
                                return;
                            }
                            
                            try {
                                const response = await fetch(`/tools/${toolId}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    },
                                });

                                if (!response.ok) {
                                    const errorData = await response.json();
                                    const errorMessage = errorData.message || 'Failed to delete tool.';
                                    throw new Error(errorMessage);
                                }
                                
                                this.tools = this.tools.filter(t => t.id !== toolId);
                                alert('Tool deleted successfully.');
                            } catch (error) {
                                alert(`Error: ${error.message}`);
                            }
                        },

                        // Function to toggle approval status
                        async toggleApproval(tool) {
                             try {
                                const response = await fetch(`/tools/${tool.id}/toggle-approval`, {
                                    method: 'PATCH',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    },
                                });

                                if (!response.ok) {
                                    throw new Error('Failed to update approval status.');
                                }

                                const updatedTool = await response.json();
                                tool.approved = updatedTool.approved;
                                alert('Approval status updated.');
                            } catch (error) {
                                alert(`Error: ${error.message}`);
                            }
                        }
                     }"
                >
                    <!-- Add new tool form -->
                    <div class="mb-6 p-4 border rounded-lg">
                        <h3 class="font-bold text-lg mb-2">Add New Tool</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <input type="text" x-model="newTool.name" placeholder="Tool Name" class="w-full rounded-md shadow-sm border-gray-300">
                            <input type="text" x-model="newTool.comment" placeholder="Optional Comment" class="w-full md:col-span-2 rounded-md shadow-sm border-gray-300">
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button @click="addTool" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Add</button>
                        </div>
                    </div>

                    <!-- Tools Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tool</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Comment</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Projects</th>
                                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Approved</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
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
                                                <span class="sr-only">Toggle Approval</span>
                                                <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform" :class="{ 'translate-x-6': tool.approved, 'translate-x-1': !tool.approved }"></span>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button @click="deleteTool(tool.id)" :disabled="tool.projects_count > 0" :class="{ 'text-gray-400 cursor-not-allowed': tool.projects_count > 0, 'text-red-600 hover:text-red-900': tool.projects_count === 0 }" :title="tool.projects_count > 0 ? 'Cannot delete: tool is in use' : 'Delete tool'">
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
