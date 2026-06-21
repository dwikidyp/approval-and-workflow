<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Proposal TA',
            'Seminar Proposal',
            'Seminar Hasil',
            'Surat Aktif Kuliah',
            'Surat Izin Penelitian',
            'Surat Keterangan Lulus',
        ];

        foreach ($types as $type) {
            DocumentType::firstOrCreate([
                'name' => $type,
            ]);
        }
    }
}