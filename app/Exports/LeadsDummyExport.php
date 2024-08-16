<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

class LeadsDummyExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $temp = [];
        for ($i = 1; $i <= 200; $i++) {
            $t = ['tes' . $i, '62' . date('Ymds') . $i . '@example.com', '62' . date('Ymds') . $i, 'Google'];
            array_push($temp, $t);
        }

        $test = collect($temp);
        return $test;
    }
}
