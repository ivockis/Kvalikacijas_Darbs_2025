<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Categories') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" 
                     x-data="{
                        categories: {{ $categories->map(fn($cat) => [...$cat->toArray(), 'editing' => false, 'originalName' => $cat->name]) }},
                        newCategoryName: '',
                        errors: {},
                        async addCategory() {
                            this.errors = {};
                            const response = await fetch('{{ route('categories.store') }}', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ name: this.newCategoryName })
                            });
                            if (!response.ok) {
                                const data = await response.json();
                                this.errors = data.errors || {};
                                alert(data.message || 'Failed to add category.');
                                return;
                            }
                            const created = await response.json();
                            created.editing = false;
                            created.originalName = created.name;
                            this.categories.unshift(created);
                            this.newCategoryName = '';
                            alert('Category added successfully.');
                        },
                        async updateCategory(category) {
                            this.errors = {};
                            const response = await fetch(`/categories/${category.id}`, {
                                method: 'PATCH',
                                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                body: JSON.stringify({ name: category.name })
                            });
                            if (!response.ok) {
                                const data = await response.json();
                                this.errors = data.errors || {};
                                alert(data.message || 'Failed to update category.');
                                category.name = category.originalName; // Revert on failure
                                return;
                            }
                            const updated = await response.json();
                            category.originalName = updated.name;
                            category.editing = false;
                            alert('Category updated successfully.');
                        },
                        async deleteCategory(categoryId) {
                            if (!confirm('Are you sure? This is irreversible.')) return;
                            const response = await fetch(`/categories/${categoryId}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                            });
                            if (!response.ok) {
                                const data = await response.json();
                                alert(data.message || 'Failed to delete category.');
                                return;
                            }
                            this.categories = this.categories.filter(c => c.id !== categoryId);
                            alert('Category deleted successfully.');
                        }
                     }"
                >
                    <!-- Add Form -->
                    <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                        <h3 class="font-bold text-lg mb-2">{{ __('Add New Category') }}</h3>
                        <div>
                            <input type="text" x-model="newCategoryName" placeholder="Category Name" class="w-full rounded-md shadow-sm border-gray-300">
                            <template x-if="errors.name"><p class="text-sm text-red-600 mt-1" x-text="errors.name[0]"></p></template>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button @click="addCategory" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">Add Category</button>
                        </div>
                    </div>

                    <!-- Categories Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Projects</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="category in categories" :key="category.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div x-show="!category.editing" x-text="category.name"></div>
                                            <input x-show="category.editing" type="text" x-model="category.name" class="w-full rounded-md shadow-sm border-gray-300 text-sm p-1">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="category.projects_count"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                            <button x-show="!category.editing" @click="category.editing = true" class="text-indigo-600 hover:text-indigo-900">Edit</button>
                                            <button x-show="category.editing" @click="updateCategory(category)" class="text-green-600 hover:text-green-900">Save</button>
                                            <button x-show="category.editing" @click="category.editing = false; category.name = category.originalName" class="text-gray-500 hover:text-gray-900">Cancel</button>
                                            <button @click="deleteCategory(category.id)" :disabled="category.projects_count > 0" :class="{ 'text-gray-400 cursor-not-allowed': category.projects_count > 0, 'text-red-600 hover:text-red-900': category.projects_count === 0 }">Delete</button>
                                        </td>
                                    </tr>
                                </template>
                                <template x-if="categories.length === 0">
                                    <tr><td colspan="3" class="text-center py-4 text-gray-500">No categories found.</td></tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
