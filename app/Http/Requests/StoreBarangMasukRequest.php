<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarangMasukRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isSuperAdmin() || $user->hasRole(['admin gudang']) || $user->hasPermissionTo('inventory.create'));
    }

    public function rules(): array
    {
        return [
            'tanggal_masuk'  => 'required|date',
            'barang_id'      => 'required|exists:barangs,id',
            'jumlah_masuk'   => 'required|numeric|min:1',
            'supplier_id'    => 'required|exists:suppliers,id',
            'no_dokumen'     => 'nullable|string|max:100',
            'kode_transaksi' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_masuk.required' => 'Tanggal masuk wajib diisi!',
            'tanggal_masuk.date'     => 'Format tanggal tidak valid!',
            'barang_id.required'     => 'Pilih barang!',
            'barang_id.exists'       => 'Barang tidak ditemukan!',
            'jumlah_masuk.required'  => 'Jumlah masuk wajib diisi!',
            'jumlah_masuk.numeric'   => 'Jumlah harus berupa angka!',
            'jumlah_masuk.min'       => 'Jumlah minimal 1!',
            'supplier_id.required'   => 'Pilih supplier!',
            'supplier_id.exists'     => 'Supplier tidak ditemukan!',
        ];
    }
}
