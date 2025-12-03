
# Alert Modal Usage Guide

The `WithAlertModal` trait provides easy-to-use methods for showing alert modals in your Livewire components.

## Setup

1. **Include the trait in your component:**
```php
use App\Traits\Livewire\WithAlertModal;

class MyComponent extends Component
{
    use WithAlertModal;
    
    // Your component code...
}
```

2. **Make sure AlertModal is included in your layout:**
The AlertModal is already included in `app.blade.php` layout, so it's available globally.

## Usage Examples

### 1. Success Alert (Simple)

```php
public function saveData()
{
    try {
        // Your save logic
        $this->showSuccessAlert('Data berhasil disimpan!');
    } catch (\Exception $e) {
        $this->showErrorAlert('Gagal menyimpan data: ' . $e->getMessage());
    }
}
```

### 2. Success Alert (Custom Title)

```php
$this->showSuccessAlert(
    message: 'Data berhasil dihapus.',
    title: 'Hapus Berhasil!',
    actionText: 'OK'
);
```

### 3. Error Alert

```php
try {
    // Some operation
} catch (\Exception $e) {
    $this->showErrorAlert('Terjadi kesalahan: ' . $e->getMessage());
}
```

### 4. Warning Alert

```php
if ($someCondition) {
    $this->showWarningAlert('Perhatian: Data akan dihapus permanen!');
}
```

### 5. Info Alert

```php
$this->showInfoAlert('Sistem akan melakukan maintenance pada pukul 00:00 WIB.');
```

### 6. Confirmation Alert (with Cancel Button)

```php
public function deleteItem($id)
{
    $this->showConfirmAlert(
        message: 'Apakah Anda yakin ingin menghapus item ini?',
        title: 'Konfirmasi Hapus',
        actionText: 'Ya, Hapus',
        cancelText: 'Batal',
        actionMethod: 'confirmDelete'
    );
}

public function confirmDelete()
{
    // Perform actual delete
    $this->showSuccessAlert('Item berhasil dihapus.');
}
```

### 7. Alert with Redirect

```php
public function processPayment()
{
    // Process payment
    $this->showAlertWithRedirect(
        message: 'Pembayaran berhasil!',
        url: route('payment.success'),
        title: 'Pembayaran Berhasil',
        actionText: 'Lihat Detail'
    );
}
```

### 8. Custom Alert Configuration

```php
$this->showAlert([
    'title' => 'Custom Title',
    'message' => 'Custom message here',
    'type' => 'success', // success, error, warning, info
    'actionText' => 'OK',
    'actionUrl' => route('some.route'), // Optional: redirect URL
    'actionMethod' => 'someMethod', // Optional: method to call
    'showCancelButton' => true, // Optional: show cancel button
    'cancelText' => 'Batal', // Optional: cancel button text
    'size' => 'lg', // Optional: sm, md, lg, xl
]);
```

## Available Methods

### Quick Methods

- `showSuccessAlert($message, $title = 'Berhasil!', $actionText = 'Tutup')`
- `showErrorAlert($message, $title = 'Error!', $actionText = 'Tutup')`
- `showWarningAlert($message, $title = 'Peringatan!', $actionText = 'Tutup')`
- `showInfoAlert($message, $title = 'Informasi', $actionText = 'Tutup')`

### Advanced Methods

- `showConfirmAlert($message, $title, $actionText, $cancelText, $actionMethod)` - Shows confirmation dialog
- `showAlertWithRedirect($message, $url, $title, $actionText, $type)` - Shows alert with redirect URL
- `showAlert($config)` - Full custom configuration

## Real-World Examples

### Example 1: Form Submission

```php
public function save()
{
    $this->validate();
    
    try {
        $data = MyModel::create($this->formData);
        
        $this->showSuccessAlert('Data berhasil disimpan!');
        $this->resetForm();
        $this->dispatch('refresh-table');
    } catch (\Exception $e) {
        $this->showErrorAlert('Gagal menyimpan: ' . $e->getMessage());
    }
}
```

### Example 2: Delete with Confirmation

```php
public function delete($id)
{
    $this->showConfirmAlert(
        message: 'Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.',
        title: 'Konfirmasi Hapus',
        actionText: 'Ya, Hapus',
        cancelText: 'Batal',
        actionMethod: 'confirmDelete'
    );
    
    $this->itemToDelete = $id;
}

public function confirmDelete()
{
    try {
        MyModel::find($this->itemToDelete)->delete();
        $this->showSuccessAlert('Data berhasil dihapus.');
        $this->dispatch('refresh-table');
    } catch (\Exception $e) {
        $this->showErrorAlert('Gagal menghapus: ' . $e->getMessage());
    }
}
```

### Example 3: Bulk Operations

```php
public function bulkDelete()
{
    if (empty($this->selected)) {
        $this->showWarningAlert('Pilih minimal satu item untuk dihapus.');
        return;
    }
    
    try {
        MyModel::whereIn('id', $this->selected)->delete();
        $this->clearSelection();
        $this->showSuccessAlert(count($this->selected) . ' item berhasil dihapus.');
        $this->dispatch('refresh-table');
    } catch (\Exception $e) {
        $this->showErrorAlert('Gagal menghapus item: ' . $e->getMessage());
    }
}
```

### Example 4: Import/Export Operations

```php
public function importData()
{
    try {
        // Import logic
        $count = $this->processImport();
        
        $this->showSuccessAlert(
            message: "Berhasil mengimpor {$count} data.",
            title: 'Import Berhasil'
        );
    } catch (\Exception $e) {
        $this->showErrorAlert('Gagal mengimpor data: ' . $e->getMessage());
    }
}
```

### Example 5: Status Change

```php
public function changeStatus($id, $status)
{
    try {
        $item = MyModel::find($id);
        $item->update(['status' => $status]);
        
        $statusText = $status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        $this->showSuccessAlert("Status berhasil {$statusText}.");
    } catch (\Exception $e) {
        $this->showErrorAlert('Gagal mengubah status: ' . $e->getMessage());
    }
}
```

## Alert Types

- **success** - Green, checkmark icon
- **error** - Red, X icon
- **warning** - Yellow, warning icon
- **info** - Blue, info icon

## Notes

- The AlertModal is global and available in all components that use the layout
- You don't need to include the modal component in each view
- All methods automatically dispatch the `showAlertModal` event
- The modal will close automatically when the action button is clicked
- For confirmation dialogs, use `showConfirmAlert` and handle the confirmation in a separate method


