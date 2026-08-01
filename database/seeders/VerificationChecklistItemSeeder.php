<?php

namespace Database\Seeders;

use App\Models\VerificationChecklistItem;
use Illuminate\Database\Seeder;

class VerificationChecklistItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name' => 'Kelengkapan dokumen', 'description' => 'Memastikan seluruh dokumen wajib telah dilampirkan.', 'sort_order' => 10],
            ['name' => 'Kesesuaian jenis dokumen', 'description' => 'Memastikan jenis dokumen sesuai dengan kategori permohonan.', 'sort_order' => 20],
            ['name' => 'Validitas data pemohon', 'description' => 'Memastikan data identitas pemohon dan perusahaan valid.', 'sort_order' => 30],
            ['name' => 'Kesesuaian format dokumen', 'description' => 'Memastikan format dan tipe berkas sesuai ketentuan.', 'sort_order' => 40],
            ['name' => 'Kejelasan isi dokumen', 'description' => 'Memastikan isi dokumen dapat dibaca dan dipahami dengan jelas.', 'sort_order' => 50],
            ['name' => 'Masa berlaku dokumen', 'description' => 'Memastikan dokumen pendukung masih berlaku.', 'sort_order' => 60],
            ['name' => 'Kesesuaian data form dengan dokumen', 'description' => 'Memastikan data pada form selaras dengan isi dokumen.', 'sort_order' => 70],
            ['name' => 'Catatan tambahan penilai', 'description' => 'Ruang pemeriksaan tambahan dari penilai jika diperlukan.', 'is_required' => false, 'sort_order' => 80],
        ];

        foreach ($items as $item) {
            VerificationChecklistItem::updateOrCreate(
                ['name' => $item['name']],
                [
                    'description' => $item['description'],
                    'is_required' => $item['is_required'] ?? true,
                    'is_active' => true,
                    'sort_order' => $item['sort_order'],
                ],
            );
        }
    }
}
