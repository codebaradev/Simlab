@php
    use App\Enums\UserRoleEnum;

    $user = auth()->user();
    $role = $user->roles()->first()->name ?? 'mahasiswa';
@endphp

<div
    class="fixed sidebar-transition z-10 bg-white shadow-lg h-full flex flex-col"
    :class="isCollapsed ? 'w-20' : 'w-64'"
>
    <!-- Logo Section -->
    <div class="p-4 border-b border-gray-200 flex items-center justify-between">
        <div class="flex items-center space-x-2" x-show="!isCollapsed">
            <x-partials.app-logo class="size-8 flex"/>
            <h1 class="text-xl font-bold text-primary">Simlab</h1>
        </div>
        <button
            @click="toggleSidebar()"
            class="btn btn-ghost btn-sm rounded-full"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 p-4 overflow-y-auto">
        <ul class="space-y-2">
            <x-partials.sidebar-menu name="Dashboard" url="/dashboard" selected="dashboard" />
            {{-- <x-partials.sidebar-menu name="Notifikasi" url="/notifikasi" selected="notifikasi" icon="book"/> --}}
            <x-partials.sidebar-menu name="Jadwal" url="/jadwal" selected="jadwal" icon="calendar"/>
            <x-partials.sidebar-menu name="Matakuliah" url="/matakuliah" selected="matakuliah" icon="book"/>

            @if ($role === UserRoleEnum::LABORAN->label() || $role === UserRoleEnum::LAB_HEAD->label())
                <x-partials.sidebar-menu name="Mahasiswa" url="/mahasiswa" selected="mahasiswa" icon="users"/>
                <x-partials.sidebar-menu name="Dosen" url="/dosen" selected="dosen" icon="user"/>
                <x-partials.sidebar-menu name="Ruangan" url="/ruangan" selected="ruangan" icon="computer"/>
                <x-partials.sidebar-menu name="Jurusan" url="/jurusan" selected="jurusan" icon="office"/>
                <x-partials.sidebar-menu name="Prodi" url="/prodi" selected="prodi" icon="puzzle"/>
                {{-- <x-partials.sidebar-menu name="Fingerprint" url="/finger-print" selected="finger-print" icon="finger-print"/> --}}
            @endif
        </ul>
    </nav>

    <!-- User Profile Section -->
    <div class="p-4 border-t border-gray-200">
        <!-- Expanded View -->
        <div class="dropdown dropdown-right dropdown-end w-full" x-show="!isCollapsed">
            <label tabindex="0" class="btn btn-ghost hover:bg-gray-100 rounded-lg p-3 w-full flex items-center space-x-3 cursor-pointer transition-colors justify-between">
                <div class="flex items-center space-x-3 flex-1 min-w-0">
                    <div class="avatar">
                        <div class="w-10 rounded-full bg-base-200 flex justify-center items-center  ">
                            <x-icon.user />
                        </div>
                    </div>
                    <div class="flex-1 min-w-0 text-left">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $user->username }}</p>
                        <span class="text-sm text-gray-400">{{ $role }}</span>
                        {{-- <p class="text-xs text-gray-500 truncate">admin@example.com</p> --}}
                    </div>
                </div>
                <x-icon.chevron-right class="size-4"/>
            </label>

            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52 mt-2">
                <li>
                    <livewire:shared.buttons.logout-button/>
                </li>
            </ul>
        </div>

        <!-- Collapsed View -->
        <div class="dropdown dropdown-right dropdown-end w-full" x-show="isCollapsed">
            <label tabindex="0" class="btn btn-ghost hover:bg-gray-100 rounded-lg p-2 w-full flex justify-center cursor-pointer transition-colors">
                <div class="avatar">
                    <div class="w-10 rounded-full bg-base-200 flex justify-center items-center  ">
                        <x-icon.user />
                    </div>
                </div>
            </label>

            <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52 mt-2">
                <li class="menu-title">
                    <span>{{ $user->username }}</span>
                    <span class="text-xs text-base-300">{{ $role }}</span>
                </li>
                <li>
                    <livewire:shared.buttons.logout-button/>
                </li>
            </ul>
        </div>
    </div>
</div>


