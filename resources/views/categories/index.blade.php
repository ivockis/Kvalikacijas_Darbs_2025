<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Manage Categories') }}
        </h2>
    </x-slot>

    <div class="bg-white dark:bg-white">
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900" 
                        x-data="{
                            categories: {{ $categories->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->name, 'projects_count' => $cat->projects_count, 'editing' => false, 'originalName' => $cat->name, 'translatedName' => __($cat->name)]) }},
                            newCategoryName: '',
                            errors: {},
                            notification: { show: false, message: '', type: 'success' },
                            confirmingDelete: null,

                            showNotification(message, type = 'success') {
                                this.notification.message = message;
                                this.notification.type = type;
                                this.notification.show = true;
                                setTimeout(() => this.notification.show = false, 3000);
                            },

                            async addCategory() {
                                this.errors = {};
                                try {
                                    const response = await fetch('{{ route('categories.store') }}', {
                                        method: 'POST',
                                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                        body: JSON.stringify({ name: this.newCategoryName })
                                    });
                                    if (!response.ok) {
                                        const data = await response.json();
                                        this.errors = data.errors || {};
                                        throw new Error(data.message || '{{ __("Failed to add category.") }}');
                                    }
                                    const created = await response.json();
                                    created.editing = false;
                                    created.originalName = created.name;
                                    created.translatedName = created.name; // For new categories, name is not a key
                                    this.categories.unshift(created);
                                    this.newCategoryName = '';
                                    this.showNotification('{{ __("Category added successfully.") }}');
                                } catch (error) {
                                    this.showNotification(error.message, 'error');
                                }
                            },
                            async updateCategory(category) {
                                this.errors = {};
                                try {
                                    const response = await fetch(`/categories/${category.id}`, {
                                        method: 'PATCH',
                                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                        body: JSON.stringify({ name: category.name })
                                    });
                                    if (!response.ok) {
                                        const data = await response.json();
                                        this.errors = data.errors || {};
                                        category.name = category.originalName; // Revert on failure
                                        throw new Error(data.message || '{{ __("Failed to update category.") }}');
                                    }
                                    const updated = await response.json();
                                    category.originalName = updated.name;
                                    category.name = updated.name;
                                    category.translatedName = updated.name; // For updated categories, name is not a key
                                    category.editing = false;
                                    this.showNotification('{{ __("Category updated successfully.") }}');
                                } catch (error) {
                                    this.showNotification(error.message, 'error');
                                }
                            },
                            async confirmDelete() {
                                try {
                                    const response = await fetch(`/categories/${this.confirmingDelete}`, {
                                        method: 'DELETE',
                                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                                    });
                                    if (!response.ok) {
                                        const data = await response.json();
                                        throw new Error(data.message || '{{ __("Failed to delete category.") }}');
                                    }
                                    this.categories = this.categories.filter(c => c.id !== this.confirmingDelete);
                                    this.showNotification('{{ __("Category deleted successfully.") }}');
                                } catch (error) {
                                    this.showNotification(error.message, 'error');
                                } finally {
                                    this.confirmingDelete = null;
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
                                <p class="mb-4">{{ __('Confirm delete category.') }}</p>
                                <div class="flex justify-end space-x-4">
                                    <button @click="confirmingDelete = null" class="px-4 py-2 bg-gray-300 rounded-md">{{ __('Cancel') }}</button>
                                    <button @click="confirmDelete()" class="px-4 py-2 bg-red-600 text-white rounded-md">{{ __('Delete') }}</button>
                                </div>
                            </div>
                        </div>

                        <!-- Add Form -->
                        <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                            <h3 class="font-bold text-lg mb-2">{{ __('Add New Category') }}</h3>
                            <div>
                                <input type="text" x-model="newCategoryName" placeholder="{{ __('Category Name') }}" class="w-full rounded-md shadow-sm border-gray-300" maxlength="50">
                            </div>
                            <div class="mt-4 flex justify-end">
                                <button @click="addCategory" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">{{ __('Add Category') }}</button>
                            </div>
                        </div>

                        <!-- Categories Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Projects') }}</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template x-for="category in categories" :key="category.id">
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div x-show="!category.editing" x-text="category.translatedName"></div>
                                                <input x-show="category.editing" type="text" x-model="category.name" class="w-full rounded-md shadow-sm border-gray-300 text-sm p-1" maxlength="50">
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="category.projects_count"></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                <button x-show="!category.editing" @click="category.editing = true" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</button>
                                                <button x-show="category.editing" @click="updateCategory(category)" class="text-green-600 hover:text-green-900">{{ __('Save') }}</button>
                                                <button x-show="category.editing" @click="category.editing = false; category.name = category.originalName" class="text-gray-500 hover:text-gray-900">{{ __('Cancel') }}</button>
                                                <button @click="confirmingDelete = category.id" :disabled="category.projects_count > 0" :class="{ 'text-gray-400 cursor-not-allowed': category.projects_count > 0, 'text-red-600 hover:text-red-900': category.projects_count === 0 }">{{ __('Delete') }}</button>
                                            </td>
                                        </tr>
                                    </template>
                                    <template x-if="categories.length === 0">
                                        <tr><td colspan="3" class="text-center py-4 text-gray-500">{{ __('No categories found.') }}</td></tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
