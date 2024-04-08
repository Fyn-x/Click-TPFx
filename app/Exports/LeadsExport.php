<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Lead;
use Maatwebsite\Excel\Concerns\WithHeadings;

class LeadsExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */

    private $start, $end;

    public function __construct($start = null, $end = null)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function collection()
    {
        $query = Lead::selectRaw(
            'id, name, CONCAT(LEFT(email, 3),"***", "@", SUBSTRING_INDEX(email, "@", -1)) as email,
            CONCAT(SUBSTRING(phone, 1, 8), "***") as phone,
            source, medium, campaign, created_at'
        );

        if (isset($this->start, $this->end)) {
            $query = $query->where('created_at', '>=', $this->start)->where('created_at', '<=', $this->end);
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'id', 'name', 'email', 'phone', 'source', 'medium', 'campaign', 'created_at'
        ];
    }
}
