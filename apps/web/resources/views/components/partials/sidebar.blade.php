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
            <x-partials.sidebar-menu name="Mahasiswa" url="/mahasiswa" selected="mahasiswa" icon="users"/>
            <x-partials.sidebar-menu name="Dosen" url="/dosen" selected="dosen" icon="user"/>
            <x-partials.sidebar-menu name="Jurusan" url="/jurusan" selected="jurusan" icon="office"/>
            <x-partials.sidebar-menu name="Prodi" url="/prodi" selected="prodi" />
            <x-partials.sidebar-menu name="Ruangan" url="/ruangan" selected="ruangan" icon="computer"/>
        </ul>
    </nav>

    <!-- User Profile Section -->
    <div class="p-4 border-t border-gray-200">
        <div class="flex items-center space-x-3" x-show="!isCollapsed">
            <div class="avatar">
                <div class="w-10 rounded-full">
                    <img src="https://i.pravatar.cc/150?img=32" alt="User Avatar">
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">Admin Sistem</p>
                <p class="text-xs text-gray-500 truncate">admin@example.com</p>
            </div>
        </div>

        <div class="flex justify-center mt-4" x-show="isCollapsed">
            <div class="avatar">
                <div class="w-10 rounded-full">
                    <img src="https://i.pravatar.cc/150?img=32" alt="User Avatar">
                </div>
            </div>
        </div>
    </div>
</div>


