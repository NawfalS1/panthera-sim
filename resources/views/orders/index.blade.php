@extends('layouts.panthera')

@section('content')
<div class="space-y-8">
    <!-- Header & Primary Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-white">Dashboard Operasional</h1>
            <p class="text-sm text-zinc-400 mt-1">Pantau pesanan custom apparel, revisi desain, dan progres produksi Panthera.</p>
        </div>

        @if(auth()->user()->role === 'admin')
            <div class="flex items-center gap-3">
                <a href="{{ route('reports.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium bg-zinc-900 hover:bg-zinc-800 text-zinc-200 border border-zinc-800 transition">
                    Laporan Keuangan
                </a>
                <a href="{{ route('orders.create') }}" class="px-4 py-2.5 rounded-lg text-sm font-semibold bg-white hover:bg-zinc-200 text-black transition shadow-sm">
                    + Catat Pesanan Baru
                </a>
            </div>
        @endif
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-lg bg-emerald-950/60 border border-emerald-800/80 text-emerald-300 text-sm flex items-center justify-between">
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white text-xs">Tutup</button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-lg bg-red-950/60 border border-red-800/80 text-red-300 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 rounded-xl bg-zinc-900/70 border border-zinc-800/90">
            <div class="text-xs font-medium uppercase tracking-wider text-zinc-500">Total Pesanan</div>
            <div class="text-3xl font-semibold text-white mt-2">{{ $stats['total_orders'] }}</div>
        </div>
        <div class="p-5 rounded-xl bg-zinc-900/70 border border-zinc-800/90">
            <div class="text-xs font-medium uppercase tracking-wider text-zinc-500">Dalam Produksi</div>
            <div class="text-3xl font-semibold text-amber-400 mt-2">{{ $stats['in_production'] }}</div>
        </div>
        <div class="p-5 rounded-xl bg-zinc-900/70 border border-zinc-800/90">
            <div class="text-xs font-medium uppercase tracking-wider text-zinc-500">Selesai / Diambil</div>
            <div class="text-3xl font-semibold text-emerald-400 mt-2">{{ $stats['completed'] }}</div>
        </div>
        @if(auth()->user()->role === 'admin')
            <div class="p-5 rounded-xl bg-zinc-900/70 border border-zinc-800/90">
                <div class="text-xs font-medium uppercase tracking-wider text-zinc-500">Total Kas Masuk</div>
                <div class="text-2xl font-semibold text-white mt-2">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
            </div>
        @endif
    </div>

    <!-- Orders Table Card -->
    <div class="rounded-xl bg-zinc-900/70 border border-zinc-800/90 overflow-hidden">
        <div class="p-5 border-b border-zinc-800/90 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="text-base font-semibold text-white">Daftar Pesanan & Pemantauan Produksi</h2>
            <input type="text" id="searchOrder" onkeyup="filterTable()" placeholder="Cari kode atau pelanggan..."
                   class="px-3.5 py-2 rounded-lg bg-zinc-950 border border-zinc-800 text-sm text-zinc-200 placeholder-zinc-500 focus:outline-none focus:border-zinc-600 w-full sm:w-64">
        </div>

        <div class="overflow-x-auto">
            <table id="ordersTable" class="min-w-full divide-y divide-zinc-800/90 text-sm">
                <thead class="bg-zinc-950/60 text-zinc-400 text-xs uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3.5 text-left font-medium">Kode</th>
                        <th class="px-5 py-3.5 text-left font-medium">Pelanggan</th>
                        <th class="px-5 py-3.5 text-left font-medium">Produk & Spesifikasi</th>
                        <th class="px-5 py-3.5 text-left font-medium">Pembayaran</th>
                        <th class="px-5 py-3.5 text-left font-medium">Revisi</th>
                        <th class="px-5 py-3.5 text-left font-medium">Status Produksi</th>
                        <th class="px-5 py-3.5 text-right font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/70">
                    @if($orders->count() > 0)
                        @foreach($orders as $order)
                        <tr class="hover:bg-zinc-800/40 transition">
                            <td class="px-5 py-4 font-mono text-xs font-semibold text-white whitespace-nowrap">
                                {{ $order->order_code }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-zinc-100">{{ $order->customer->name }}</div>
                                <div class="text-xs text-zinc-500">{{ $order->customer->whatsapp_number }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-zinc-200">{{ $order->product->name }} <span class="text-zinc-400">({{ $order->quantity }} pcs)</span></div>
                                <div class="text-xs text-zinc-500 mt-0.5">
                                    Size {{ $order->size }} &bull; {{ $order->color }} &bull; Stok {{ strtoupper($order->product->stock_source) }}
                                </div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="px-2.5 py-0.5 text-[11px] font-semibold rounded-full {{ $order->payment_status === 'lunas' ? 'bg-emerald-950 text-emerald-300 border border-emerald-800' : 'bg-amber-950 text-amber-300 border border-amber-800' }}">
                                    {{ strtoupper(str_replace('_', ' ', $order->payment_status)) }}
                                </span>
                                <div class="text-xs text-zinc-400 mt-1">Rp {{ number_format($order->total_price, 0, ',', '.') }}</div>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="text-zinc-300 font-mono text-xs">{{ $order->revision_count }}/3</span>
                                @if(auth()->user()->role === 'admin' && $order->revision_count < 3)
                                    <form action="{{ route('orders.revision', $order) }}" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="ml-1.5 px-2 py-0.5 text-[11px] rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-200 border border-zinc-700 transition">
                                            +1
                                        </button>
                                    </form>
                                @elseif($order->revision_count >= 3)
                                    <span class="ml-1 text-[10px] text-zinc-500 uppercase">Max</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="production_status" class="text-xs bg-zinc-950 border border-zinc-700 text-zinc-200 rounded-md px-2.5 py-1.5 focus:outline-none focus:border-white">
                                        @foreach(['konsultasi', 'desain_disetujui', 'proses_sablon', 'selesai', 'diambil'] as $status)
                                            <option value="{{ $status }}" {{ $order->production_status === $status ? 'selected' : '' }}>
                                                {{ strtoupper(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-2.5 py-1.5 rounded-md bg-zinc-800 hover:bg-white hover:text-black text-zinc-200 text-xs font-medium border border-zinc-700 transition">
                                        Simpan
                                    </button>
                                </form>
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-right">
                                @if(auth()->user()->role === 'admin')
                                    <div class="inline-flex items-center gap-2">
                                        @if($order->payment_status !== 'lunas')
                                            <form action="{{ route('orders.settle', $order) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-2.5 py-1.5 rounded-md bg-zinc-900 hover:bg-zinc-800 text-zinc-300 border border-zinc-700 text-xs transition">
                                                    Lunasi
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('orders.invoice', $order) }}" target="_blank"
                                           class="px-3 py-1.5 rounded-md bg-white hover:bg-zinc-200 text-black text-xs font-semibold transition">
                                            Invoice PDF
                                        </a>
                                    </div>
                                @else
                                    <span class="text-xs text-zinc-500">Mode Produksi</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center">
                                <div class="text-zinc-400 text-sm">Belum ada pesanan tercatat.</div>
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('orders.create') }}" class="inline-block mt-3 px-4 py-2 rounded-lg bg-white text-black text-xs font-semibold hover:bg-zinc-200 transition">
                                        + Buat Pesanan Pertama
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function filterTable() {
    const query = document.getElementById('searchOrder').value.toLowerCase();
    const rows = document.querySelectorAll('#ordersTable tbody tr');
    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
    });
}
</script>
@endsection