<!-- resources/views/livewire/admin/dashboard.blade.php -->
<div class="container mx-auto px-4 py-6">
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
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4 mb-8">
        <!-- Users Card -->
        <div class="card bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-blue-700">Total Users</h3>
                        <p class="text-2xl font-bold text-blue-800">{{ $stats['total_users'] }}</p>
                    </div>
                    <div class="text-blue-500">
                        <i class="fas fa-users text-3xl"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('student.index') }}" class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <i class="fas fa-external-link-alt"></i>
                        Kelola Users
                    </a>
                </div>
            </div>
        </div>

        <!-- Students Card -->
        <div class="card bg-gradient-to-br from-green-50 to-green-100 border border-green-200">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-green-700">Mahasiswa</h3>
                        <p class="text-2xl font-bold text-green-800">{{ $stats['total_students'] }}</p>
                    </div>
                    <div class="text-green-500">
                        <i class="fas fa-user-graduate text-3xl"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('student.index') }}" class="text-xs text-green-600 hover:text-green-800 flex items-center gap-1">
                        <i class="fas fa-external-link-alt"></i>
                        Kelola Mahasiswa
                    </a>
                </div>
            </div>
        </div>

        <!-- Lecturers Card -->
        <div class="card bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-purple-700">Dosen</h3>
                        <p class="text-2xl font-bold text-purple-800">{{ $stats['total_lecturers'] }}</p>
                    </div>
                    <div class="text-purple-500">
                        <i class="fas fa-chalkboard-teacher text-3xl"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('lecturer.index') }}" class="text-xs text-purple-600 hover:text-purple-800 flex items-center gap-1">
                        <i class="fas fa-external-link-alt"></i>
                        Kelola Dosen
                    </a>
                </div>
            </div>
        </div>

        <!-- Courses Card -->
        <div class="card bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-yellow-700">Mata Kuliah</h3>
                        <p class="text-2xl font-bold text-yellow-800">{{ $stats['total_courses'] }}</p>
                    </div>
                    <div class="text-yellow-500">
                        <i class="fas fa-book text-3xl"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('course.index') }}" class="text-xs text-yellow-600 hover:text-yellow-800 flex items-center gap-1">
                        <i class="fas fa-external-link-alt"></i>
                        Kelola Matkul
                    </a>
                </div>
            </div>
        </div>

        <!-- Rooms Card -->
        <div class="card bg-gradient-to-br from-red-50 to-red-100 border border-red-200">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-red-700">Ruangan</h3>
                        <p class="text-2xl font-bold text-red-800">{{ $stats['total_rooms'] }}</p>
                    </div>
                    <div class="text-red-500">
                        <i class="fas fa-door-closed text-3xl"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('room.index') }}" class="text-xs text-red-600 hover:text-red-800 flex items-center gap-1">
                        <i class="fas fa-external-link-alt"></i>
                        Kelola Ruangan
                    </a>
                </div>
            </div>
        </div>

        <!-- Rooms Card -->
        <div class="card bg-gradient-to-br from-pink-50 to-pink-100 border border-pink-200">
            <div class="card-body p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-pink-700">Komputer</h3>
                        <p class="text-2xl font-bold text-pink-800">{{ $stats['total_computers'] }}</p>
                    </div>
                    <div class="text-pink-500">
                        <i class="fas fa-door-closed text-3xl"></i>
                    </div>
                </div>
                <div class="mt-2">
                    <a href="{{ route('room.index') }}" class="text-xs text-pink-600 hover:text-pink-800 flex items-center gap-1">
                        <i class="fas fa-external-link-alt"></i>
                        Kelola Komputer
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Left Column: Recent Activities & Room Status -->
        <div class="lg:col-span-2">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Recent Activities -->
                <div class="card bg-base-100 shadow-lg">
                    <div class="card-body">
                        <h2 class="card-title text-lg font-bold mb-4">Aktivitas Terkini</h2>
                        <div class="space-y-4">
                            @forelse($recentActivities as $activity)
                            <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-base-200 transition-colors">
                                <div class="{{ $activity['color'] }}">
                                    <i class="fas fa-{{ $activity['icon'] }} text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="font-semibold text-sm">{{ $activity['title'] }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $activity['description'] }}</p>
                                    <span class="text-xs text-gray-400 mt-1">{{ $activity['time'] }}</span>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8">
                                <i class="fas fa-history text-3xl text-gray-300 mb-3"></i>
                                <p class="text-gray-500">Tidak ada aktivitas terkini</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Room Status Summary -->
                <div class="card bg-base-100 shadow-lg">
                    <div class="card-body">
                        <h2 class="card-title text-lg font-bold mb-4">Status Ruangan</h2>
                        <div class="space-y-4">
                            <!-- Progress Bar -->
                            <div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm font-medium">Ketersediaan Ruangan</span>
                                    <span class="text-sm font-bold">{{ $roomStatusSummary['available_percentage'] }}%</span>
                                </div>
                                <progress class="progress progress-primary w-full"
                                          value="{{ $roomStatusSummary['available_percentage'] }}"
                                          max="100"></progress>
                            </div>

                            <!-- Status Breakdown -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center p-3 bg-green-50 rounded-lg">
                                    <div class="text-2xl font-bold text-green-600">{{ $roomStatusSummary['available'] }}</div>
                                    <div class="text-sm text-green-700 mt-1">Tersedia</div>
                                </div>
                                <div class="text-center p-3 bg-red-50 rounded-lg">
                                    <div class="text-2xl font-bold text-red-600">{{ $roomStatusSummary['occupied'] }}</div>
                                    <div class="text-sm text-red-700 mt-1">Terisi</div>
                                </div>
                                <div class="text-center p-3 bg-yellow-50 rounded-lg">
                                    <div class="text-2xl font-bold text-yellow-600">{{ $roomStatusSummary['maintenance'] }}</div>
                                    <div class="text-sm text-yellow-700 mt-1">Perawatan</div>
                                </div>
                                <div class="text-center p-3 bg-blue-50 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-600">{{ $roomStatusSummary['total'] }}</div>
                                    <div class="text-sm text-blue-700 mt-1">Total</div>
                                </div>
                            </div>

                            <!-- Top Rooms by Usage -->
                            <div class="mt-6">
                                <h3 class="font-semibold mb-3">Ruangan Paling Aktif</h3>
                                <div class="space-y-2">
                                    @forelse($topRoomsBySchedule as $room)
                                    <div class="flex justify-between items-center p-2 hover:bg-base-200 rounded">
                                        <div>
                                            <span class="font-medium">{{ $room['name'] }}</span>
                                            <span class="text-sm text-gray-500 ml-2">({{ $room['code'] }})</span>
                                        </div>
                                        <span class="badge badge-primary">
                                            {{ $room['schedule_count'] }} jadwal
                                        </span>
                                    </div>
                                    @empty
                                    <p class="text-gray-500 text-sm">Tidak ada data ruangan</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Today's Schedules -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h2 class="card-title text-lg font-bold mb-4">Jadwal Hari Ini</h2>

                    @if($todaySchedules->isEmpty())
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-3xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Tidak ada jadwal hari ini</p>
                    </div>
                    @else
                    <div class="space-y-4">
                        @foreach($todaySchedules->take(4) as $schedule)
                        <div class="border border-gray-200 rounded-lg p-3 hover:border-primary transition-colors">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-semibold text-sm">{{ $schedule->course->name ?? 'N/A' }}</h4>
                                <span class="badge badge-sm {{ $schedule->is_open ? 'badge-success' : 'badge-error' }}">
                                    {{ $schedule->is_open ? 'Buka' : 'Tutup' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mb-2">{{ $this->getFormattedTime($schedule->time) }}</p>
                            @if($schedule->rooms->isNotEmpty())
                            <p class="text-xs text-gray-600">
                                <i class="fas fa-map-marker-alt mr-1"></i>
                                {{ $schedule->rooms->pluck('name')->join(', ') }}
                            </p>
                            @endif
                            <div class="mt-2">
                                <div class="flex justify-between text-xs text-gray-500">
                                    <span>Kehadiran:</span>
                                    <span class="font-semibold">{{ $this->getAttendancePercentage($schedule) }}%</span>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        @if($todaySchedules->count() > 4)
                        <div class="text-center">
                            <a href="{{ route('schedule.index') }}" class="link link-primary text-sm">
                                + {{ $todaySchedules->count() - 4 }} jadwal lainnya
                            </a>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Computer Stats -->
            <div class="card bg-base-100 shadow-lg mt-6">
                <div class="card-body">
                    <h2 class="card-title text-lg font-bold mb-4">Statistik Komputer</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center p-3 bg-indigo-50 rounded-lg">
                                <div class="text-xl font-bold text-indigo-600">{{ $computerStats['avg_ram'] }} GB</div>
                                <div class="text-xs text-indigo-700 mt-1">Rata-rata RAM</div>
                            </div>
                            <div class="text-center p-3 bg-indigo-50 rounded-lg">
                                <div class="text-xl font-bold text-indigo-600">{{ $computerStats['avg_storage'] }} GB</div>
                                <div class="text-xs text-indigo-700 mt-1">Rata-rata Storage</div>
                            </div>
                        </div>

                        @if(!empty($computerStats['os_distribution']))
                        <div>
                            <h3 class="font-semibold text-sm mb-2">Distribusi OS</h3>
                            <div class="space-y-2">
                                @foreach($computerStats['os_distribution'] as $os => $count)
                                <div class="flex justify-between items-center">
                                    <span class="text-sm">{{ ucfirst($os) }}</span>
                                    <span class="badge badge-sm">{{ $count }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Upcoming Schedules & Quick Links -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upcoming Schedules -->
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="card-title text-lg font-bold">Jadwal Mendatang (7 Hari)</h2>
                        <a href="{{ route('schedule.index') }}" class="link link-primary text-sm">
                            Lihat Semua
                        </a>
                    </div>

                    @if($upcomingSchedules->isEmpty())
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-alt text-3xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Tidak ada jadwal mendatang</p>
                    </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="table table-zebra">
                            <thead>
                                <tr>
                                    <th>Hari/Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Mata Kuliah</th>
                                    <th>Ruangan</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingSchedules as $schedule)
                                <tr>
                                    <td>
                                        <div class="font-medium">{{ $schedule->start_date->translatedFormat('D') }}</div>
                                        <div class="text-sm text-gray-500">{{ $schedule->start_date->format('d/m/Y') }}</div>
                                    </td>
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

        {{-- <!-- Quick Links -->
        {{-- <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h2 class="card-title text-lg font-bold mb-4">Aksi Cepat</h2>
                    <div class="space-y-3">
                        <a href="{{ route('schedules.create') }}" class="btn btn-primary btn-block">
                            <i class="fas fa-plus mr-2"></i>
                            Buat Jadwal Baru
                        </a>
                        <a href="{{ route('rooms.create') }}" class="btn btn-outline btn-block">
                            <i class="fas fa-door-closed mr-2"></i>
                            Tambah Ruangan
                        </a>
                        <a href="{{ route('computers.create') }}" class="btn btn-outline btn-block">
                            <i class="fas fa-desktop mr-2"></i>
                            Tambah Komputer
                        </a>
                        <a href="{{ route('courses.create') }}" class="btn btn-outline btn-block">
                            <i class="fas fa-book mr-2"></i>
                            Tambah Mata Kuliah
                        </a>
                        <a href="{{ route('students.create') }}" class="btn btn-outline btn-block">
                            <i class="fas fa-user-plus mr-2"></i>
                            Tambah Mahasiswa
                        </a>
                        <a href="{{ route('lecturers.create') }}" class="btn btn-outline btn-block">
                            <i class="fas fa-user-tie mr-2"></i>
                            Tambah Dosen
                        </a>
                    </div>

                    <div class="divider">Laporan</div>

                    <div class="grid grid-cols-2 gap-2">
                        <a href="#" class="btn btn-sm btn-ghost">
                            <i class="fas fa-file-pdf mr-1"></i>
                            PDF
                        </a>
                        <a href="#" class="btn btn-sm btn-ghost">
                            <i class="fas fa-file-excel mr-1"></i>
                            Excel
                        </a>
                        <a href="#" class="btn btn-sm btn-ghost">
                            <i class="fas fa-chart-bar mr-1"></i>
                            Statistik
                        </a>
                        <a href="#" class="btn btn-sm btn-ghost">
                            <i class="fas fa-print mr-1"></i>
                            Cetak
                        </a>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
    @endif

    <!-- Livewire Scripts -->
    <script>
        document.addEventListener('livewire:init', () => {
            // Auto-refresh setiap 5 menit
            setInterval(() => {
                Livewire.dispatch('refresh');
            }, 300000);
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

        .stats-card {
            min-height: 120px;
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 640px) {
            .stats-grid {
                grid-template-columns: 1fr !important;
            }
        }
    </style>
</div>

