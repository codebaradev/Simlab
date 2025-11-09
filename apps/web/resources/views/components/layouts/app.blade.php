<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'Simlab' }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 min-h-screen">
        <div>
            {{-- Sidebar --}}
            <x-partials.sidebar />

            {{-- Header --}}

            {{-- Main --}}
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
    <script>
        function sidebar() {
            return {
                isCollapsed: false,
                activeMenu: 'dashboard',
                toggleSidebar() {
                    this.isCollapsed = !this.isCollapsed;
                },
                setActiveMenu(menu) {
                    this.activeMenu = menu;
                }
            }
        }
    </script>
</html>
