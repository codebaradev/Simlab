<div>
    <div class="mb-4">
        <div class="tabs tabs-box">
            @foreach($schedules as $i => $s)
                <button wire:click="$set('selectedIndex', {{ $i }})" class="tab {{ $selectedSchedule && $selectedSchedule->id === $s->id ? 'tab-active bg-primary text-white' : '' }}">
                    Pertemuan {{ $i + 1 }} <span class="text-xs block">{{ optional($s->start_date)->format('Y-m-d') }}</span>
                </button>
            @endforeach
            @if($schedules->isEmpty())
                <div class="text-sm text-gray-500">Belum ada pertemuan untuk course ini.</div>
            @endif
        </div>
    </div>

    <x-table.wrapper>
        <x-table.container>
            <x-table.thead>
                <tr>
                    <th>#</th>
                    <th>Mahasiswa</th>
                    <th>NIM</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </x-table.thead>

            <tbody>
                @forelse($attendances as $att)
                    <tr>
                        <td>{{ $att->id }}</td>
                        <td>{{ $att->user?->name ?? '-' }}</td>
                        <td>{{ $att->user?->nim ?? '-' }}</td>
                        <td>{{ optional($att->status)->label() ?? $att->status }}</td>
                        <td>
                            <button wire:click="$emit('view-attendance', {{ $att->id }})" class="btn btn-ghost btn-sm">Detail</button>
                        </td>
                    </tr>
                @empty
                    <x-table.empty-state :colspan="5" message="Belum ada attendance untuk pertemuan ini." />
                @endforelse
            </tbody>
        </x-table.container>

        <div class="mt-4 px-4">
            {{ $attendances->links() }}
        </div>
    </x-table.wrapper>
</div>
