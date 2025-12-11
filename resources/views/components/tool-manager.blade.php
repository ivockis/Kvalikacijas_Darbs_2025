@props(['initialTools' => []])

@php
    $initialToolsData = collect($initialTools)->map(function($tool) {
        return [
            'id' => $tool->id, 
            'name' => $tool->name, 
            'comment' => $tool->comment, 
            'existing' => true,
            'searchTerm' => $tool->name, // Initialize searchTerm
            'suggestions' => [],
        ];
    });
@endphp

<div x-data="{ 
    selectedTools: {{ $initialToolsData->toJson() }},

    addTool() {
        this.selectedTools.push({ id: null, name: '', comment: '', existing: false, searchTerm: '', suggestions: [] });
    },

    removeTool(index) {
        this.selectedTools.splice(index, 1);
    },

    async searchTools(tool) {
        if (tool.searchTerm.length < 2) {
            tool.suggestions = [];
            return;
        }
        try {
            const response = await fetch(`/tools/search?query=${tool.searchTerm}`);
            if (!response.ok) throw new Error('Search failed');
            tool.suggestions = await response.json();
        } catch (error) {
            console.error('Error searching for tools:', error);
            tool.suggestions = [];
        }
    },

    selectSuggestion(tool, suggestion) {
        tool.id = suggestion.id;
        tool.name = suggestion.name;
        tool.comment = suggestion.comment;
        tool.existing = true;
        tool.suggestions = [];
        tool.searchTerm = suggestion.name; // Update search term to full name
    }
}" x-init="if (selectedTools.length === 0) addTool()">

    <x-input-label :value="__('Tools')" class="mb-2 font-semibold" />

    <template x-for="(tool, index) in selectedTools" :key="index">
        <div class="flex items-start space-x-2 mb-2 p-2 border rounded-md bg-gray-50">
            <div class="flex-grow">
                <!-- Tool Name Input -->
                <div class="relative">
                    <input type="text" 
                           x-model="tool.searchTerm" 
                           @input.debounce.300ms="searchTools(tool)"
                           @focus="searchTools(tool)"
                           placeholder="{{ __('Tool Name') }}" 
                           class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    
                    <!-- Suggestions Dropdown -->
                    <div x-show="tool.suggestions.length > 0" 
                         @click.away="tool.suggestions = []" 
                         class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto">
                        <template x-for="suggestion in tool.suggestions" :key="suggestion.id">
                            <div @click="selectSuggestion(tool, suggestion)" 
                                 x-text="suggestion.name" 
                                 class="p-2 cursor-pointer hover:bg-gray-100 text-sm"></div>
                        </template>
                    </div>
                </div>

                <!-- Tool Comment Input -->
                <div class="mt-2">
                    <input type="text" 
                           x-model="tool.comment" 
                           placeholder="{{ __('Comment (Optional)') }}" 
                           class="w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                </div>
            </div>

            <!-- Hidden Inputs for Form Submission -->
            <input type="hidden" :name="`tools_data[${index}][id]`" x-model="tool.id">
            <input type="hidden" :name="`tools_data[${index}][name]`" x-model="tool.name">
            <input type="hidden" :name="`tools_data[${index}][comment]`" x-model="tool.comment">

            <!-- Remove Button -->
            <button type="button" @click="removeTool(index)" class="text-red-600 hover:text-red-900 pt-1" title="{{ __('Remove Tool') }}">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
            </button>
        </div>
    </template>

    <!-- Add Tool Button -->
    <button type="button" @click="addTool()" class="mt-2 inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
        {{ __('+ Add Tool') }}
    </button>
</div>
