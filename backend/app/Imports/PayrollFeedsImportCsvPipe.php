<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithChunkReading;

// use Maatwebsite\Excel\Imports\HeadingRowFormatter;
// HeadingRowFormatter::default('none');

class PayrollFeedsImportCsvPipe implements WithHeadingRow, WithCustomCsvSettings, WithChunkReading
{
    use Importable;

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => '|'
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
