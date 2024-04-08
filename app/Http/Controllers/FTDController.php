<?php

namespace App\Http\Controllers;

use App\Mail\FTDMail;
use App\Models\Lead;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DownloadPerClientExport;

class FTDController extends Controller
{
    private $TransactionPath = null;
    private $TradePath = null;
    private $EquityBulkPath = null;

    public function __construct() {
        $this->TransactionPath = config('api.api_client_id').config('api.getdepwith');
        $this->TradePath = config('api.api_client_id').config('api.gettrades');
        $this->EquityBulkPath = config('api.api_client_id').config('api.getequitybulk');
    }

    public function create()
    {
        return view('ftd.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => "required|string|max:255",
            'email' => "required|email",
            'source' => "required|string|max:255",
            'source_detail' => "nullable|string|max:255",
            'master_ib' => "required|string|max:255",
            'account_type' => "nullable|string|max:255",
            'freecomm' => "nullable|string|max:255",
            'commission' => "nullable|numeric|min:0|max:55",
            'markup' => "nullable|numeric|min:0|max:10",
            'rate' => "nullable|string|max:255",
            'leverage' => "nullable|numeric|min:100|max:400",
            'amount' => "nullable|numeric|min:10|max:999999999",
            'email_marketing' => "required|email",
            'name_marketing' => "required|string|max:255",
            'name_tl' => "required|string|max:255",
            'email_tl' => "required|email",
            'name_spv' => "required|string|max:255",
            'email_spv' => "required|email",
            'name_assm' => "nullable|string|max:255",
            'group' => "required|string|max:255",
            'screenshot.*' => "required|image|max:15360",
            'notes' => "nullable|string|max:255",
        ]);

        $request->merge([
            'email' => strtolower($request->email),
            'email_marketing' => strtolower($request->email_marketing),
            'email_tl' => strtolower($request->email_tl),
            'email_spv' => strtolower($request->email_spv),
            'created_at' => date('Y-m-d h:i:s')
        ]);

        $screenshots = array();
        if($request->file('screenshot') != null && count($request->file('screenshot')) > 0)
        {
            foreach($request->file('screenshot') as $key=>$screenshot){
                $screenshots[$key] = $screenshot->path();
            }
        }

        if($request->master_ib == "NASABAH") Mail::to("dealing.ftd@tpfx.co.id")
            ->cc([
                $request->email_tl,
                $request->email_spv
            ])
            ->send(new FTDMail($request->all(),$screenshots));

            $client = new \GuzzleHttp\Client([
                'allow_redirects' => false,
                'http_errors' => false
            ]);
            $response = $client->post('https://hook.us1.make.com/k0316g4xupdt23v1j8x1fu8f7bk33buo', [
                'form_params' => $request->all()
            ]);
            if($response->getStatusCode() != 200) {
                Log::info([$response->getStatusCode() => "FTD: " . json_encode($request->all())]);
            }
        elseif($request->master_ib == "IB") Mail::to("ib.support@team.tpfx.co.id")
            ->cc([
                $request->email_tl,
                $request->email_spv,
                'dealing.ftd@tpfx.co.id'
            ])
            ->send(new FTDMail($request->all(),$screenshots));

        return redirect('/ftd')->withSuccess('FTD berhasil disubmit. Silakan konfirmasi ke dealing jika email sudah diterima.');
    }

    public function check_leads() {
        return view('ftd.check_leads');
    }

    public function check_leads_store(Request $request) {
        $request->validate([
            'file_clients' => "required|mimes:csv,txt|max:2048",
        ]);

        $file = $request->file('file_clients');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        $fileSize = $file->getSize();

        $this->checkUploadedFileProperties($extension, $fileSize);
        $file->move(public_path('uploads/csv'), $filename);
        $filepath = public_path('uploads/csv/'.$filename);
        $file = fopen($filepath, "r");
        $importData_arr = array();
        $i = 0;
        $errors = array();
        while (($filedata = fgetcsv($file, 2000, ",")) !== FALSE) {
            $num = count($filedata);
            for ($c = 0; $c < $num; $c++) {
                $filedata_contents = explode(";", $filedata[$c]);
                foreach($filedata_contents as $filedata_content){
                    $importData_arr[$i][] = $filedata_content;
                }
            }
            $i++;
        }
        fclose($file);
        $j = 0;

        foreach($importData_arr as $key => $account){
            $account_numbers[] = (int) preg_replace("/[^0-9]/", "", $account[0] );
        }

        $client = new \GuzzleHttp\Client([
            'allow_redirects' => false,
            'http_errors' => false,
        ]);
        $responses_live = $client->request('POST', urldecode(urlencode($this->TransactionPath."?server=1&logins=".implode(",",$account_numbers)."&from=1970-01-01&to=".date('Y-m-d'))));
        $responses_real = $client->request('POST', urldecode(urlencode($this->TransactionPath."?server=2&logins=".implode(",",$account_numbers)."&from=1970-01-01&to=".date('Y-m-d'))));
        $responses = json_decode($responses_live->getBody(), true) + json_decode($responses_real->getBody(), true);

        foreach($importData_arr as $key => $client) {
            $clients[$key][0] = $client[0];
            $clients[$key][1] = $client[1];
            $clients[$key][2] = $client[2];
            $clients[$key][3] = isset($client[3]) ? $client[3] : "";
            $is_leads_email = Lead::where('email', $client[1])->first();
            if(isset($is_leads_email) && $client[1] != "" && isset($client[1])){
                $clients[$key][4] = 'YES';
                $clients[$key][5] = date('Y-m-d', strtotime($is_leads_email->created_at));
                $source[0] = isset($is_leads_email->source) ? $is_leads_email->source : '';
                $source[1] = isset($is_leads_email->medium) ? $is_leads_email->medium : '';
                $source[2] = isset($is_leads_email->campaign) ? $is_leads_email->campaign : '';
                $clients[$key][6] = implode(' ', $source);
            }
            else {
                $is_leads_phone = Lead::where('phone', 'like', '%'.$client[2].'%')->first();
                if(isset($is_leads_phone) && $client[2] != "0" && isset($client[2])){
                    $clients[$key][4] = 'YES';
                    $clients[$key][5] = date('Y-m-d', strtotime($is_leads_phone->created_at));
                    $source[0] = isset($is_leads_phone->source) ? $is_leads_phone->source : '';
                    $source[1] = isset($is_leads_phone->medium) ? $is_leads_phone->medium : '';
                    $source[2] = isset($is_leads_phone->campaign) ? $is_leads_phone->campaign : '';
                    $clients[$key][6] = implode(' ', $source);
                } else {
                    $clients[$key][4] = 'NO';
                    $clients[$key][5] = "NO DATA";
                    $clients[$key][6] = "NO DATA";
                }
            }
            if($clients[$key][4] == 'YES' || ($clients[$key][3] != "" && str_contains(strtolower($clients[$key][3]),'crm'))) {
                if(isset($responses[(int) preg_replace('/[^0-9 ]/m','',$client[0])])){
                    if($responses[(int) preg_replace('/[^0-9 ]/m','',$client[0])][0]['amount']>0){
                        $clients[$key][7] = date('Y-m-d', strtotime($responses[(int) preg_replace('/[^0-9 ]/m','',$client[0])][0]['time']));
                        $clients[$key][8] = (int) $responses[(int) preg_replace('/[^0-9 ]/m','',$client[0])][0]['amount'];
                        unset($responses[(int) preg_replace('/[^0-9 ]/m','',$client[0])][0]);
                        $clients[$key][9] = 0;
                        $clients[$key][10] = 0;
                        $clients[$key][11] = 0;
                        $clients[$key][12] = 0;
                        $clients[$key][13] = 0;
                        $clients[$key][14] = 0;
                        foreach($responses[(int) preg_replace('/[^0-9 ]/m','',$client[0])] as $client_transaction){
                            if($client_transaction['amount'] > 0){
                                $clients[$key][9] += $client_transaction['amount'];
                                if(date_create($client_transaction['time']) >= date_add(date_create($clients[$key][7]),date_interval_create_from_date_string("7 days"))){
                                    $clients[$key][12] += $client_transaction['amount'];
                                }
                            } else {
                                $clients[$key][10] += $client_transaction['amount'];
                                if(date_create($client_transaction['time']) >= date_add(date_create($clients[$key][7]),date_interval_create_from_date_string("7 days"))){
                                    $clients[$key][13] += $client_transaction['amount'];
                                }
                            }
                        }
                        $clients[$key][11] = $clients[$key][8] + $clients[$key][9] + $clients[$key][10];
                        $clients[$key][14] = $clients[$key][12] + $clients[$key][13];
                        $clients[$key][10] = abs($clients[$key][10]);
                        $clients[$key][13] = abs($clients[$key][13]);
                    }
                }
            }
        }

        $clients_title = [
            "- Account Number -",
            "- Email -",
            "- Phone -",
            "- Source Dealing -",
            "IS LEADS",
            "Leads Created",
            "Source",
            "Account Registration",
            "FTD",
            "Deposit",
            "Withdrawal",
            "Net Depo",
            "Retention Deposit",
            "Retention Withdrawal",
            "Retention Net Depo"
        ];

        array_unshift($clients, $clients_title);

        return Excel::download(new DownloadPerClientExport(collect($clients)), 'Check Leads - '.date('Y M d').'.xlsx');
    }

    public function check_last_trade() {
        return view('ftd.check_last_trade');
    }

    public function check_last_trade_store(Request $request) {
        $request->validate([
            'file_clients' => "required|mimes:csv,txt|max:2048",
        ]);

        $file = $request->file('file_clients');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        $fileSize = $file->getSize();

        $this->checkUploadedFileProperties($extension, $fileSize);
        $file->move(public_path('uploads/csv'), $filename);
        $filepath = public_path('uploads/csv/'.$filename);
        $file = fopen($filepath, "r");
        $importData_arr = array();
        $i = 0;
        $errors = array();
        while (($filedata = fgetcsv($file, 2000, ",")) !== FALSE) {
            $num = count($filedata);
            for ($c = 0; $c < $num; $c++) {
                $filedata_contents = explode(";", $filedata[$c]);
                foreach($filedata_contents as $filedata_content){
                    $importData_arr[$i][] = $filedata_content;
                }
            }
            $i++;
        }
        fclose($file);
        $j = 0;

        foreach($importData_arr as $key => $account){
            $account_numbers[] = (int) preg_replace("/[^0-9]/", "", $account[0] );
        }

        $client = new \GuzzleHttp\Client([
            'allow_redirects' => false,
            'http_errors' => false,
        ]);
        $responses1_live = $client->request('POST', urldecode(urlencode($this->TradePath."?server=1&logins=".implode(",",$account_numbers)."&from=1970-01-01&to=".date('Y-m-d'))));
        $responses1_real = $client->request('POST', urldecode(urlencode($this->TradePath."?server=2&logins=".implode(",",$account_numbers)."&from=1970-01-01&to=".date('Y-m-d'))));
        $responses1 = json_decode($responses1_live->getBody(), true) + json_decode($responses1_real->getBody(), true);

        $responses2_live = $client->request('POST', urldecode(urlencode($this->EquityBulkPath."?server=1&logins=".implode(",",$account_numbers))));
        $responses2_real = $client->request('POST', urldecode(urlencode($this->EquityBulkPath."?server=2&logins=".implode(",",$account_numbers))));
        $responses2 = array_merge_recursive(json_decode($responses2_live->getBody(), true), json_decode($responses2_real->getBody(), true));
        unset($responses2["total"]);
        unset($responses2["code"]);
        unset($responses2["codeStat"]);
        foreach($responses2 as $apiequity) {
            try{
                $responses_equity[(int) preg_replace('/[^0-9 ]/m','',$apiequity['login'])] = $apiequity['net_equity'];
            } catch (\Exception $e) {
                continue;
            }
        }

        foreach($importData_arr as $key => $client) {
            $clients[$key][0] = $client[0];
            if(isset($responses1[(int) preg_replace('/[^0-9 ]/m','',$client[0])])){
                $total_trades = count($responses1[(int) preg_replace('/[^0-9 ]/m','',$client[0])]);
                $clients[$key][1] = $responses1[(int) preg_replace('/[^0-9 ]/m','',$client[0])][$total_trades-1]['open_time'];
            } else {
                $clients[$key][1] = "Closed Account/Tidak ada transaksi";
            }
            if(isset($responses_equity[(int) preg_replace('/[^0-9 ]/m','',$client[0])])){
                $clients[$key][2] = $responses_equity[(int) preg_replace('/[^0-9 ]/m','',$client[0])];
            } else {
                $clients[$key][2] = "";
            }
        }

        $clients_title = [
            "Account Number",
            "Last Open Trade",
            "Equity"
        ];

        array_unshift($clients, $clients_title);

        return Excel::download(new DownloadPerClientExport(collect($clients)), 'Check Equity - '.date('Y M d').'.xlsx');
    }

    public function check_all_leads() {
        return view('ftd.check_all_leads');
    }

    public function check_all_leads_store(Request $request) {
        $request->validate([
            'file_clients' => "required|mimes:csv,txt|max:2048",
        ]);

        $file = $request->file('file_clients');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        $fileSize = $file->getSize();

        $this->checkUploadedFileProperties($extension, $fileSize);
        $file->move(public_path('uploads/csv'), $filename);
        $filepath = public_path('uploads/csv/'.$filename);
        $file = fopen($filepath, "r");
        $importData_arr = array();
        $i = 0;
        $errors = array();
        while (($filedata = fgetcsv($file, 2000, ",")) !== FALSE) {
            $num = count($filedata);
            for ($c = 0; $c < $num; $c++) {
                $filedata_contents = explode(";", $filedata[$c]);
                foreach($filedata_contents as $filedata_content){
                    $importData_arr[$i][] = $filedata_content;
                }
            }
            $i++;
        }
        fclose($file);
        $j = 0;

        foreach($importData_arr as $key => $account){
            $account_numbers[] = (int) preg_replace("/[^0-9]/", "", $account[0] );
        }

        $client = new \GuzzleHttp\Client([
            'allow_redirects' => false,
            'http_errors' => false,
        ]);
        $responses_live = $client->request('POST', urldecode(urlencode($this->TransactionPath."?server=1&logins=".implode(",",$account_numbers)."&from=1970-01-01&to=".date('Y-m-d'))));
        $responses_real = $client->request('POST', urldecode(urlencode($this->TransactionPath."?server=2&logins=".implode(",",$account_numbers)."&from=1970-01-01&to=".date('Y-m-d'))));
        //dd(json_decode($responses_live->getBody(), true), json_decode($responses_real->getBody(), true));
        $responses = json_decode($responses_live->getBody(), true) + json_decode($responses_real->getBody(), true);

        foreach($importData_arr as $key => $client) {
            $clients[$key][0] = $client[0];
            $clients[$key][1] = $client[1];
            $clients[$key][2] = $client[2];
            $clients[$key][3] = isset($client[3]) ? $client[3] : "";
            if(isset($responses[(int) preg_replace('/[^0-9 ]/m','',$client[0])])){
                if(!empty($client[0]) && !empty($responses[(int) preg_replace('/[^0-9 ]/m','',$client[0])][0]) && $responses[(int) preg_replace('/[^0-9 ]/m','',$client[0])][0]['amount']>0){
                    $clients[$key][7] = date('Y-m-d', strtotime($responses[(int) preg_replace('/[^0-9 ]/m','',$client[0])][0]['time']));
                    $clients[$key][8] = (int) $responses[(int) preg_replace('/[^0-9 ]/m','',$client[0])][0]['amount'];
                    unset($responses[(int) preg_replace('/[^0-9 ]/m','',$client[0])][0]);
                    $clients[$key][9] = 0;
                    $clients[$key][10] = 0;
                    $clients[$key][11] = 0;
                    foreach($responses[(int) preg_replace('/[^0-9 ]/m','',$client[0])] as $client_transaction){
                        if($client_transaction['amount'] > 0){
                            $clients[$key][9] += $client_transaction['amount'];
                        } else {
                            $clients[$key][10] += $client_transaction['amount'];
                        }
                    }
                    $clients[$key][11] = $clients[$key][8] + $clients[$key][9] + $clients[$key][10];
                    $clients[$key][10] = abs($clients[$key][10]);
                }
            }
        }

        $clients_title = [
            "- Account Number -",
            "- Email -",
            "- Phone -",
            "- Source Dealing -",
            "IS LEADS",
            "FTD",
            "Deposit",
            "Withdrawal",
            "Net Depo"
        ];

        array_unshift($clients, $clients_title);

        return Excel::download(new DownloadPerClientExport(collect($clients)), 'Check Leads - '.date('Y M d').'.xlsx');
    }

    public function checkUploadedFileProperties($extension, $fileSize)
    {
        $valid_extension = array("csv"); //Only want csv and excel files
        $maxFileSize = 2048152; // Uploaded file size limit is 2mb
        if (in_array(strtolower($extension), $valid_extension)) {
            if ($fileSize <= $maxFileSize) {
            } else {
                throw new \Exception('No file was uploaded', Response::HTTP_REQUEST_ENTITY_TOO_LARGE); //413 error
            }
        } else {
            throw new \Exception('Invalid file extension', Response::HTTP_UNSUPPORTED_MEDIA_TYPE); //415 error
        }
    }
}
