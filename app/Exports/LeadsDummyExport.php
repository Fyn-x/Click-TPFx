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
        for ($i = 1; $i <= 5; $i++) {
            $t = ['tes' . $i, '6220240400' . $i . '@example.com', '6220240400' . $i, 'Google'];
            array_push($temp, $t);
        }

        $test = collect($temp);
        return $test;
    }
}
