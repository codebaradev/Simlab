<!-- resources/views/livewire/lecturer/dashboard.blade.php -->
<div class="p-8">
    <x-page.header
        class="mb-4"
        title="Dashboard"
        :breadcrumbs="[
            ['label' => 'Dashboard'],
        ]"
    />

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Profile -->
        <div class="lg:col-span-1">
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <h2 class="card-title text-xl font-bold mb-4">Profil Dosen</h2>

                    <!-- Profile Info -->
                    <div class="space-y-4">
                        <!-- Profile Header -->
                        <div class="flex items-center space-x-4">
                            <div class="avatar">
                                <div class="w-16 h-16 rounded-full bg-primary text-primary-content flex items-center justify-center">
                                    <span class="text-2xl font-bold">
                                        {{ substr($lecturer->user->name, 0, 1) }}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold">{{ $lecturer->user->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $lecturer->nip }}</p>
                            </div>
                        </div>

                        <!-- Profile Details -->
                        <div class="divider"></div>

                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">NIDN:</span>
                                <span class="font-semibold">{{ $lecturer->nidn }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">NIP:</span>
                                <span class="font-semibold">{{ $lecturer->nip }}</span>
                            </div>

                            @if($lecturer->study_program)
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Program Studi:</span>
                                <span class="font-semibold text-right">{{ $lecturer->study_program->name }}</span>
                            </div>

                            @if($lecturer->study_program->department)
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Jurusan:</span>
                                <span class="font-semibold">{{ $lecturer->study_program->department->name }}</span>
                            </div>
                            @endif
                            @endif

                            {{-- @if($lecturer->academic_classes->isNotEmpty())
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Kelas:</span>
                                <span class="font-semibold">
                                    {{ $lecturer->academic_classes->first()->name ?? 'Tidak ada kelas' }}
                                </span>
                            </div>
                            @endif --}}

                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">Email:</span>
                                <span class="font-semibold">{{ $lecturer->user->email ?? '-' }}</span>
                            </div>

                            @if($lecturer->user->phone_number)
                            <div class="flex justify-between">
                                <span class="font-medium text-gray-700">No. Telepon:</span>
                                <span class="font-semibold">{{ $lecturer->user->phone_number }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Today's Schedules -->
        <div class="lg:col-span-2">
            <div class="card bg-base-100 shadow-lg">
                <div class="card-body">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="card-title text-xl font-bold">Jadwal Hari Ini</h2>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500">
                                {{ now()->translatedFormat('l, d F Y') }}
                            </span>
                            <button wire:click="refresh" class="btn btn-sm btn-ghost"
                                    wire:loading.attr="disabled" wire:loading.class="loading">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>

                    @if($isLoading)
                    <!-- Loading State -->
                    <div class="flex justify-center items-center py-12">
                        <span class="loading loading-spinner loading-lg text-primary"></span>
                    </div>
                    @elseif($todaySchedules->isEmpty())
                    <!-- No Schedules -->
                    <div class="text-center py-12">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-calendar-times text-5xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-600 mb-2">Tidak ada jadwal hari ini</h3>
                        <p class="text-gray-500">Anda tidak memiliki jadwal kuliah untuk hari ini.</p>
                    </div>
                    @else
                    <!-- Schedules List -->
                    <div class="space-y-4">
                        @foreach($todaySchedules as $schedule)
                        <div class="card bg-base-100 border border-gray-200 hover:border-primary transition-colors">
                            <div class="card-body p-4">
                                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                                    <!-- Left: Course Info -->
                                    <div class="flex-1">
                                        <div class="flex items-start gap-3">
                                            <div class="text-center">
                                                <div class="text-lg font-bold text-primary">
                                                    {{ $this->getFormattedTime($schedule->time) }}
                                                </div>
                                            </div>
                                            <div class="flex gap-4  items-center flex-1 ">
                                                <h4 class="text-lg font-semibold">
                                                    {{ $schedule->course->name ?? 'Mata Kuliah tidak ditemukan' }}
                                                </h4>

                                                @if($schedule->rooms->isNotEmpty())
                                                <div class="mt-2">
                                                    <span class="text-sm text-gray-600 flex items-center gap-1">
                                                        <i class="fas fa-map-marker-alt"></i>
                                                        Ruang:
                                                        {{ $schedule->rooms->pluck('name')->join(', ') }}
                                                    </span>
                                                </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right: Attendance Status -->
                                    <div class="flex flex-col items-end gap-2">
                                        @php
                                            $attendance = $schedule->attendances->first();
                                        @endphp

                                        <span class="badge badge-soft badge-{{ $attendance->status?->status->color() }} gap-2">
                                            {{ $attendance->status ? $attendance->status->label() : 'Belum Hadir' }}
                                        </span>

                                        @if($schedule->attendance_monitoring && $schedule->attendance_monitoring->topic)
                                        <div class="text-right">
                                            <p class="text-sm font-medium">Topik:</p>
                                            <p class="text-sm text-gray-600">{{ $schedule->attendance_monitoring->topic }}</p>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Summary -->
                    {{-- <div class="mt-6 pt-4 border-t border-gray-200">
                        <div class="flex flex-wrap gap-4 justify-center">
                            <div class="stats shadow">
                                <div class="stat">
                                    <div class="stat-title">Total Jadwal</div>
                                    <div class="stat-value text-primary">{{ $todaySchedules->count() }}</div>
                                </div>
                            </div>

                            @php
                                $attendedCount = $todaySchedules->filter(function($schedule) {
                                    return $schedule->attendances->first()?->status->value === 1;
                                })->count();
                            @endphp

                            <div class="stats shadow">
                                <div class="stat">
                                    <div class="stat-title">Sudah Hadir</div>
                                    <div class="stat-value text-success">{{ $attendedCount }}</div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        {{-- <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Total SKS Semester Ini</h3>
                        <p class="text-2xl font-bold">
                            {{ $lecturer->academic_classes->sum('course.sks') ?? 0 }}
                        </p>
                    </div>
                    <div class="text-primary">
                        <i class="fas fa-book text-3xl"></i>
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Status Akademik</h3>
                        <p class="text-2xl font-bold">
                            {{ $lecturer->user->status === 1 ? 'Aktif' : 'Non-Aktif' }}
                        </p>
                    </div>
                    <div class="text-success">
                        <i class="fas fa-user-graduate text-3xl"></i>
                    </div>
                </div>
            </div>
        </div> --}}

        {{-- <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500">Kelas Terdaftar</h3>
                        <p class="text-2xl font-bold">
                            {{ $lecturer->academic_classes->count() }}
                        </p>
                    </div>
                    <div class="text-info">
                        <i class="fas fa-users text-3xl"></i>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>

    <!-- Styles -->
    <style>
        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .badge {
            min-width: 120px;
            justify-content: center;
        }

        @media (max-width: 768px) {
            .stats {
                width: 100%;
            }
        }
    </style>
</div>
