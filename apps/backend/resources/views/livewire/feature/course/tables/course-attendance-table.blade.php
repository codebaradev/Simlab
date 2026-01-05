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
            <div class="flex gap-2 items-center justify-between mb-4">
                <div class="flex gap-2 items-center">
                    <h3 class="font-semibold">
                        {{ $selectedSchedule->formatted_start_date }}
                        ({{ $selectedSchedule->time->label() }})
                    </h3>

                    <span>
                        @if ($is_open)
                            <div class="badge badge-soft badge-success">Absensi Terbuka</div>
                        @else
                            <div class="badge badge-soft badge-error">Absensi Tertutup</div>
                        @endif
                    </span>
                </div>

                <x-button :variant="$is_open ? 'error' : null" wire:click="toggleAttendance" target="toggleAttendance">
                    @if ($is_open)
                        <span>Tutup Absensi</span>
                    @else
                        <span>Buka Absensi</span>
                    @endif
                </x-button>
            </div>

            <form wire:submit.prevent="saveMonitoring" class="space-y-4">
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
                        <tr wire:key="attendance-{{ $att->id }}">
                            <td>{{ $att->user?->name ?? '-' }}</td>
                            <td>{{ $att->user?->student->nim ?? '-' }}</td>
                            <td>
                                <span class="badge badge-soft badge-{{ $att->status?->color() }}">
                                    {{ $att->status ? $att->status->label() : 'Belum Hadir' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex gap-2">
                                    {{-- Change Status Button --}}
                                    <button
                                        wire:click="openChangeStatusModal({{ $att->id }})"
                                        class="btn btn-outline btn-sm btn-primary"
                                        title="Ubah Status Kehadiran"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                </div>
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

    @if($showStatusModal)
        <dialog open class="modal modal-bottom sm:modal-middle">
            <div class="modal-box max-w-md" wire:click.stop>

                {{-- Header --}}
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg">
                        Ubah Status Kehadiran
                    </h3>
                    <button
                        type="button"
                        wire:click="closeStatusModal"
                        class="btn btn-sm btn-circle btn-ghost"
                    >
                        ✕
                    </button>
                </div>

                <form wire:submit.prevent="updateAttendanceStatus" class="space-y-4">

                    {{-- Current Student Info --}}
                    @php
                        $attendance = \App\Models\Attendance::with('user')->find($attendanceIdToUpdate);
                    @endphp

                    @if($attendance)
                        <div class="rounded-lg bg-base-200 p-3 text-sm">
                            <p class="font-semibold">
                                {{ $attendance->user->name ?? '-' }}
                            </p>
                            <p>NIM: {{ $attendance->user->student->nim ?? '-' }}</p>
                            <p>
                                Pertemuan:
                                {{ $selectedSchedule->formatted_start_date ?? '-' }}
                            </p>
                        </div>
                    @endif

                    {{-- Status Selection --}}
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text font-medium">
                                Status Kehadiran
                            </span>
                        </label>

                        <x-form.select name="newStatus" :options="$statusOptions" optionValue="value" optionLabel="label"/>
                    </div>

                    {{-- Actions --}}
                    <div class="modal-action">
                        <x-button type="button" variant="outline" wire:click="closeStatusModal">Batal</x-button>
                        <x-button type="submit" leftIcon="check" variant="primary" target="updateAttendanceStatus">Update</x-button>
                    </div>

                </form>
            </div>

            {{-- Backdrop --}}
            <form method="dialog" class="modal-backdrop">
                <button wire:click="closeStatusModal">close</button>
            </form>
        </dialog>
    @endif

    {{-- Notification --}}

    <div>
        <!-- Konten utama Anda -->

        <!-- Notifikasi Popup -->
        @if($showNotification)
            <x-notification-popup
                :notifications="$notifications"
                :showNotification="$showNotification"
            />
        @endif

        <!-- AlpineJS untuk animasi -->
        @push('scripts')
            <script>
                document.addEventListener('livewire:initialized', () => {
                    Livewire.on('show-notification', () => {
                        // Trigger untuk efek tambahan jika diperlukan
                    });
                });
            </script>
        @endpush
    </div>

</div>
