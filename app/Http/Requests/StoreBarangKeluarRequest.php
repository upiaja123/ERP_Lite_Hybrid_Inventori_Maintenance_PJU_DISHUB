<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangKeluarRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isSuperAdmin() || $user->hasRole(['admin gudang']) || $user->hasPermissionTo('inventory.create'));
    }

    public function rules(): array
    {
        return [
            'tanggal_keluar' => 'required|date',
            'nama_barang'    => 'required_without:barang_id|string',
            'barang_id'      => 'nullable|exists:barangs,id',
            'customer_id'    => 'required|exists:customers,id',
            'jumlah_keluar'  => 'required|numeric|min:1',
            'kode_transaksi' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_keluar.required' => 'Tanggal keluar wajib diisi!',
            'tanggal_keluar.date'     => 'Format tanggal tidak valid!',
            'nama_barang.required_without' => 'Nama barang wajib diisi!',
            'customer_id.required'    => 'Pilih customer/penerima!',
            'customer_id.exists'      => 'Customer tidak ditemukan!',
            'jumlah_keluar.required'  => 'Jumlah keluar wajib diisi!',
            'jumlah_keluar.numeric'   => 'Jumlah harus berupa angka!',
            'jumlah_keluar.min'       => 'Jumlah minimal 1!',
        ];
    }
}
