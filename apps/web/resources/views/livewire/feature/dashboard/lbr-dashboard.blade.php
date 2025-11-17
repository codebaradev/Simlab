<!-- Main Content -->
<div class="p-8">
    <x-page.breadcrumbs
        :items="[
            ['label' => 'Dashboard'],
        ]"
    />

    <x-page.title title="Dashboard"/>

    <!-- Content based on active menu -->
    <div class="bg-white rounded-lg shadow p-6">
        <div x-show="activeMenu === 'dashboard'">
            <h2 class="text-xl font-semibold mb-4">Dashboard Overview</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="stats shadow">
                    <div class="stat">
                        <div class="stat-figure text-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        </div>
                        <div class="stat-title">Total Mahasiswa</div>
                        <div class="stat-value text-primary">1,248</div>
                        <div class="stat-desc">21% lebih banyak dari bulan lalu</div>
                    </div>
                </div>

                <div class="stats shadow">
                    <div class="stat">
                        <div class="stat-figure text-secondary">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <div class="stat-title">Total Dosen</div>
                        <div class="stat-value text-secondary">86</div>
                        <div class="stat-desc">14% lebih banyak dari bulan lalu</div>
                    </div>
                </div>

                <div class="stats shadow">
                    <div class="stat">
                        <div class="stat-figure text-accent">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                        </div>
                        <div class="stat-title">Total Ruangan</div>
                        <div class="stat-value text-accent">42</div>
                        <div class="stat-desc">2 ruangan sedang maintenance</div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeMenu !== 'dashboard'">
            <h2 class="text-xl font-semibold mb-4" x-text="'Manajemen ' + activeMenu.charAt(0).toUpperCase() + activeMenu.slice(1)"></h2>
            <p class="text-gray-600 mb-4">Ini adalah halaman untuk mengelola data <span x-text="activeMenu"></span>.</p>

            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>001</td>
                            <td>Contoh Data</td>
                            <td><span class="badge badge-success">Aktif</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline btn-primary">Edit</button>
                                <button class="btn btn-sm btn-outline btn-error ml-2">Hapus</button>
                            </td>
                        </tr>
                        <tr>
                            <td>002</td>
                            <td>Data Contoh</td>
                            <td><span class="badge badge-warning">Pending</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline btn-primary">Edit</button>
                                <button class="btn btn-sm btn-outline btn-error ml-2">Hapus</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
