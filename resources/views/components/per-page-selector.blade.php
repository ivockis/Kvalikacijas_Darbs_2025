<select name="per_page" id="per_page"
    class="block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
    onchange="this.form.submit()">
    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10{{ __(' per page') }}</option>
    <option value="25" {{ !request('per_page') || request('per_page') == 25 ? 'selected' : '' }}>25{{ __(' per page') }}</option>
    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50{{ __(' per page') }}</option>
    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>{{ __('All') }}</option>
</select>