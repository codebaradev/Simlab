<?php

namespace App\Traits\Livewire;

/**
 * Trait for handling sorting in Livewire table components
 *
 * Usage:
 *
 * 1. Use the trait in your component:
 *    use App\Traits\Livewire\WithSorting;
 *    class MyTable extends Component {
 *        use WithSorting;
 *
 *        protected function getDefaultSortField(): string {
 *            return 'name';
 *        }
 *
 *        protected function getDefaultSortDirection(): string {
 *            return 'asc';
 *        }
 *
 *        protected function getSortableFields(): array {
 *            return ['name', 'email', 'created_at']; // optional
 *        }
 *    }
 *
 * 2. In your Blade template:
 *    <th wire:click="sortBy('name')">
 *        Name
 *        @if($sortField === 'name')
 *            <svg class="{{ $sortDirection === 'asc' ? '' : 'rotate-180' }}">...</svg>
 *        @endif
 *    </th>
 *
 * 3. Override getSortingQueryString() if needed:
 *    public function getSortingQueryString(): array {
 *        return [
 *            'sortField' => ['except' => 'name'],
 *            'sortDirection' => ['except' => 'asc'],
 *        ];
 *    }
 *
 * Available methods:
 * - sortBy(string $field) - Sort by field
 * - resetSorting() - Reset to default sorting
 * - isSortingField(string $field): bool - Check if field is being sorted
 * - getSortIconClass(string $field): string - Get CSS class for sort icon
 */
trait WithSorting
{
    /**
     * Current sort field
     */
    public $sortField = 'id';

    /**
     * Current sort direction (asc or desc)
     */
    public $sortDirection = 'asc';

    /**
     * Get default sort field
     * Override this method in your component to set a different default
     *
     * @return string
     */
    protected function getDefaultSortField(): string
    {
        return 'id';
    }

    /**
     * Get default sort direction
     * Override this method in your component to set a different default
     *
     * @return string
     */
    protected function getDefaultSortDirection(): string
    {
        return 'asc';
    }

    /**
     * Get sortable fields
     * Override this method in your component to define which fields can be sorted
     * Example: return ['name', 'email', 'created_at'];
     *
     * @return array
     */
    protected function getSortableFields(): array
    {
        return [];
    }

    /**
     * Sort by field
     *
     * @param string $field The field to sort by
     * @return void
     */
    public function sortBy($field)
    {
        // Check if field is sortable (if sortableFields is defined)
        $sortableFields = $this->getSortableFields();
        if (!empty($sortableFields) && !in_array($field, $sortableFields)) {
            return;
        }

        if ($this->sortField === $field) {
            // Toggle direction if same field
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            // Set new field with ascending direction
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    /**
     * Reset sorting to default
     */
    public function resetSorting()
    {
        $this->sortField = $this->getDefaultSortField();
        $this->sortDirection = $this->getDefaultSortDirection();
    }

    /**
     * Get query string configuration for sorting
     */
    public function getSortingQueryString(): array
    {
        return [
            'sortField' => ['except' => $this->getDefaultSortField()],
            'sortDirection' => ['except' => $this->getDefaultSortDirection()],
        ];
    }

    /**
     * Check if a field is currently being sorted
     *
     * @param string $field
     * @return bool
     */
    public function isSortingField(string $field): bool
    {
        return $this->sortField === $field;
    }

    /**
     * Get sort icon class based on direction
     *
     * @param string $field
     * @return string
     */
    public function getSortIconClass(string $field): string
    {
        if (!$this->isSortingField($field)) {
            return '';
        }

        return $this->sortDirection === 'asc' ? '' : 'rotate-180';
    }
}

