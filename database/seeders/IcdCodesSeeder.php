<?php

namespace Database\Seeders;

use App\Models\IcdCode;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class IcdCodesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $json = File::get(database_path('data/icd10.json'));
        $data = json_decode($json);
        foreach ($data as $obj) {
            if (! $obj->desc || ! $obj->code) {
                continue;
            }
            IcdCode::create([
                'code' => $obj->code,
                'description' => $obj->desc,
            ]);
        }
    }
}
