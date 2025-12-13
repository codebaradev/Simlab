<div>
    <div class="mb-4">
        <div class="tabs tabs-box">
            <button wire:click="$set('tab','masuk')" class="tab w-1/3 font-semibold {{ $tab === 'masuk' ? 'tab-active bg-primary text-white' : '' }}">Masuk</button>
            <button wire:click="$set('tab','disetujui')" class="tab w-1/3 font-semibold {{ $tab === 'disetujui' ? 'tab-active bg-primary text-white' : '' }}">Disetujui</button>
            <button wire:click="$set('tab','ditolak')" class="tab w-1/3 font-semibold {{ $tab === 'ditolak' ? 'tab-active bg-primary text-white' : '' }}">Ditolak</button>
        </div>
    </div>

    <x-table.wrapper class="p-4">
        <x-table.container>
            <x-table.thead>
                <tr>
                    <th>#</th>
                    <th>Tanggal Request</th>
                    <th>Mata Kuliah / Keperluan</th>
                    <th>Dosen</th>
                    <th>Jadwal (count)</th>
                    <th>Status</th>
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
                        <td>{{ $req->id }}</td>
                        <td>{{ optional($req->created_at)->format('Y-m-d H:i') }}</td>
                        <td>{{ $req->course?->name ?? ($req->category_label ?? $req->category) }}</td>
                        <td>{{ $req->lecturer?->name ?? '-' }}</td>
                        <td>{{ $req->schedules?->count() ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $badgeClass }}">{{ $se->label() ?? $se->name }}</span>
                        </td>
                        <td class="flex gap-2">
                            @php $statusVal = $req->status; @endphp
                            @if($statusVal === \App\Enums\ScheduleRequest\StatusEnum::PENDING)
                                <button wire:click="approve({{ $req->id }})" class="btn btn-success btn-sm">Setujui</button>
                                <button wire:click="reject({{ $req->id }})" class="btn btn-error btn-sm">Tolak</button>
                            @else
                                <button wire:click="cancel({{ $req->id }})" class="btn btn-warning btn-sm">Batalkan</button>
                            @endif
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
    </x-table.wrapper>
</div>

