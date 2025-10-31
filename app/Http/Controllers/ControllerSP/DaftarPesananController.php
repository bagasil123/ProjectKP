<?php

namespace App\Http\Controllers\ControllerSP;

use App\Http\Controllers\Controller;
use App\Models\SPModels\CustomerOrder;
use App\Models\SPModels\Pelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class DaftarPesananController extends Controller
{
    /**
     * Display a listing of the resource along with data for the creation form.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Eager load relationships for efficiency.
        $customerOrders = CustomerOrder::with(['pelanggan', 'details'])
            ->orderBy('tanggal_pesan', 'desc')
            ->get();

        // Get all active customers for the dropdown list.
        $pelanggans = Pelanggan::where('status', 'Aktif')->orderBy('anggota')->get();

        // Return the main view with all necessary data.
        return view('SistemPenjualan.Customerorder', compact('customerOrders', 'pelanggans'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the incoming request data.
        $validated = $request->validate([
            'pelanggan_id' => 'required|exists:daftarpelanggan,id',
            'po_pelanggan' => 'nullable|string|max:255',
            'tgl_kirim' => 'nullable|date',
            'bruto' => 'required|numeric|min:0',
            'disc' => 'nullable|numeric|min:0|max:100',
            'pajak' => 'nullable|numeric|min:0',
            'netto' => 'required|numeric|min:0',
            'tanggal_pesan' => 'required|date',
            'status' => 'nullable|in:Draft,Dikirim,Selesai,Batal',
        ]);

        // Generate a unique order number (no_order).
        $currentYear = date('Y');
        $currentMonth = date('m');
        $prefix = 'CO-' . $currentYear . $currentMonth . '-';

        $lastOrder = CustomerOrder::where('no_order', 'like', $prefix . '%')->latest('id')->first();
        $sequence = $lastOrder ? (int)substr($lastOrder->no_order, -5) + 1 : 1;
        $no_order = $prefix . str_pad($sequence, 5, '0', STR_PAD_LEFT);

        // Add generated and user data to the validated array.
        $validated['no_order'] = $no_order;
        $validated['pengguna'] = Auth::user()->name; // Get user from Auth facade

        // Create the customer order.
        $order = CustomerOrder::create($validated);

        // Return a success response with the created data.
        return response()->json([
            'success' => true,
            'message' => 'Pesanan pelanggan berhasil ditambahkan.',
            'data' => $order
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SPModels\CustomerOrder  $customer_order
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, CustomerOrder $customer_order) // Using Route-Model Binding
    {
        // Validate the incoming request data.
        $validated = $request->validate([
            'pelanggan_id' => 'required|exists:daftarpelanggan,id',
            'po_pelanggan' => 'nullable|string|max:255',
            'tgl_kirim' => 'nullable|date',
            'bruto' => 'required|numeric|min:0',
            'disc' => 'nullable|numeric|min:0|max:100',
            'pajak' => 'nullable|numeric|min:0',
            'netto' => 'required|numeric|min:0',
            'tanggal_pesan' => 'required|date',
            'status' => 'nullable|in:Draft,Dikirim,Selesai,Batal',
        ]);
        
        $validated['pengguna'] = Auth::user()->name;

        // Update the model instance.
        $customer_order->update($validated);

        // Return a success response with the updated data.
        return response()->json([
            'success' => true,
            'message' => 'Pesanan pelanggan berhasil diperbarui.',
            'data' => $customer_order
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SPModels\CustomerOrder  $customer_order
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(CustomerOrder $customer_order) // Using Route-Model Binding
    {
        $no_order = $customer_order->no_order;
        $customer_order->delete(); // Deletes the order and its details via model events if configured.

        return response()->json([
            'success' => true,
            'message' => "Pesanan $no_order berhasil dihapus."
        ]);
    }
}
