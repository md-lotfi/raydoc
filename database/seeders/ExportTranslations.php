<?php

namespace Database\Seeders;

use App\Models\LtmTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ExportTranslations extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = LtmTranslations::all(['key'])->toArray();

        $transformed = [];

        foreach ($data as $item) {
            $transformed[$item['key']] = $item['key'];
        }

        $jsonData = json_encode($transformed, JSON_PRETTY_PRINT);

        Storage::put('translatable.json', $jsonData);

    }
}
