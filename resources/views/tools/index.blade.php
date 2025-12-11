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

                        // Function to handle inline editing
                        editTool(tool) {
                            tool.editing = true;
                        },

                        // Function to save an updated tool
                        async updateTool(tool) {
                            if (!tool.name) {
                                alert('Tool name cannot be empty.');
                                return;
                            }
                            
                            try {
                                const response = await fetch(`/tools/${tool.id}`, {
                                    method: 'PATCH',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                    },
                                    body: JSON.stringify({ name: tool.name, comment: tool.comment })
                                });

                                if (!response.ok) {
                                    const errorData = await response.json();
                                    const errorMessage = errorData.message || 'Failed to update tool.';
                                    throw new Error(errorMessage);
                                }

                                const updatedTool = await response.json();
                                tool.editing = false;
                                tool.name = updatedTool.name;
                                tool.comment = updatedTool.comment;
                                alert('Tool updated successfully.');
                            } catch (error) {
                                alert(`Error: ${error.message}`);
                            }
                        },

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
                                    body: JSON.stringify(this.newTool)
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
                        }
                     }"
                >
                    <!-- Add new tool form -->
                    <div class="mb-6 p-4 border rounded-lg">
                        <h3 class="font-bold text-lg mb-2">Add New Tool</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <input type="text" x-model="newTool.name" placeholder="Tool Name" class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" x-model="newTool.comment" placeholder="Optional Comment" class="w-full md:col-span-2 rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button @click="addTool" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Add</button>
                        </div>
                    </div>

                    <!-- Tools List -->
                    <div class="space-y-2">
                        <template x-for="tool in tools" :key="tool.id">
                            <div class="p-4 border rounded-lg flex items-center space-x-4">
                                <!-- View/Edit Fields -->
                                <div class="flex-grow grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div x-show="!tool.editing" @click="editTool(tool)" class="cursor-pointer font-medium text-gray-900" x-text="tool.name"></div>
                                    <input x-show="tool.editing" type="text" x-model="tool.name" class="w-full rounded-md shadow-sm border-gray-300">
                                    
                                    <div x-show="!tool.editing" @click="editTool(tool)" class="cursor-pointer text-gray-600 md:col-span-2" x-text="tool.comment || 'No comment'"></div>
                                    <input x-show="tool.editing" type="text" x-model="tool.comment" class="w-full md:col-span-2 rounded-md shadow-sm border-gray-300">
                                </div>
                                <!-- Actions -->
                                <div class="flex items-center space-x-2">
                                    <button x-show="tool.editing" @click="updateTool(tool)" class="text-green-600 hover:text-green-900">Save</button>
                                    <button x-show="tool.editing" @click="tool.editing = false" class="text-gray-600 hover:text-gray-900">Cancel</button>
                                    
                                    <button 
                                        @click="deleteTool(tool.id)" 
                                        :disabled="tool.projects_count > 0"
                                        :class="{ 'text-red-600 hover:text-red-900': tool.projects_count == 0, 'text-gray-400 cursor-not-allowed': tool.projects_count > 0 }"
                                        :title="tool.projects_count > 0 ? 'Cannot delete: tool is in use' : 'Delete tool'"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
