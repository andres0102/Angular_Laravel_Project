<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

// use Maatwebsite\Excel\Imports\HeadingRowFormatter;
// HeadingRowFormatter::default('none');

class PayrollFeedsImport implements WithHeadingRow, WithChunkReading
{
    use Importable;

    public function chunkSize(): int
    {
        return 1000;
    }
}
