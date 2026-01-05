<!-- resources/views/livewire/lhead/dashboard.blade.php -->
<div class="p-8">
    <!-- Header -->
    <x-page.header
        class="mb-4"
        title="Dashboard"
        :breadcrumbs="[
            ['label' => 'Dashboard'],
        ]"
    />

    @if($isLoading)
    <!-- Loading State -->
    <div class="flex justify-center items-center py-12">
        <div class="text-center">
            <span class="loading loading-spinner loading-lg text-primary"></span>
            <p class="mt-4 text-gray-600">Memuat data dashboard...</p>
        </div>
    </div>
    @else
    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
        <!-- Pending Requests -->
        <div class="card bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-yellow-700">Menunggu Persetujuan</h3>
                        <p class="text-2xl font-bold text-yellow-800">{{ $stats['pending_requests'] }}</p>
                        <p class="text-xs text-yellow-600 mt-1">{{ $stats['pending_rate'] }}% dari total</p>
                    </div>
                    <div class="text-yellow-500">
                        <i class="fas fa-clock text-3xl"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('schedule.request.index') }}" class="text-xs text-yellow-600 hover:text-yellow-800 flex items-center gap-1">
                        <i class="fas fa-external-link-alt"></i>
                        Lihat Semua
                    </a>
                </div>
            </div>
        </div>

        <!-- Approved Requests -->
        <div class="card bg-gradient-to-br from-green-50 to-green-100 border border-green-200">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-green-700">Disetujui</h3>
                        <p class="text-2xl font-bold text-green-800">{{ $stats['approved_requests'] }}</p>
                        <p class="text-xs text-green-600 mt-1">{{ $stats['approval_rate'] }}% dari total</p>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-check-circle text-3xl"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('schedule.request.index') }}" class="text-xs text-green-600 hover:text-green-800 flex items-center gap-1">
                        <i class="fas fa-external-link-alt"></i>
                        Lihat Semua
                    </a>
                </div>
            </div>
        </div>

        <!-- Today's Schedules -->
        <div class="card bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-blue-700">Jadwal Hari Ini</h3>
                        <p class="text-2xl font-bold text-blue-800">{{ $stats['today_schedules'] }}</p>
                        <p class="text-xs text-blue-600 mt-1">{{ $todaySchedules->count() }} aktif</p>
                    </div>
                    <div class="text-blue-500">
                        <i class="fas fa-calendar-day text-3xl"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('schedule.request.index') }}" class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <i class="fas fa-external-link-alt"></i>
                        Lihat Jadwal
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Approved -->
        <div class="card bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-purple-700">Disetujui (7 Hari)</h3>
                        <p class="text-2xl font-bold text-purple-800">{{ $stats['recent_approved'] }}</p>
                        <p class="text-xs text-purple-600 mt-1">Rata-rata: {{ round($stats['recent_approved'] / 7, 1) }}/hari</p>
                    </div>
                    <div class="text-purple-500">
                        <i class="fas fa-chart-line text-3xl"></i>
                    </div>
                </div>
                {{-- <div class="mt-2">
                    <a href="{{ route('reports.approvals') }}" class="text-xs text-purple-600 hover:text-purple-800 flex items-center gap-1">
                        <i class="fas fa-external-link-alt"></i>
                        Lihat Laporan
                    </a>
                </div> --}}
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Left Column: Pending Requests & Quick Actions -->
        <div class="lg:col-span-2">
            <!-- Pending Schedule Requests -->
            <div class="card bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="card-title text-lg font-bold">Permintaan Jadwal Menunggu</h2>
                        <a href="{{ route('schedule.request.index') }}" class="link link-primary text-sm">
                            Lihat Semua
                        </a>
                    </div>

                    @if($pendingRequests->isEmpty())
                    <div class="text-center py-8">
                        <i class="fas fa-inbox text-3xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Tidak ada permintaan jadwal yang menunggu</p>
                    </div>
                    @else
                    <div class="space-y-4">
                        @foreach($pendingRequests as $request)
                        <div class="border border-yellow-200 rounded-lg p-4 hover:border-yellow-300 transition-colors bg-yellow-50">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="{{ $this->getRequestStatusBadge($request->status)['class'] }} gap-2">
                                            <i class="fas fa-{{ $this->getRequestStatusBadge($request->status)['icon'] }}"></i>
                                            {{ $this->getRequestStatusBadge($request->status)['text'] }}
                                        </span>
                                        <span class="{{ $this->getCategoryBadge($request->category)['class'] }}">
                                            {{ $this->getCategoryBadge($request->category)['text'] }}
                                        </span>
                                        @if($request->repeat_count > 0)
                                        <span class="badge badge-info">
                                            {{ $request->repeat_count }}x pengulangan
                                        </span>
                                        @endif
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <p class="text-sm font-medium">Pengaju:</p>
                                            <p class="text-sm text-gray-600">{{ $request->user->name }}</p>
                                        </div>
                                        @if($request->lecturer)
                                        <div>
                                            <p class="text-sm font-medium">Dosen:</p>
                                            <p class="text-sm text-gray-600">{{ $request->lecturer->user->name ?? 'N/A' }}</p>
                                        </div>
                                        @endif
                                    </div>

                                    @if($request->information)
                                    <div class="mt-3">
                                        <p class="text-sm font-medium">Informasi:</p>
                                        <p class="text-sm text-gray-600 line-clamp-2">{{ $request->information }}</p>
                                    </div>
                                    @endif

                                    <div class="mt-3 text-xs text-gray-500">
                                        Diajukan: {{ $request->created_at->diffForHumans() }}
                                    </div>
                                </div>

                                <div class="flex flex-col gap-2">
                                    <button wire:click="selectRequest({{ $request->id }})"
                                            class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye mr-1"></i>
                                        Tinjau
                                    </button>
                                    <a href="{{ route('schedule.request.index', $request->id) }}"
                                       class="btn btn-sm btn-outline">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Today's Schedules -->
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h2 class="card-title text-lg font-bold mb-4">Jadwal Hari Ini</h2>

                    @if($todaySchedules->isEmpty())
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-3xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Tidak ada jadwal hari ini</p>
                    </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Mata Kuliah</th>
                                    <th>Ruangan</th>
                                    <th>Pengaju</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todaySchedules as $schedule)
                                <tr>
                                    <td>{{ $this->getFormattedTime($schedule->time) }}</td>
                                    <td>{{ $schedule->course->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($schedule->rooms->isNotEmpty())
                                        {{ $schedule->rooms->first()->name }}
                                        @if($schedule->rooms->count() > 1)
                                        <span class="badge badge-xs">+{{ $schedule->rooms->count() - 1 }}</span>
                                        @endif
                                        @else
                                        <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $schedule->schedule_request->user->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $schedule->is_open ? 'badge-success' : 'badge-error' }}">
                                            {{ $schedule->is_open ? 'Buka' : 'Tutup' }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Recent Activities & Quick Actions -->
        <div class="lg:col-span-1">
            <!-- Recent Approvals & Rejections -->
            <div class="card bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg font-bold mb-4">Aktivitas Terkini</h2>

                    <div class="space-y-4">
                        <!-- Recent Approvals -->
                        <div>
                            <h3 class="font-semibold text-sm mb-2 text-success flex items-center gap-1">
                                <i class="fas fa-check-circle"></i>
                                Disetujui (7 Hari)
                            </h3>
                            <div class="space-y-2">
                                @forelse($approvedRequests as $request)
                                <div class="p-2 bg-green-50 rounded-lg">
                                    <p class="text-sm font-medium">{{ $request->user->name }}</p>
                                    <p class="text-xs text-gray-600 line-clamp-1">{{ $request->information ?: 'Tidak ada keterangan' }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $request->updated_at->diffForHumans() }}</p>
                                </div>
                                @empty
                                <p class="text-sm text-gray-500">Belum ada persetujuan</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Recent Rejections -->
                        <div>
                            <h3 class="font-semibold text-sm mb-2 text-error flex items-center gap-1">
                                <i class="fas fa-times-circle"></i>
                                Ditolak (7 Hari)
                            </h3>
                            <div class="space-y-2">
                                @forelse($rejectedRequests as $request)
                                <div class="p-2 bg-red-50 rounded-lg">
                                    <p class="text-sm font-medium">{{ $request->user->name }}</p>
                                    <p class="text-xs text-gray-600 line-clamp-1">{{ $request->information ?: 'Tidak ada keterangan' }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ $request->updated_at->diffForHumans() }}</p>
                                </div>
                                @empty
                                <p class="text-sm text-gray-500">Belum ada penolakan</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Schedules -->
            <div class="card bg-base-100 shadow-lg mb-6">
                <div class="card-body">
                    <h2 class="card-title text-lg font-bold mb-4">Jadwal Mendatang (3 Hari)</h2>

                    @if($upcomingSchedules->isEmpty())
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-alt text-2xl text-gray-300 mb-2"></i>
                        <p class="text-gray-500 text-sm">Tidak ada jadwal mendatang</p>
                    </div>
                    @else
                    <div class="space-y-3">
                        @foreach($upcomingSchedules as $schedule)
                        <div class="border border-gray-200 rounded-lg p-3 hover:border-primary transition-colors">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-semibold text-sm">{{ $schedule->course->name ?? 'N/A' }}</h4>
                                <span class="badge badge-sm {{ $schedule->is_open ? 'badge-success' : 'badge-error' }}">
                                    {{ $schedule->is_open ? 'Buka' : 'Tutup' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mb-1">
                                {{ $schedule->start_date->translatedFormat('D, d/m') }} • {{ $this->getFormattedTime($schedule->time) }}
                            </p>
                            @if($schedule->rooms->isNotEmpty())
                            <p class="text-xs text-gray-600">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                {{ $schedule->rooms->first()->name }}
                            </p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h2 class="card-title text-lg font-bold mb-4">Aksi Cepat</h2>
                    <div class="space-y-3">
                        <a href="{{ route('schedule.request.index') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-plus mr-2"></i>
                            Buat Permintaan
                        </a>
                        <a href="{{ route('schedule.request.index') }}" class="btn btn-outline btn-warning btn-block">
                            <i class="fas fa-clock mr-2"></i>
                            Tinjau Semua Pending
                        </a>
                        <a href="{{ route('schedule.request.index') }}" class="btn btn-outline btn-block">
                            <i class="fas fa-calendar-alt mr-2"></i>
                            Kelola Jadwal
                        </a>
                        {{-- <a href="{{ route('reports.monthly') }}" class="btn btn-outline btn-block">
                            <i class="fas fa-chart-bar mr-2"></i>
                            Laporan Bulanan
                        </a> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Approval Modal -->
    @if($showModal && $selectedRequest)
    <div class="modal modal-open">
        <div class="modal-box max-w-2xl">
            <h3 class="font-bold text-lg mb-4">Tinjau Permintaan Jadwal</h3>

            <!-- Request Details -->
            <div class="space-y-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Pengaju</span>
                        </label>
                        <p class="p-2 bg-base-200 rounded">{{ $selectedRequest->user->name }}</p>
                    </div>

                    @if($selectedRequest->lecturer)
                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Dosen</span>
                        </label>
                        <p class="p-2 bg-base-200 rounded">{{ $selectedRequest->lecturer->user->name ?? 'N/A' }}</p>
                    </div>
                    @endif

                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Kategori</span>
                        </label>
                        <span class="{{ $this->getCategoryBadge($selectedRequest->category)['class'] }}">
                            {{ $this->getCategoryBadge($selectedRequest->category)['text'] }}
                        </span>
                    </div>

                    <div>
                        <label class="label">
                            <span class="label-text font-semibold">Pengulangan</span>
                        </label>
                        <p class="p-2 bg-base-200 rounded">{{ $selectedRequest->repeat_count }}x</p>
                    </div>
                </div>

                @if($selectedRequest->information)
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Informasi Permintaan</span>
                    </label>
                    <div class="p-3 bg-base-200 rounded">
                        <p class="whitespace-pre-line">{{ $selectedRequest->information }}</p>
                    </div>
                </div>
                @endif

                @if($selectedRequest->schedules->isNotEmpty())
                <div>
                    <label class="label">
                        <span class="label-text font-semibold">Jadwal Terkait</span>
                    </label>
                    <div class="p-3 bg-base-200 rounded">
                        @foreach($selectedRequest->schedules as $schedule)
                        <div class="mb-2 last:mb-0">
                            <p class="font-medium">{{ $schedule->course->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600">
                                {{ $schedule->start_date->format('d/m/Y') }} •
                                {{ $this->getFormattedTime($schedule->time) }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Approval Notes -->
            <div class="mb-6">
                <label class="label">
                    <span class="label-text font-semibold">Catatan Persetujuan</span>
                    <span class="label-text-alt">Opsional untuk persetujuan</span>
                </label>
                <textarea wire:model="approvalNote"
                          class="textarea textarea-bordered w-full"
                          placeholder="Tambahkan catatan jika diperlukan..."
                          rows="3"></textarea>
                @error('approvalNote') <span class="text-error text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Modal Actions -->
            <div class="modal-action">
                <button wire:click="closeModal" class="btn btn-ghost">Batal</button>
                <button wire:click="rejectRequest"
                        class="btn btn-error"
                        wire:loading.attr="disabled">
                    <i class="fas fa-times mr-2"></i>
                    Tolak
                </button>
                <button wire:click="approveRequest"
                        class="btn btn-success"
                        wire:loading.attr="disabled">
                    <i class="fas fa-check mr-2"></i>
                    Setujui
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Livewire Scripts -->
    <script>
        document.addEventListener('livewire:init', () => {
            // Auto-refresh setiap 2 menit untuk permintaan pending
            setInterval(() => {
                Livewire.dispatch('refresh');
            }, 120000);

            // Close modal on ESC key
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    Livewire.dispatch('close-modal');
                }
            });
        });
    </script>

    <!-- Styles -->
    <style>
        .card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .line-clamp-1 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 1;
        }

        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }

        .modal {
            z-index: 1000;
        }
    </style>
</div>
