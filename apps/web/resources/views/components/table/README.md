# Table Components

Reusable Blade components for building consistent table interfaces with filters, sorting, bulk actions, and pagination.

## Components Overview

### 1. `x-table.wrapper`
Main container for the entire table component.

```blade
<x-table.wrapper>
    <!-- Table content -->
</x-table.wrapper>
```

**Props:**
- `class` (optional): Additional CSS classes

---

### 2. `x-table.header`
Sticky header container for search, actions, and bulk actions.

```blade
<x-table.header>
    <!-- Header content -->
</x-table.header>
```

**Props:**
- `sticky` (default: `true`): Make header sticky
- `class` (optional): Additional CSS classes

---

### 3. `x-table.header-actions`
Container for search bar and action buttons in the header.

```blade
<x-table.header-actions>
    <x-table.search-bar />
    <div class="flex items-center gap-3">
        <!-- Action buttons -->
    </div>
</x-table.header-actions>
```

**Props:**
- `class` (optional): Additional CSS classes

---

### 4. `x-table.search-bar`
Search input field with icon.

```blade
<x-table.search-bar name="search" placeholder="Cari..." />
```

**Props:**
- `name` (default: `'search'`): Input name attribute
- `placeholder` (default: `'Cari...'`): Placeholder text
- `class` (optional): Additional CSS classes

---

### 5. `x-table.bulk-actions`
Bulk actions bar that appears when items are selected.

```blade
<x-table.bulk-actions 
    :selected="$selected" 
    itemName="jurusan"
    deleteAction="deleteSelected"
    deleteConfirm="Apakah Anda yakin ingin menghapus jurusan terpilih?"
/>
```

**Props:**
- `selected` (required): Array of selected item IDs
- `itemName` (default: `'item'`): Name of the item type (singular)
- `deleteAction` (default: `'deleteSelected'`): Method name to call on delete
- `deleteConfirm` (optional): Confirmation message
- `cancelAction` (optional): Cancel action (default: clears selection)
- `class` (optional): Additional CSS classes

---

### 6. `x-table.container`
Scrollable container for the table.

```blade
<x-table.container>
    <table class="table w-full">
        <!-- Table content -->
    </table>
</x-table.container>
```

**Props:**
- `class` (optional): Additional CSS classes

---

### 7. `x-table.sticky-thead`
Sticky table header.

```blade
<x-table.sticky-thead>
    <tr>
        <!-- Header cells -->
    </tr>
</x-table.sticky-thead>
```

**Props:**
- `class` (optional): Additional CSS classes

---

### 8. `x-table.checkbox-header`
Checkbox column header for select all.

```blade
<x-table.checkbox-header />
```

**Props:**
- `class` (default: `'w-12'`): CSS classes for width

---

### 9. `x-table.sortable-header`
Sortable table header cell with sort indicator.

```blade
<x-table.sortable-header 
    field="name" 
    label="Nama" 
    :sortField="$sortField" 
    :sortDirection="$sortDirection" 
/>
```

**Props:**
- `field` (required): Field name to sort by
- `label` (required): Column label
- `sortField` (required): Current sort field from component
- `sortDirection` (required): Current sort direction ('asc' or 'desc')
- `class` (optional): Additional CSS classes

---

### 10. `x-table.checkbox-cell`
Checkbox cell for row selection.

```blade
<x-table.checkbox-cell :value="$item->id" />
```

**Props:**
- `value` (required): Item ID value
- `class` (optional): Additional CSS classes

---

### 11. `x-table.action-menu`
Dropdown action menu for row actions.

```blade
<x-table.action-menu 
    :id="$item->id"
    :actions="[
        [
            'action' => 'editItem',
            'label' => 'Edit',
            'icon' => 'pencil-square',
            'class' => 'text-info'
        ],
        [
            'action' => 'deleteItem',
            'label' => 'Hapus',
            'icon' => 'trash',
            'class' => 'text-error',
            'confirm' => 'Apakah Anda yakin?'
        ]
    ]"
/>
```

**Props:**
- `id` (required): Item ID to pass to actions
- `actions` (required): Array of action configurations
  - `action`: Method name to call (will receive `id` as parameter)
  - `label`: Button label text
  - `icon` (optional): Icon component name
  - `class` (optional): CSS classes for the button
  - `confirm` (optional): Confirmation message
- `dropdownPosition` (default: `'left'`): Dropdown position ('left' or 'right')
- `class` (optional): Additional CSS classes

---

### 12. `x-table.empty-state`
Empty state message when no data is available.

```blade
<x-table.empty-state 
    colspan="4"
    message="Tidak ada data"
    actionLabel="Tambah Data Pertama"
    actionEvent="$dispatch('showCreateForm')"
/>
```

**Props:**
- `colspan` (default: `1`): Number of columns to span
- `message` (default: `'Tidak ada data'`): Empty state message
- `actionLabel` (optional): Label for action button
- `actionEvent` (optional): Livewire event/method to call
- `class` (optional): Additional CSS classes

---

## Complete Example

```blade
<x-table.wrapper>
    {{-- Header --}}
    <x-table.header>
        <div class="p-4">
            <x-table.header-actions>
                <x-table.search-bar name="search" placeholder="Cari..." />
                <div class="flex items-center gap-3">
                    <x-button leftIcon="add" wire:click="$dispatch('showCreateForm')">
                        Tambah Data
                    </x-button>
                </div>
            </x-table.header-actions>
        </div>

        <x-table.bulk-actions 
            :selected="$selected" 
            itemName="data"
            deleteAction="deleteSelected"
        />
    </x-table.header>

    {{-- Table --}}
    <x-table.container>
        <x-table.sticky-thead>
            <tr>
                <x-table.checkbox-header />
                <x-table.sortable-header 
                    field="name" 
                    label="Nama" 
                    :sortField="$sortField" 
                    :sortDirection="$sortDirection" 
                />
                <x-table.sortable-header 
                    field="created_at" 
                    label="Tanggal" 
                    :sortField="$sortField" 
                    :sortDirection="$sortDirection" 
                />
                <th class="w-20"></th>
            </tr>
        </x-table.sticky-thead>

        <tbody>
            @forelse($items as $item)
                <tr wire:key="item-{{ $item->id }}">
                    <x-table.checkbox-cell :value="$item->id" />
                    <td>{{ $item->name }}</td>
                    <td>{{ $item->created_at->format('d/m/Y') }}</td>
                    <x-table.action-menu 
                        :id="$item->id"
                        :actions="[
                            ['action' => 'edit', 'label' => 'Edit', 'icon' => 'pencil-square', 'class' => 'text-info'],
                            ['action' => 'delete', 'label' => 'Hapus', 'icon' => 'trash', 'class' => 'text-error', 'confirm' => 'Yakin?']
                        ]"
                    />
                </tr>
            @empty
                <x-table.empty-state 
                    colspan="4"
                    message="Tidak ada data"
                    actionLabel="Tambah Data Pertama"
                    actionEvent="$dispatch('showCreateForm')"
                />
            @endforelse
        </tbody>
    </x-table.container>

    {{-- Pagination --}}
    {{ $items->links() }}
</x-table.wrapper>
```

## Requirements

These components work with the following traits:
- `WithFilters` - For search functionality
- `WithBulkActions` - For bulk selection and actions
- `WithSorting` - For sortable columns
- `WithPagination` - For pagination

Make sure your Livewire component uses these traits and has the required properties:
- `$search` - Search query
- `$selected` - Selected item IDs array
- `$selectAll` - Select all checkbox state
- `$sortField` - Current sort field
- `$sortDirection` - Current sort direction ('asc' or 'desc')
- `$perPage` - Items per page

