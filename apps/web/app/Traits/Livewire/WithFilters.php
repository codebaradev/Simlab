<?php

namespace App\Traits\Livewire;

/**
 * Trait for handling filters in Livewire table components
 *
 * Usage:
 *
 * 1. Use the trait in your component:
 *    use App\Traits\Livewire\WithFilters;
 *    class MyTable extends Component {
 *        use WithFilters;
 *    }
 *
 * 2. Add custom filter properties:
 *    public $selectedStatus = '';
 *    public $selectedDate = '';
 *
 * 3. Override getFilters() to include custom filters:
 *    public function getFilters(): array {
 *        $filters = parent::getFilters();
 *        if (!empty($this->selectedStatus)) {
 *            $filters['status'] = $this->selectedStatus;
 *        }
 *        return $filters;
 *    }
 *
 * 4. Override clearFilters() to clear custom filters:
 *    public function clearFilters() {
 *        parent::clearFilters();
 *        $this->selectedStatus = '';
 *    }
 *
 * 5. Override getFilterQueryString() to add custom filters to query string:
 *    public function getFilterQueryString(): array {
 *        return array_merge(parent::getFilterQueryString(), [
 *            'selectedStatus' => ['except' => ''],
 *        ]);
 *    }
 */
trait WithFilters
{
    /**
     * Search query string
     */
    public $search = '';

    /**
     * Additional filter properties
     * Override this in your component to add custom filters
     * Example: public $selectedStatus = ''; public $selectedDate = '';
     */

    /**
     * Get filter values as an array
     * Override this method in your component to customize which filters to include
     */
    public function getFilters(): array
    {
        $filters = [];

        if (!empty($this->search)) {
            $filters['search'] = $this->search;
        }

        // Add custom filters by checking if properties exist
        // Example: if (property_exists($this, 'selectedStatus') && !empty($this->selectedStatus)) {
        //     $filters['status'] = $this->selectedStatus;
        // }

        return $filters;
    }

    /**
     * Clear all filters and reset to default values
     * Override this method in your component to clear custom filters
     */
    public function clearFilters()
    {
        $this->search = '';

        // Clear custom filters
        // Example: $this->selectedStatus = '';

        $this->resetPage();
    }

    /**
     * Get query string configuration for filters
     * Override this method in your component to add custom filters to query string
     */
    public function getFilterQueryString(): array
    {
        $queryString = [
            'search' => ['except' => ''],
        ];

        // Add custom filters to query string
        // Example: $queryString['selectedStatus'] = ['except' => ''];

        return $queryString;
    }

    /**
     * Reset filters when search is updated
     */
    public function updatedSearch()
    {
        $this->resetPage();
    }
}

