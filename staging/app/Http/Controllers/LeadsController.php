<?php

namespace App\Http\Controllers;

use App\Mail\RegisterWebinar;
use App\Models\Import;
use App\Models\Lead;
use App\Models\Staff;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\QontakController;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class LeadsController extends Controller
{
    public function index() {
        
        return view('leads.index');
    }
    
    public function getLeads(Request $request)
    {
        if ($request->ajax()) {
        $result = Lead::selectRaw('id, name, email, phone, source, medium, campaign, CONVERT_TZ(`created_at`, @@session.time_zone, "Asia/Jakarta") as created_at')->latest();
         if ($request->filled('from_date') && $request->filled('to_date')) {
                $result = $result->whereBetween('created_at', [date_sub(date_create($request->from_date), date_interval_create_from_date_string('7 hour')), date_sub(date_create($request->to_date), date_interval_create_from_date_string('7 hour'))]);
            }
        return Datatables::of($result)
            ->addIndexColumn()
            ->editColumn('created_at', function ($result) {
            return $result->created_at; // no formatting, just returned $user->created_at; 
        })
            ->make(true);
        }
        return view('leads.index');
    }

     public function search(Request $request) {
         $keyword =$request->email_search;
         $keyword1 =$request->source_search;
         $result = Lead::query();
         
            if (!empty($keyword)) {
            $result = $result->where('email', 'like', '%'.$keyword.'%');
            }
            if (!empty($keyword1)) {
            $result = $result->where('created_at', 'like', '%'.$keyword1.'%');
            }
        
           $result = $result->get();

//dd($result);
        return view('leads.search',compact('result'));
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