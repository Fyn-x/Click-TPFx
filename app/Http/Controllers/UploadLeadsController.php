<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\LeadsDummyExport;
use App\Imports\LeadsImport;
use App\Models\Lead;
use App\Models\Source;
use App\Models\SPV;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class UploadLeadsController extends Controller
{
    public function __construct()
    {
        ini_set('memory_limit', '500M');
        ini_set('max_execution_time', '3600');
    }

    public function index()
    {
        // return Excel::download(new LeadsDummyExport(), 'leadsdummy100-' . date('YmdHis') . '.csv');
        $spv = SPV::on('mysql_crm')
            ->select('staffid', 'email', 'firstname', 'lastname')
            ->where('role', 3)
            ->where('active', 1)
            ->orderBy('firstname', 'ASC')
            ->get();

        return view('events.upload_leads', compact('spv'));
    }

    public function upload_leads(Request $request)
    {
        $failed_job = [];
        $success_job = [];
        $results = [];

        $file = $request->file('file_leads');
        $filename = $file->getClientOriginalName();
        $this->validateFileUpload($file);

        $file->move(public_path('uploads/csv'), $filename);
        $filepath = public_path('uploads/csv/' . $filename);

        $leads = Excel::toArray(new LeadsImport, $filepath)[0];
        shuffle($leads);
        $totalLeads = count($leads);

        for ($i = 0; $i < count($request->spv_id); $i++) {
            $spv_persentase[$request->spv_id[$i]] = $request->persentase[$i];
        }

        foreach ($spv_persentase as $spv => $percentage) {
            $numLeads = floor(($percentage / 100) * $totalLeads);

            for ($i = 0; $i < $numLeads; $i++) {
                if (empty($leads)) break 2;
                $lead = array_shift($leads);
                $results[] = [
                    'name' => $lead[0],
                    'email' => $lead[1],
                    'phonenumber' => $lead[2],
                    'source' => $lead[3],
                    'assigned' => $spv,
                ];
            }
        }

        if ($leads) {
            $numRemainingLeads = count($leads);
            $numSPVs = count($spv_persentase);
            $leadsPerSPV = ceil($numRemainingLeads / $numSPVs);
            foreach ($spv_persentase as $spv => $percentage) {
                for ($i = 0; $i < $leadsPerSPV; $i++) {
                    if (empty($leads)) break 2;
                    $lead = array_shift($leads);
                    $results[] = [
                        'name' => $lead[0],
                        'email' => $lead[1],
                        'phonenumber' => $lead[2],
                        'source' => $lead[3],
                        'assigned' => $spv,
                    ];
                }
            }
        }

        foreach ($results as $item) {
            $leadName = htmlentities($item['name']);
            $cleanedName = preg_replace(['/&([a-z])[a-z]+;/i', '/[^A-Za-z\ \-]/'], ['', ''], $leadName);

            $result = [
                'name' => $cleanedName ?? "No Name",
                'email' => htmlentities($item['email']),
                'phonenumber' => cleanPhoneNumber($item['phonenumber']),
                'source' => cleanData($item['source'] ?? ''),
                'assigned' => $item['assigned'],
                'url' => isset($item['url']) ? $item['url'] : url()->previous(),
                'campaign' => cleanData($item['campaign'] ?? ''),
                'medium' => cleanData($item['medium'] ?? ''),
                'compro_campaign_clicked' => $item['compro_campaign_clicked'] ?? '',
                'source_rekret_id' => cleanData($item['source_rekret_id'] ??  null),
            ];

            $req_obj = new Request($result);

            if ($request->direct) {
                $proses_crm = $this->_store_to_crm($result);
                if ($proses_crm->getData()->code == 200) {
                    array_push($success_job, $result);
                } else {
                    $result['error'] = $proses_crm->getData()->message;
                    array_push($failed_job, $result);
                }
            } else {
                $validate = $this->validation($req_obj);

                if ($validate->fails()) {
                    $result['error'] = $validate->errors()->first();
                    array_push($failed_job, $result);
                } else {
                    $proses_crm = $this->_store_to_crm($result);
                    if ($proses_crm->getData()->code == 200) {
                        $proses_db = $this->_store_to_db($result);
                        if ($proses_db->getData()->code == 200) {
                            array_push($success_job, $result);
                        } else {
                            $result['error'] = $proses_db->getData()->message;
                            array_push($failed_job, $result);
                        }
                    } else {
                        $result['error'] = $proses_crm->getData()->message;
                        array_push($failed_job, $result);
                    }
                }
            }
        }

        $failed_message = '';
        foreach ($failed_job as $item) {
            $failed_message .= '<li class="text-danger small">"' . $item['email'] . '" ' . $item['error'] . '</li>';
        }

        return back()->withSuccess(
            '
                Uploading has been completed! <br/>
                Success: ' . count($success_job) . '<br/>
                Failed: ' . count($failed_job) . '<br/><br/>' . $failed_message
        );
    }

    private function _store_to_crm($input)
    {
        try {
            if (!$input['medium'] || !$input['campaign']) {
                $sc_temp = strtolower($input['source']);
            } else {
                $sc_temp = strtolower($input['source'] . " " . $input['medium'] . " " . $input['campaign']);
            }

            $sc = Source::whereRaw('LOWER(name) = ?', $sc_temp)->select('id')->first();
            if (!$sc) {
                if (str_contains(strtolower($input['source']), 'google')) {
                    $sc_id = 99998;
                } elseif (str_contains(strtolower($input['source']), 'meta') || str_contains(strtolower($input['source']), 'facebook')) {
                    $sc_id = 99999;
                } elseif (str_contains(strtolower($input['source']), 'twitter')) {
                    $sc_id = 99997;
                }
            } else {
                $sc_id = $sc->id;
            }

            $body = [
                'source' => $sc_id,
                'status' => 11,
                'name' => $input['name'],
                'email' => $input['email'],
                'phonenumber' => $input['phonenumber'],
                'assigned' => $input['assigned']
            ];

            $client = new \GuzzleHttp\Client([
                'headers' => [
                    'authtoken' => config('api.api_crm_leads_token')
                ],
                // 'allow_redirec   ts' => true,
                // 'http_errors' => false,
            ]);

            $client->post(config('api.api_url_leads'), ['form_params' => $body]);
        } catch (\Throwable $th) {
            Log::info(['code' => $th->getCode(), 'message' => $th->getMessage()]);
            return response()->json(['code' => $th->getCode(), 'message' => $th->getMessage()]);
        }

        return response()->json(['code' => 200]);
    }

    private function _store_to_db($input)
    {
        try {
            Lead::on('mysql_leads')->insertGetId([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phonenumber'],
                'url' => $input['url'],
                'source' => $input['source'],
                'campaign' => $input['campaign'],
                'medium' => $input['medium'],
                'compro_campaign_clicked' => $input['compro_campaign_clicked'],
                // 'assigned' => $input['assigned']
            ]);
        } catch (\Throwable $th) {
            Log::info([$th->getCode() => $th->getMessage()]);
            return response()->json(['code' => $th->getCode(), 'message' => $th->getMessage()]);
        }

        return response()->json(['code' => 200]);
    }

    public function validation(Request $request)
    {
        $input = $request->all();
        $validation = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:mysql_leads.leads,email|email:rfc,dns',
            'phonenumber' => 'required|string|max:255|unique:mysql_leads.leads,phone',
            'source' => 'string',
        ];

        $leads_validation = Validator::make($input, $validation);
        Log::info([500 => $leads_validation->errors()->first()]);

        return $leads_validation;
    }

    public function validateFileUpload($file)
    {
        $extension = $file->getClientOriginalExtension();
        $fileSize = $file->getSize();

        $valid_extension = ["csv", "xlsx"];
        $maxFileSize = 2048152;
        if (in_array(strtolower($extension), $valid_extension)) {
            if ($fileSize <= $maxFileSize) {
            } else {
                throw new \Exception('No file was uploaded');
            }
        } else {
            throw new \Exception('Invalid file extension');
        }
    }
}
