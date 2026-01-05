<?php

namespace App\Traits\Livewire;

/**
 * Combined trait that includes filters, bulk actions, and sorting
 * Use this trait if you need all three features in your table component
 *
 * Usage:
 *
 * 1. Use the trait in your component:
 *    use App\Traits\Livewire\WithTableFeatures;
 *    class MyTable extends Component {
 *        use WithPagination, WithTableFeatures;
 *    }
 *
 * 2. This trait includes all methods from:
 *    - WithFilters (search, filters, clearFilters)
 *    - WithBulkActions (selected, selectAll, bulk actions)
 *    - WithSorting (sortField, sortDirection, sortBy)
 *
 * 3. Use getTableQueryString() to get combined query string config:
 *    protected $queryString = $this->getTableQueryString();
 *
 * 4. Use clearAll() to clear filters, selections, and reset sorting:
 *    public function clearAll() {
 *        parent::clearAll();
 *        // Add any additional cleanup
 *    }
 *
 * Note: You still need to implement:
 * - getItemsForBulkSelection() from WithBulkActions
 * - Override getFilters() if you have custom filters
 * - Override bulkDelete() or other bulk action methods
 */
trait WithTableFeatures
{
    use WithFilters, WithBulkActions, WithSorting;

    /**
     * Per page pagination
     */
    public $perPage = 10;

    /**
     * Get available per page options
     * Override this method in your component to customize options
     *
     * @return array
     */
    public function getPerPageOptions(): array
    {
        return [10, 25, 50, 100];
    }

    /**
     * Handle per page change - reset to first page
     */
    public function updatedPerPage()
    {
        $this->resetPage();
    }

    /**
     * Get query string configuration combining all features
     */
    public function getTableQueryString(): array
    {
        return array_merge(
            $this->getFilterQueryString(),
            $this->getSortingQueryString(),
            [
                'perPage' => ['except' => 10],
            ]
        );
    }

    /**
     * Clear all filters and reset selections
     */
    public function clearAll()
    {
        $this->clearFilters();
        $this->clearSelection();
        $this->resetSorting();
    }
}

