<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panthera — Custom Apparel Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #09090b; color: #f4f4f5; }
    </style>
</head>
<body class="min-h-screen bg-zinc-950 text-zinc-100 antialiased selection:bg-white selection:text-black">
    <!-- Top Navigation Bar -->
    <nav class="sticky top-0 z-30 border-b border-zinc-800/80 bg-zinc-950/90 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg bg-white text-black font-bold flex items-center justify-center text-sm tracking-tighter">P</span>
                    <span class="font-semibold tracking-tight text-white text-base">PANTHERA <span class="text-zinc-500 font-normal text-xs ml-1">OPS</span></span>
                </a>

                <div class="hidden sm:flex items-center gap-1">
                    <a href="{{ route('orders.index') }}"
                       class="px-3 py-1.5 rounded-md text-sm font-medium transition {{ request()->routeIs('dashboard', 'orders.*') ? 'bg-zinc-900 text-white border border-zinc-800' : 'text-zinc-400 hover:text-white' }}">
                        Pesanan & Produksi
                    </a>
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('reports.index') }}"
                           class="px-3 py-1.5 rounded-md text-sm font-medium transition {{ request()->routeIs('reports.*') ? 'bg-zinc-900 text-white border border-zinc-800' : 'text-zinc-400 hover:text-white' }}">
                            Laporan Keuangan
                        </a>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <div class="text-xs font-medium text-zinc-200">{{ auth()->user()->name }}</div>
                    <div class="text-[11px] uppercase tracking-wider text-zinc-500">{{ auth()->user()->role }}</div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 rounded-md text-xs font-medium bg-zinc-900 hover:bg-zinc-800 text-zinc-300 border border-zinc-800 transition">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>
</body>
</html>