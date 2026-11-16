<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Admin (Pemilik Usaha - Bang Rico)
        User::create([
            'name' => 'Bang Rico (Admin)',
            'email' => 'admin@panthera.com',
            'role' => 'admin',
            'password' => Hash::make('password123'),
        ]);

        // Akun Pegawai (Tim Produksi)
        User::create([
            'name' => 'Staf Produksi Panthera',
            'email' => 'pegawai@panthera.com',
            'role' => 'pegawai',
            'password' => Hash::make('password123'),
        ]);

        // Katalog Produk Panthera (Stok Mandiri & Jakarta)
        Product::insert([
            [
                'name' => 'Kaos Custom Sablon Plastisol',
                'material' => 'Cotton Combed 24s / 30s',
                'stock_source' => 'mandiri',
                'base_price' => 85000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kemeja PDH / Kerja Custom',
                'material' => 'American Drill',
                'stock_source' => 'jakarta',
                'base_price' => 145000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Jaket Varsity / Coach Custom',
                'material' => 'Fleece / Taslan',
                'stock_source' => 'jakarta',
                'base_price' => 210000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Topi & Armband Custom',
                'material' => 'Rafel / Canvas',
                'stock_source' => 'mandiri',
                'base_price' => 45000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // Contoh Data Pelanggan Awal
        Customer::create([
            'name' => 'Himpunan Mahasiswa Informatika UNJA',
            'whatsapp_number' => '6281234567890',
            'address' => 'Mendalo Darat, Jambi',
            'notes' => 'Pesanan rutin baju kepanitiaan',
        ]);
    }
}