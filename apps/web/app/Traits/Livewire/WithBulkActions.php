<?php

namespace App\Traits\Livewire;

/**
 * Trait for handling bulk actions in Livewire table components
 *
 * Usage:
 *
 * 1. Use the trait in your component:
 *    use App\Traits\Livewire\WithBulkActions;
 *    class MyTable extends Component {
 *        use WithBulkActions;
 *    }
 *
 * 2. Implement getItemsForBulkSelection() to return your items:
 *    public function getItemsForBulkSelection() {
 *        return $this->items; // or $this->departments, etc.
 *    }
 *
 * 3. Override bulkDelete() to implement bulk delete:
 *    public function bulkDelete() {
 *        if (empty($this->selected)) return;
 *        $service = app(MyService::class);
 *        $service->bulkDelete($this->selected);
 *        $this->clearSelection();
 *    }
 *
 * 4. In your Blade template, use:
 *    - wire:model.live="selectAll" for select all checkbox
 *    - wire:model.live="selected" value="{{ $item->id }}" for item checkboxes
 *    - @if(count($selected) > 0) to show bulk actions bar
 *    - wire:click="bulkDelete()" to trigger bulk action
 *
 * Available methods:
 * - hasSelected(): bool - Check if any items are selected
 * - getSelectedCount(): int - Get count of selected items
 * - clearSelection() - Clear all selections
 * - executeBulkAction(string $action) - Execute a bulk action (override in component)
 */
trait WithBulkActions
{
    /**
     * Selected items array
     */
    public $selected = [];

    /**
     * Select all checkbox state
     */
    public $selectAll = false;

    /**
     * Get the collection of items for bulk selection
     * Override this method in your component to return the items collection
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function getItemsForBulkSelection()
    {
        // Override this method in your component
        // Example: return $this->departments;
        return collect([]);
    }

    public function toggleSelect(int $id)
    {
        // pastikan bertipe int (casting) untuk perbandingan konsisten
        $id = (int) $id;

        if (in_array($id, $this->selected, true)) {
            // hapus
            $this->selected = array_values(array_filter($this->selected, fn($id) => (int)$id !== $id));
        } else {
            // tambahkan
            $this->selected[] = $id;
        }

        // opsional: emit event agar parent/komponen lain tahu
        // $this->emit('selectionUpdated', $this->selected);
    }

    /**
     * Handle select all checkbox change
     */
    public function updatedSelectAll($value)
    {
        if ($value) {
            $items = $this->getItemsForBulkSelection();
            $this->selected = $items->pluck('id')->toArray();
        } else {
            $this->selected = [];
        }
    }

    /**
     * Handle individual item selection
     */
    public function updatedSelected()
    {
        $this->selectAll = false;
    }

    /**
     * Clear all selections
     */
    public function clearSelection()
    {
        $this->selected = [];
        $this->selectAll = false;
    }

    /**
     * Check if any items are selected
     */
    public function hasSelected(): bool
    {
        return !empty($this->selected);
    }

    /**
     * Get count of selected items
     */
    public function getSelectedCount(): int
    {
        return count($this->selected);
    }

    /**
     * Execute bulk action
     * Override this method in your component to implement specific bulk actions
     *
     * @param string $action The action to perform (e.g., 'delete', 'restore', 'activate')
     * @return void
     */
    public function executeBulkAction(string $action)
    {
        if (empty($this->selected)) {
            return;
        }

        // Override this method in your component to handle specific actions
        // Example:
        // switch ($action) {
        //     case 'delete':
        //         $this->bulkDelete();
        //         break;
        //     case 'restore':
        //         $this->bulkRestore();
        //         break;
        // }
    }

    /**
     * Bulk delete selected items
     * Override this method in your component to implement bulk delete
     */
    public function bulkDelete()
    {
        // Override in component
    }

    /**
     * Bulk restore selected items
     * Override this method in your component to implement bulk restore
     */
    public function bulkRestore()
    {
        // Override in component
    }

    /**
     * Bulk force delete selected items
     * Override this method in your component to implement bulk force delete
     */
    public function bulkForceDelete()
    {
        // Override in component
    }
}

