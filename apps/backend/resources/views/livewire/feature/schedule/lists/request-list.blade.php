<div>
    <div class="mb-4">
        <div class="tabs tabs-box">
            <button wire:click="$set('tab','masuk')" class="tab w-1/3 font-semibold {{ $tab === 'masuk' ? 'tab-active bg-primary text-white' : '' }}">Masuk</button>
            <button wire:click="$set('tab','disetujui')" class="tab w-1/3 font-semibold {{ $tab === 'disetujui' ? 'tab-active bg-primary text-white' : '' }}">Disetujui</button>
            <button wire:click="$set('tab','ditolak')" class="tab w-1/3 font-semibold {{ $tab === 'ditolak' ? 'tab-active bg-primary text-white' : '' }}">Ditolak</button>
        </div>
    </div>

    <div>
        <x-table.container>
            <x-table.thead>
                <tr>
                    <th>Tanggal Request</th>
                    <th>Mata Kuliah / Keperluan</th>
                    <th>Dosen</th>
                    <th>Jadwal (count)</th>
                    {{-- <th>Status</th> --}}
                    <th>Aksi</th>
                </tr>
            </x-table.thead>

            <tbody>
                @forelse($requests as $req)
                    @php
                        $se = $req->status;
                        $badgeClass = $se === \App\Enums\ScheduleRequest\StatusEnum::PENDING ? 'badge-warning' : ($se === \App\Enums\ScheduleRequest\StatusEnum::APPROVED ? 'badge-success' : 'badge-error');
                    @endphp
                    <tr>
                        <td>{{ optional($req->created_at)->format('d F Y - H:i') }}</td>
                        <td>{{ $req->schedules[0]?->course->name ?? $req->category->label() }}</td>
                        <td>{{ $req->lecturer?->user->name ?? '-' }}</td>
                        <td>{{ $req->schedules?->count() ?? '-' }}</td>
                        {{-- <td>
                            <span class="badge {{ $badgeClass }}">{{ $se->label() ?? $se->name }}</span>
                        </td> --}}
                        <td>
                            <x-table.action-menu
                                :id="$req->id"
                                :actions="array_filter([
                                    [
                                        'action' => 'showSchedules',
                                        'label' => 'Lihat Jadwal',
                                        'icon' => 'calendar',
                                        'class' => 'text-info'
                                    ],

                                    $se === \App\Enums\ScheduleRequest\StatusEnum::PENDING
                                        ? [
                                            'action' => 'approve',
                                            'label' => 'Setujui',
                                            'icon' => 'check',
                                            'class' => 'text-success',
                                            'confirm' => 'Apakah kamu yakin ingin menyetujui request jadwal ini?'
                                        ]
                                        : null,

                                    $se === \App\Enums\ScheduleRequest\StatusEnum::PENDING
                                        ? [
                                            'action' => 'reject',
                                            'label' => 'Tolak',
                                            'icon' => 'x-circle',
                                            'class' => 'text-error',
                                            'confirm' => 'Apakah kamu yakin ingin menolak request jadwal ini?'
                                        ]
                                        : null,

                                    $se !== \App\Enums\ScheduleRequest\StatusEnum::PENDING
                                        ? [
                                            'action' => 'cancel',
                                            'label' => 'Batalkan',
                                            'icon' => 'arrow-path',
                                            'class' => 'text-warning',
                                            'confirm' => 'Apakah kamu yakin ingin membatalkan request jadwal ini?'
                                        ]
                                        : null,
                                ])"
                            />
                        </td>

                    </tr>
                @empty
                    <x-table.empty-state :colspan="7" message="Belum ada request untuk kategori ini." />
                @endforelse
            </tbody>
        </x-table.container>

        <div class="mt-4 px-4">
            {{ $requests->links() }}
        </div>
    </div>
    {{-- <x-table.wrapper class="p-4"> --}}
    {{-- </x-table.wrapper> --}}

    <dialog class="modal" {{ $showSchedulesModal ? 'open' : '' }}>
        <div class="modal-box max-w-3xl">
            <h3 class="font-bold text-lg mb-4">
                Daftar Jadwal
            </h3>

            <div class="overflow-x-auto">
                <table class="table table-sm table-zebra">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Waktu</th>
                            <th>Ruangan</th>
                            <th>Mata Kuliah</th>
                            {{-- <th>Status</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($selectedSchedules as $sch)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($sch->start_date)->format('d F Y') }}</td>
                                <td>{{ $sch->time?->label() ?? '-' }}</td>
                                <td>
                                    {{ $sch->rooms?->pluck('name')->implode(', ') ?? '-' }}
                                </td>
                                <td>{{ $sch->course?->name ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-outline">
                                        {{ $sch->status?->label() ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-gray-500">
                                    Tidak ada jadwal
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="modal-action">
                <button wire:click="closeSchedulesModal" class="btn btn-sm">
                    Tutup
                </button>
            </div>
        </div>

        {{-- backdrop --}}
        <form method="dialog" class="modal-backdrop">
            <button wire:click="closeSchedulesModal">close</button>
        </form>
    </dialog>

</div>

