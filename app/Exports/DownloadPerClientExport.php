<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class DownloadPerClientExport implements FromCollection, ShouldAutoSize, WithStrictNullComparison
{
    private $clients;

    public function  __construct($clients)
    {
        $this->clients = $clients;
    }

    public function collection()
    {
        return $this->clients;
    }
}
