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

    public function __construct($start = null, $end = null, $search = '')
    {
        $this->start = $start;
        $this->end = $end;
        $this->search = $search;
    }

    public function collection()
    {
        // $query = Lead::selectRaw(
        //     'id, name, CONCAT(LEFT(email, 3),"***", "@", SUBSTRING_INDEX(email, "@", -1)) as email,
        //     CONCAT(SUBSTRING(phone, 1, 8), "***") as phone,
        //     source, medium, campaign, created_at'
        // );

        $query = Lead::selectRaw(
            'id, name, email,
            phone,
            source, medium, campaign, created_at'
        );

        if (isset($this->start, $this->end, $this->search)) {
            $query = $query->where('created_at', '>=', $this->start)->where('created_at', '<=', $this->end)
            ->where(function($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('phone', 'like', "%{$this->search}%")
                  ->orWhere('source', 'like', "%{$this->search}%")
                  ->orWhere('medium', 'like', "%{$this->search}%")
                  ->orWhere('campaign', 'like', "%{$this->search}%");
            })
            ->latest();;
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
