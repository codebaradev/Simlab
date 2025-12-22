<div>
    {{-- Tabs Navigation --}}
    <div class="tabs tabs-boxed bg-base-200 p-1 mb-6">
        <a wire:click="switchTab('dashboard')"
           class="tab {{ $activeTab === 'dashboard' ? 'tab-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            Dashboard
        </a>
        <a wire:click="switchTab('schedule')"
           class="tab {{ $activeTab === 'schedule' ? 'tab-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            Jadwal
        </a>
        <a wire:click="switchTab('courses')"
           class="tab {{ $activeTab === 'courses' ? 'tab-active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
            Mata Kuliah
        </a>
    </div>

    {{-- Dashboard Content --}}
    @if($activeTab === 'dashboard')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            {{-- Stats Cards --}}
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center">
                        <div class="bg-primary/10 p-3 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold">{{ $totalCredits }}</h3>
                            <p class="text-sm opacity-70">Total SKS</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center">
                        <div class="bg-secondary/10 p-3 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold">{{ $totalCourses }}</h3>
                            <p class="text-sm opacity-70">Mata Kuliah</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <div class="flex items-center">
                        <div class="bg-accent/10 p-3 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-2xl font-bold">{{ number_format($gpa, 2) }}</h3>
                            <p class="text-sm opacity-70">IPK</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Today's Schedule --}}
        <div class="card bg-base-100 shadow mb-6">
            <div class="card-body">
                <h2 class="card-title">Jadwal Hari Ini</h2>
                <div class="divider my-2"></div>
                <div class="space-y-4">
                    @foreach([
                        ['course' => 'Pemrograman Web', 'time' => '08:00 - 10:30', 'room' => 'Lab. Komputer 1', 'lecturer' => 'Dr. Ahmad, M.Kom.', 'status' => 'ongoing'],
                        ['course' => 'Basis Data', 'time' => '13:00 - 15:30', 'room' => 'Lab. Komputer 2', 'lecturer' => 'Prof. Siti, M.Sc.', 'status' => 'upcoming'],
                    ] as $schedule)
                        <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                            <div class="flex items-center">
                                <div class="avatar placeholder">
                                    <div class="bg-primary text-primary-content rounded-full w-12">
                                        <span>{{ substr($schedule['course'], 0, 1) }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="font-bold">{{ $schedule['course'] }}</h4>
                                    <p class="text-sm opacity-70">{{ $schedule['time'] }} • {{ $schedule['room'] }}</p>
                                    <p class="text-sm">{{ $schedule['lecturer'] }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="badge {{ $schedule['status'] === 'ongoing' ? 'badge-success' : 'badge-info' }}">
                                    {{ $schedule['status'] === 'ongoing' ? 'Sedang Berlangsung' : 'Akan Datang' }}
                                </span>
                                <p class="text-sm mt-2">{{ $schedule['time'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title">Pengingat</h2>
                    <ul class="space-y-3">
                        @foreach(['Tugas Pemrograman Web', 'UTS Basis Data', 'Presentasi Proyek'] as $reminder)
                            <li class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-warning mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                {{ $reminder }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title">Tautan Cepat</h2>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <button wire:click="switchTab('schedule')" class="btn btn-primary btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Jadwal
                        </button>
                        <button wire:click="switchTab('courses')" class="btn btn-secondary btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                            Mata Kuliah
                        </button>
                    </div>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'schedule')
        {{-- Schedule Tab --}}
        <div class="card bg-base-100 shadow">
            <div class="card-body">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="card-title">Jadwal Perkuliahan</h2>
                    <div class="join">
                        <button class="btn join-btn btn-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Minggu Lalu
                        </button>
                        <button class="btn join-btn btn-sm btn-active">Minggu Ini</button>
                        <button class="btn join-btn btn-sm">
                            Minggu Depan
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th>Hari</th>
                                <th>Mata Kuliah</th>
                                <th>Waktu</th>
                                <th>Ruang</th>
                                <th>Dosen</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach([
                                ['day' => 'Senin', 'course' => 'Pemrograman Web', 'time' => '08:00 - 10:30', 'room' => 'Lab. Komputer 1', 'lecturer' => 'Dr. Ahmad, M.Kom.'],
                                ['day' => 'Selasa', 'course' => 'Basis Data', 'time' => '13:00 - 15:30', 'room' => 'Lab. Komputer 2', 'lecturer' => 'Prof. Siti, M.Sc.'],
                                ['day' => 'Rabu', 'course' => 'Jaringan Komputer', 'time' => '10:00 - 12:30', 'room' => 'Lab. Jaringan', 'lecturer' => 'Ir. Budi, M.T.'],
                            ] as $schedule)
                                <tr>
                                    <td>
                                        <div class="font-bold">{{ $schedule['day'] }}</div>
                                    </td>
                                    <td>
                                        <div class="font-bold">{{ $schedule['course'] }}</div>
                                        <div class="text-sm opacity-70">PW123</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-outline">{{ $schedule['time'] }}</span>
                                    </td>
                                    <td>{{ $schedule['room'] }}</td>
                                    <td>{{ $schedule['lecturer'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'courses')
        {{-- Courses Tab --}}
        @if(!$selectedCourse)
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    <h2 class="card-title">Mata Kuliah Saya</h2>
                    <p class="text-sm opacity-70">Semester Genap 2023/2024</p>

                    <div class="divider my-4"></div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach([
                            ['id' => 1, 'code' => 'PW123', 'name' => 'Pemrograman Web', 'credits' => 3, 'lecturer' => 'Dr. Ahmad, M.Kom.', 'attendance' => 85, 'color' => 'badge-primary'],
                            ['id' => 2, 'code' => 'BD456', 'name' => 'Basis Data', 'credits' => 3, 'lecturer' => 'Prof. Siti, M.Sc.', 'attendance' => 92, 'color' => 'badge-success'],
                            ['id' => 3, 'code' => 'JK789', 'name' => 'Jaringan Komputer', 'credits' => 4, 'lecturer' => 'Ir. Budi, M.T.', 'attendance' => 78, 'color' => 'badge-warning'],
                        ] as $course)
                            <div class="card bg-base-100 border border-base-300 hover:border-primary transition-all duration-300">
                                <div class="card-body">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h3 class="card-title text-lg">{{ $course['name'] }}</h3>
                                            <p class="text-sm opacity-70">{{ $course['code'] }}</p>
                                        </div>
                                        <span class="badge {{ $course['color'] }}">{{ $course['credits'] }} SKS</span>
                                    </div>

                                    <div class="space-y-2 mb-4">
                                        <p class="text-sm">
                                            <span class="opacity-70">Dosen:</span> {{ $course['lecturer'] }}
                                        </p>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm opacity-70">Kehadiran</span>
                                            <span class="font-bold {{ $course['attendance'] >= 80 ? 'text-success' : ($course['attendance'] >= 70 ? 'text-warning' : 'text-error') }}">
                                                {{ $course['attendance'] }}%
                                            </span>
                                        </div>
                                        <progress
                                            class="progress {{ $course['attendance'] >= 80 ? 'progress-success' : ($course['attendance'] >= 70 ? 'progress-warning' : 'progress-error') }}"
                                            value="{{ $course['attendance'] }}"
                                            max="100">
                                        </progress>
                                    </div>

                                    <div class="card-actions justify-end">
                                        <button
                                            wire:click="selectCourse({{ $course['id'] }})"
                                            class="btn btn-primary btn-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Lihat Absensi
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            {{-- Attendance Detail --}}
            <div class="card bg-base-100 shadow">
                <div class="card-body">
                    {{-- Breadcrumb --}}
                    <div class="text-sm breadcrumbs mb-4">
                        <ul>
                            <li><button wire:click="backToCourses" class="hover:text-primary">Mata Kuliah</button></li>
                            <li>Absensi</li>
                            <li class="font-bold">Pemrograman Web</li>
                        </ul>
                    </div>

                    {{-- Course Header --}}
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
                        <div>
                            <h2 class="card-title text-2xl">Absensi: Pemrograman Web</h2>
                            <p class="opacity-70">PW123 • Kelas A • Dr. Ahmad, M.Kom.</p>
                        </div>
                        <button wire:click="backToCourses" class="btn btn-ghost btn-sm mt-2 md:mt-0">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            Kembali
                        </button>
                    </div>

                    {{-- Attendance Stats --}}
                    <div class="stats stats-vertical lg:stats-horizontal shadow w-full mb-8">
                        <div class="stat">
                            <div class="stat-figure text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="stat-title">Persentase Kehadiran</div>
                            <div class="stat-value text-primary">85%</div>
                            <div class="stat-desc">20 dari 24 pertemuan</div>
                        </div>

                        <div class="stat">
                            <div class="stat-figure text-success">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div class="stat-title">Hadir</div>
                            <div class="stat-value text-success">20</div>
                            <div class="stat-desc">Pertemuan</div>
                        </div>

                        <div class="stat">
                            <div class="stat-figure text-error">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </div>
                            <div class="stat-title">Tidak Hadir</div>
                            <div class="stat-value text-error">4</div>
                            <div class="stat-desc">2 Ijin, 2 Alpa</div>
                        </div>
                    </div>

                    {{-- Attendance History --}}
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr>
                                    <th>Pertemuan</th>
                                    <th>Tanggal</th>
                                    <th>Topik</th>
                                    <th>Status</th>
                                    <th>Waktu Presensi</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([
                                    ['meeting' => 1, 'date' => '10 Jan 2024', 'topic' => 'Pengenalan Laravel', 'status' => 'Hadir', 'status_class' => 'badge-success', 'time' => '07:55', 'note' => ''],
                                    ['meeting' => 2, 'date' => '17 Jan 2024', 'topic' => 'Livewire Dasar', 'status' => 'Alpa', 'status_class' => 'badge-error', 'time' => '-', 'note' => 'Terlambat > 15 menit'],
                                    ['meeting' => 3, 'date' => '24 Jan 2024', 'topic' => 'Blade Templating', 'status' => 'Hadir', 'status_class' => 'badge-success', 'time' => '08:02', 'note' => ''],
                                    ['meeting' => 4, 'date' => '31 Jan 2024', 'topic' => 'Database & Eloquent', 'status' => 'Ijin', 'status_class' => 'badge-warning', 'time' => '-', 'note' => 'Surat izin terlampir'],
                                ] as $attendance)
                                    <tr>
                                        <th>#{{ $attendance['meeting'] }}</th>
                                        <td>{{ $attendance['date'] }}</td>
                                        <td>{{ $attendance['topic'] }}</td>
                                        <td>
                                            <span class="badge {{ $attendance['status_class'] }} gap-1">
                                                {{ $attendance['status'] }}
                                            </span>
                                        </td>
                                        <td>{{ $attendance['time'] }}</td>
                                        <td class="max-w-xs truncate">{{ $attendance['note'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Attendance Filter --}}
                    <div class="flex flex-wrap gap-2 mt-6">
                        <button class="btn btn-sm btn-active">Semua</button>
                        <button class="btn btn-sm btn-success btn-outline">Hadir</button>
                        <button class="btn btn-sm btn-warning btn-outline">Ijin</button>
                        <button class="btn btn-sm btn-error btn-outline">Alpa</button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
