<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer', 'product', 'payments'])->latest()->get();

        $stats = [
            'total_orders' => $orders->count(),
            'in_production' => $orders->whereIn('production_status', ['desain_disetujui', 'proses_sablon'])->count(),
            'completed' => $orders->whereIn('production_status', ['selesai', 'diambil'])->count(),
            'total_revenue' => Payment::sum('amount'),
        ];

        return view('orders.index', compact('orders', 'stats'));
    }

    public function create()
    {
        $customers = Customer::latest()->get();
        $products = Product::all();
        return view('orders.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_mode' => 'required|in:existing,new',
            'customer_id' => 'required_if:customer_mode,existing|nullable|exists:customers,id',
            'new_customer_name' => 'required_if:customer_mode,new|nullable|string|max:100',
            'new_whatsapp_number' => 'required_if:customer_mode,new|nullable|string|max:20',
            'new_address' => 'nullable|string|max:255',
            'product_id' => 'required|exists:products,id',
            'size' => 'required|string|max:20',
            'color' => 'required|string|max:50',
            'quantity' => 'required|integer|min:1',
            'dp_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:tunai,transfer',
            'design_file' => 'nullable|image|max:2048',
        ]);

        if ($validated['customer_mode'] === 'new') {
            $customer = Customer::create([
                'name' => $validated['new_customer_name'],
                'whatsapp_number' => $validated['new_whatsapp_number'],
                'address' => $validated['new_address'] ?? null,
            ]);
            $customerId = $customer->id;
        } else {
            $customerId = $validated['customer_id'];
        }

        $product = Product::findOrFail($validated['product_id']);
        $totalPrice = $product->base_price * $validated['quantity'];
        $minDp = 0.5 * $totalPrice;

        if ($validated['dp_amount'] < $minDp) {
            return back()
                ->withInput()
                ->withErrors(['dp_amount' => 'Nominal DP ditolak: minimal 50% dari total harga yaitu Rp ' . number_format($minDp, 0, ',', '.') . '.']);
        }

        $designPath = null;
        if ($request->hasFile('design_file')) {
            $designPath = $request->file('design_file')->store('designs', 'public');
        }

        $paymentStatus = $validated['dp_amount'] >= $totalPrice ? 'lunas' : 'dp_50';

        $order = Order::create([
            'order_code' => 'PNT-' . now()->format('Ym') . '-' . strtoupper(Str::random(4)),
            'customer_id' => $customerId,
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'size' => $validated['size'],
            'color' => $validated['color'],
            'quantity' => $validated['quantity'],
            'total_price' => $totalPrice,
            'design_file' => $designPath,
            'revision_count' => 0,
            'payment_status' => $paymentStatus,
            'production_status' => 'desain_disetujui',
            'order_source' => 'manual_web',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_type' => $paymentStatus === 'lunas' ? 'pelunasan' : 'dp',
            'amount' => $validated['dp_amount'],
            'payment_method' => $validated['payment_method'],
            'payment_date' => now()->toDateString(),
        ]);

        return redirect()->route('orders.index')->with('success', 'Pesanan ' . $order->order_code . ' berhasil dicatat & Invoice siap dicetak.');
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'production_status' => 'required|in:konsultasi,desain_disetujui,proses_sablon,selesai,diambil',
        ]);

        $order->update([
            'production_status' => $request->production_status,
        ]);

        return back()->with('success', 'Status produksi ' . $order->order_code . ' diperbarui menjadi ' . strtoupper(str_replace('_', ' ', $request->production_status)) . '.');
    }

    public function incrementRevision(Order $order)
    {
        if ($order->revision_count >= 3) {
            return back()->withErrors(['revision' => 'Batas maksimal 3x revisi gratis sudah tercapai.']);
        }

        $order->increment('revision_count');
        return back()->with('success', 'Revisi desain ke-' . $order->revision_count . ' berhasil dicatat.');
    }

    public function settlePayment(Order $order)
    {
        $paid = $order->payments()->sum('amount');
        $remaining = max(0, $order->total_price - $paid);

        if ($remaining > 0) {
            Payment::create([
                'order_id' => $order->id,
                'payment_type' => 'pelunasan',
                'amount' => $remaining,
                'payment_method' => 'transfer',
                'payment_date' => now()->toDateString(),
            ]);
        }

        $order->update(['payment_status' => 'lunas']);
        return back()->with('success', 'Pelunasan pesanan ' . $order->order_code . ' berhasil dicatat.');
    }

    public function downloadInvoice(Order $order)
    {
        $order->load(['customer', 'product', 'payments', 'user']);
        $pdf = Pdf::loadView('orders.invoice_pdf', compact('order'));
        return $pdf->stream('Invoice-' . $order->order_code . '.pdf');
    }

    public function reports()
    {
        $payments = Payment::with(['order.customer', 'order.product'])->latest()->get();
        $totalIncome = $payments->sum('amount');
        $totalReceivable = Order::sum('total_price') - $totalIncome;

        return view('orders.reports', compact('payments', 'totalIncome', 'totalReceivable'));
    }
}