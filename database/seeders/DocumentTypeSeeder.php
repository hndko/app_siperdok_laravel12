<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'AMDAL',
                'name' => 'Analisis Mengenai Dampak Lingkungan (AMDAL)',
                'description' => 'Dokumen kajian mengenai dampak penting suatu usaha dan/area kegiatan yang direncanakan.',
                'required_files' => ['Dokumen KA-ANDAL', 'Dokumen ANDAL', 'Dokumen RKL-RPL', 'Peta Lokasi'],
                'is_active' => true,
            ],
            [
                'code' => 'UKL-UPL',
                'name' => 'Upaya Pengelolaan Lingkungan dan Upaya Pemantauan Lingkungan',
                'description' => 'Dokumen pengelolaan dan pemantauan lingkungan hidup bagi usaha kegiatan tidak berdampak penting.',
                'required_files' => ['Formulir UKL-UPL', 'Peta Tapak Proyek', 'Surat Pernyataan Kesanggupan'],
                'is_active' => true,
            ],
            [
                'code' => 'SPPL',
                'name' => 'Surat Pernyataan Kesanggupan Pengelolaan dan Pemantauan Lingkungan',
                'description' => 'Pernyataan sanggup mengelola lingkungan hidup untuk usaha mikro dan kecil.',
                'required_files' => ['Formulir SPPL', 'KTP Pemohon', 'NIB/Izin Usaha'],
                'is_active' => true,
            ],
            [
                'code' => 'PERTEK-AIR',
                'name' => 'Persetujuan Teknis Pembuangan Air Limbah',
                'description' => 'Persetujuan teknis untuk baku mutu air limbah bagi kegiatan berisiko.',
                'required_files' => ['Dokumen Pertek', 'Hasil Uji Laboratorium', 'Sistem Pengolahan Air Limbah'],
                'is_active' => true,
            ],
            [
                'code' => 'PERTEK-EMISI',
                'name' => 'Persetujuan Teknis Emisi Udara',
                'description' => 'Persetujuan teknis pengolahan emisi udara industri.',
                'required_files' => ['Desain Cerobong', 'Hasil Pemantauan Udara', 'Spesifikasi Cerobong'],
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            DocumentType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
