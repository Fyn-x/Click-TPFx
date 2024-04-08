<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use Illuminate\Http\Request;
use App\Exports\LeadsExport;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class LeadsController extends Controller
{
    public function index()
    {
        return view('leads.index');
    }

    public function getLeads(Request $request)
    {
        if ($request->ajax()) {
            $result = Lead::selectRaw('id, name, email,phone, source, medium, campaign, created_at')->latest();

            if ($request->filled('from_date') && $request->filled('to_date')) {
                $result = $result->where('created_at', '>=', $request->from_date)->where('created_at', '<=', $request->to_date);
            }
            return DataTables::of($result)
                ->addIndexColumn()
                ->editColumn('created_at', function ($result) {
                    return $result->created_at; // no formatting, just returned $user->created_at;
                })
                ->make(true);
        }

        return view('leads.index');
    }

    function export(Request $request)
    {
        $start = $request->start;
        $end = $request->end;

        try {
            $excel = isset($start, $end) ? new LeadsExport($start, $end) : new LeadsExport();
            return Excel::download($excel, 'leads-' . date('YmdHis') . '.xlsx');
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }

        dd('berhasil');
    }

    public function search(Request $request)
    {
        $keyword = $request->email_search;
        $keyword1 = $request->source_search;
        $result = Lead::query();

        if (!empty($keyword)) {
            $result = $result->where('email', 'like', '%' . $keyword . '%');
        }
        if (!empty($keyword1)) {
            $result = $result->where('created_at', 'like', '%' . $keyword1 . '%');
        }

        $result = $result->get();

        //dd($result);
        return view('leads.search', compact('result'));
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->parameters([
                'dom'          => 'Bfrtip',
                'buttons'      => ['excel', 'csv'],
            ]);
    }
}
