document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('filter-form');
    if (!filterForm) {
        return;
    }

    const projectListContainer = document.getElementById('project-list-container');
    const searchInput = document.getElementById('search');
    const resetFiltersWrapper = document.getElementById('reset-filters-wrapper');

    let debounceTimeout;

    const checkFiltersAndToggleButton = () => {
        if (!resetFiltersWrapper) return;

        const params = new URLSearchParams(window.location.search);
        
        // These parameters can exist but don't count as user-applied filters
        const defaultParams = ['page']; 
        
        let hasFilters = false;
        for (const key of params.keys()) {
            if (!defaultParams.includes(key)) {
                hasFilters = true;
                break;
            }
        }
        
        // Special case: sort_by=latest is default, so don't show reset if it's the only param
        if (params.get('sort_by') === 'latest' && params.size <= 2) {
             let onlySortByLatest = true;
             for (const key of params.keys()) {
                 if (key !== 'sort_by' && key !== 'page') {
                     onlySortByLatest = false;
                     break;
                 }
             }
             if (onlySortByLatest) {
                hasFilters = false;
             }
        }
        
        if (hasFilters) {
            resetFiltersWrapper.classList.remove('hidden');
        } else {
            resetFiltersWrapper.classList.add('hidden');
        }
    };

    const fetchProjects = async (url) => {
        // If no URL is provided, construct it from the form
        if (!url) {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            url = filterForm.action + '?' + params.toString();
        }

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const html = await response.text();
            
            window.history.pushState({path: url}, '', url);
            projectListContainer.innerHTML = html;
            
            attachPaginationListeners();
            checkFiltersAndToggleButton();

        } catch (error) {
            console.error('Error fetching projects:', error);
        }
    };

    // --- Event Listeners ---

    filterForm.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', () => fetchProjects());
    });

    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => fetchProjects(), 300);
        });
    }

    const attachPaginationListeners = () => {
        projectListContainer.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', async (e) => {
                e.preventDefault();
                const url = e.target.href;
                await fetchProjects(url);
                projectListContainer.scrollIntoView({ behavior: 'smooth' });
            });
        });
    }

    // --- Initial State Setup ---
    checkFiltersAndToggleButton();
    attachPaginationListeners();
});