<div>
    <div class="mb-4">
        <div class="tabs tabs-border">
            @foreach($schedules as $i => $s)
                <button wire:click="$set('selectedIndex', {{ $i }})" class="tab {{ $selectedSchedule && $selectedSchedule->id === $s->id ? 'tab-active' : '' }}">
                    {{ $i + 1 }}
                </button>
            @endforeach
            @if($schedules->isEmpty())
                <div class="text-sm text-gray-500">Belum ada pertemuan untuk course ini.</div>
            @endif
        </div>
    </div>

    @if($selectedSchedule)
        <div class="mb-4">
            <div class="flex gap-2 items-center">
                <h3 class="font-semibold mb-4">
                    {{ $selectedSchedule->formatted_start_date }}
                </h3>

                <span>
                    @if ($selectedSchedule->is_open)
                        <div class="badge badge-soft badge-success">Absensi Terbuka</div>
                    @else
                        <div class="badge badge-soft badge-error">Absensi Tertutup</div>
                    @endif
                </span>
            </div>

            <form wire:submit.prevent="saveMonitoring" class=" space-y-4">
                <div class="gap-4 grid grid-cols-2">
                    <x-form.input name="topic" label="Topik" placeholder="Topik pertemuan" :live="true"/>

                    <x-form.input name="sub_topic" label="Sub Topik" placeholder="Sub topik" :live="true"/>
                </div>
            </form>
        </div>
    @endif


    @if(!$schedules->isEmpty())
        <x-table.wrapper>
            <x-table.container>
                <x-table.thead>
                    <tr>
                        <th>Mahasiswa</th>
                        <th>NIM</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </x-table.thead>

                <tbody>
                    @forelse($attendances as $att)
                        <tr>
                            <td>{{ $att->user?->name ?? '-' }}</td>
                            <td>{{ $att->user?->student->nim ?? '-' }}</td>
                            <td>{{ $att->status ? $att->status->label() : 'Belum Hadir' }}</td>
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
    @endif
</div>
