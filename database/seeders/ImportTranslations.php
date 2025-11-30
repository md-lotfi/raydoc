<?php

namespace Database\Seeders;

use App\Models\LtmTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ImportTranslations extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonContent = Storage::get('translatable.json');

        // Decode into an associative array
        $data = json_decode($jsonContent, true);

        // Loop through and insert/update keys
        foreach ($data as $key => $value) {

            // Create or update existing record
            LtmTranslations::updateOrCreate(
                ['key' => $key],
                ['key' => $key] // or any other fields you want to fill
            );
        }
    }
}
