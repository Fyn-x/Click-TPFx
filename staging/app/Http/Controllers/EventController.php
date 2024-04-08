<?php

namespace App\Http\Controllers;

use App\Mail\RegisterWebinar;
use App\Models\Import;
use App\Models\Lead;
use App\Models\Staff;
use App\Models\Source;
use App\Models\QontakAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\QontakController;
use Illuminate\Support\Facades\Validator;
use Log;

class EventController extends Controller
{
    public function upload_leads_view() {
        return view('events.upload_leads_view');
    }

    public function upload_leads (Request $request) {
        $file = $request->file('file_leads');
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


        foreach($importData_arr as $key => $lead) {
            $req['name'] = preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities($lead[0]));;
            $req['email'] = $lead[1];
            $req['phonenumber'] = $lead[2];
            $req['source'] = $lead[3];
            $req['created_at'] = $lead[4];
            $req_obj = new Request($req);
            $this->store_leads_rekret($req_obj, $key+1);
        }

        return 'ok';
    }

    public function checkUploadedFileProperties($extension, $fileSize)
    {
        $valid_extension = array("csv"); //Only want csv and excel files
        $maxFileSize = 2048152; // Uploaded file size limit is 2mb
        if (in_array(strtolower($extension), $valid_extension)) {
            if ($fileSize <= $maxFileSize) {
            } else {
                throw new \Exception('No file was uploaded'); //413 error
            }
        } else {
            throw new \Exception('Invalid file extension'); //415 error
        }
    }

    public function process_leads($input, $url){
        $lead_id = Lead::on('mysql_leads')->insertGetId([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phonenumber'],
            'url' => $url,
            'source' => $input['source'],
            'campaign' => $input['campaign'],
            'medium' => $input['medium'],
            'compro_campaign_clicked' => $input['compro_campaign_clicked']
        ]);

        if (!$lead_id) {
            Log::info([500 => "Error input leads to DB"]);
            abort(500, 'Error input leads to DB');
        }

        switch ($lead_id % 16) {
            case 0:
                $assigned = 155; // yafet
                $qontak_assigned = "2368a88e-2254-4c40-a285-931733070a50";
                break;
            case 1:
                $assigned = 17; // tulus
                $qontak_assigned = "d852789d-c3ab-4aca-9176-c34dcddb551e";
                break;
            case 2:
                $assigned = 34; // ale
                $qontak_assigned = "6f1d57e8-64bc-41d8-80a3-a92ffb63baa1";
                break;
            case 3:
                $assigned = 61; // mulyanah
                $qontak_assigned = "c3602870-50cf-45e0-9d5f-9112341dd66e";
                break;
            case 4:
                $assigned = 72; // ilham
                $qontak_assigned = "a3caa418-e7d0-4aac-9083-56b1f987cc67";
                break;
            case 5:
                $assigned = 240; // rohmah
                $qontak_assigned = "47a32c0f-196d-4b3c-8556-dbd29caaee41";
                break;
            case 6:
                $assigned = 249; // idrus
                $qontak_assigned = "f270e7ab-6f02-4e66-a76b-0eb1938a97dc";
                break;
            case 7:
                $assigned = 250; // widy
                $qontak_assigned = "f302931d-bc52-42e9-9b07-af02f3503c10";
                break;
            case 8:
                $assigned = 34; // ale 2
                $qontak_assigned = "6f1d57e8-64bc-41d8-80a3-a92ffb63baa1";
                break;
            case 9:
                $assigned = 61; // mulyanah 2
                $qontak_assigned = "c3602870-50cf-45e0-9d5f-9112341dd66e";
                break;
            case 10:
                $assigned = 72; // ilham 2
                $qontak_assigned = "a3caa418-e7d0-4aac-9083-56b1f987cc67";
                break;
            case 11:
                $assigned = 240; // rohmah 2
                $qontak_assigned = "47a32c0f-196d-4b3c-8556-dbd29caaee41";
                break;
            case 12:
                $assigned = 277; // abdul
                $qontak_assigned = "80c33e5d-fa3d-4590-8e36-435f1ec7e27b";
                break;
            case 13:
                $assigned = 273; // deni
                $qontak_assigned = "80c33e5d-fa3d-4590-8e36-435f1ec7e27b";
                break;
            case 14:
                $assigned = 277; // abdul 2
                $qontak_assigned = "80c33e5d-fa3d-4590-8e36-435f1ec7e27b";
                break;
            case 15:
                $assigned = 273; // deni 2
                $qontak_assigned = "80c33e5d-fa3d-4590-8e36-435f1ec7e27b";
                break;
            default:
                $assigned = 1;
                $qontak_assigned = "80c33e5d-fa3d-4590-8e36-435f1ec7e27b";
                break;
        }

        if($input['medium'] == "" || $input['medium'] == null || $input['campaign'] == "" || $input['campaign'] == null){
            $sc_temp = strtolower($input['source']);
        } else {
            $sc_temp = strtolower($input['source']." ".$input['medium']." ".$input['campaign']);
        }
        $sc = Source::whereRaw('LOWER(name) = ?', $sc_temp)
            ->select('id')->first();
        if(!$sc) {
            if(str_contains(strtolower($input['source']), 'google')){
                $sc_id = 99998;
            } elseif(str_contains(strtolower($input['source']), 'meta') || str_contains(strtolower($input['source']), 'facebook')){
                $sc_id = 99999;
            } elseif(str_contains(strtolower($input['source']), 'twitter')) {
                $sc_id = 99997;
            }
        } else {
            $sc_id = $sc->id;
        }

        $headers = [
            'authtoken' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyIjoiYWRtaW4iLCJuYW1lIjoiQ1JNIEFkcyIsInBhc3N3b3JkIjpudWxsLCJBUElfVElNRSI6MTY4ODE5MzI5NiwiRVhQX0FQSV9USU1FIjoyNTU2MTE4NzQwfQ.6CqwMrrRx3K-i_nYKxisPXWqWNdD8fEhyYdYlONA8Xo'
        ];
        $json_create_leads = [
            'source' => $sc_id,
            'status' => 11,
            'name' => $input['name'],
            'email' => $input['email'],
            'phonenumber' => $input['phonenumber'],
            'assigned' => $assigned
        ];

        $client = new \GuzzleHttp\Client([
            'allow_redirects' => false,
            'http_errors' => false,
            'headers' => $headers
        ]);
        $response_create_leads = $client->post('https://crm.tpfx.co.id/api/leads', [
            'form_params' => $json_create_leads
        ]);
        if($response_create_leads->getStatusCode() != 200) {
            Log::info([$response_create_leads->getStatusCode() => json_encode($response_create_leads)]);
        }

        /*if(!in_array($assigned, [277,273])){
            $lead_id = json_decode($response_create_leads->getBody())->leadid;

            $qontak_broadcast = QontakController::qontak_broadcast_message($json_create_leads, $lead_id);
            if(isset($qontak_broadcast['room_lead_roomid'])){
                $qontak_room_id = $qontak_broadcast['room_lead_roomid'];
            } else {
                Log::info(202, ['event_qontak_broadcast',json_decode($qontak_broadcast, TRUE)]);
            }

            QontakAssignment::insert([
                'agent_id' => $qontak_assigned,
                'leads_phone_number' => '62'.$input['phonenumber']
            ]);
        }*/

        if ($input['campaign'] == 'ebook') {
            return redirect()->route('events.thank_you_ebook_basic');
        } else {
            return redirect()->route('events.thank_you');
        }
    }

    public function store_leads_google_form(Request $request)
    {
        $input['phonenumber'] = preg_replace('/^(\+628|\+6208|628|08)/', '8', $request->user_column_data[array_search('PHONE_NUMBER',array_column($request->user_column_data, 'column_id'),true)]->string_value);
        $input['name'] = preg_replace('/[^A-Za-z\ \-]/', '', $request->user_column_data[array_search('FULL_NAME',array_column($request->user_column_data, 'column_id'),true)]->string_value);
        $input['email'] = $request->user_column_data[array_search('EMAIL',array_column($request->user_column_data, 'column_id'),true)]->string_value;
        $input['over21'] = $request->user_column_data[array_search('OVER_21_AGE',array_column($request->user_column_data, 'column_id'),true)]->string_value;
        $validation = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:leads,email',
            'phonenumber' => 'required|string|max:255|unique:leads,phone',
            'over21' => 'string',
        ];
        $leads_validation = Validator::make($input, $validation);
        if ($leads_validation->fails()) {
            return response()->json(['status'=>'failed/duplicated'], 200);
        }

        $url = '';
        $input['campaign'] = 1;
        $input['medium'] = "Form";
        $input['source'] = "Google";
        $input['compro_campaign_clicked'] = "";

        return $this->process_leads($input, $url);
    }

    public function store_leads(Request $request)
    {
        $input = $request->all();
        $input['phonenumber'] = preg_replace('/^(\+628|\+6208|628|08)/', '8', $input['phonenumber']);
        $input['name'] = preg_replace('/[^A-Za-z\ \-]/', '', $input['name']);
        if($input['name'] == '') $input['name'] = "No Name";
        $input['source_rekret_id'] = isset($request->source_rekret_id) ? $request->source_rekret_id : null;
        $request->replace($input);
        $validation = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:mysql_leads.leads,email',
            'phonenumber' => 'required|string|max:255|unique:mysql_leads.leads,phone',
            'source' => 'string',
            'campaign' => 'string',
            'medium' => 'string',
            'compro_campaign_clicked' => 'string|nullable',
            'source_rekret_id' => 'numeric|nullable',
            'utm_campaign' => 'string'
        ];
        $leads_validation = Validator::make($input, $validation);
        if ($leads_validation->fails()) {
            Log::info([500 => $leads_validation->fails()]);
            if (isset($input['campaign']) && $input['campaign'] == 'ebook') {
                return redirect()->route('events.thank_you_ebook_basic');
            } else {
                return redirect()->route('events.thank_you');
            }
        }

        if(isset($input['url'])){
            $url = $input['url'];
        } else {
            $url = url()->previous();
        }
        //$url = url()->previous();
        $input['campaign'] = isset($input['campaign']) ? preg_replace("/[^A-Za-z0-9 ]/", '', $input['campaign']) : "";
        $input['medium'] = isset($input['medium']) ? preg_replace("/[^A-Za-z0-9 ]/", '', $input['medium']) : "";
        $input['source'] = isset($input['source']) ? preg_replace("/[^A-Za-z0-9 ]/", '', $input['source']) : "";
        $input['compro_campaign_clicked'] = isset($input['compro_campaign_clicked']) ? $input['compro_campaign_clicked'] : "";

        return $this->process_leads($input, $url);
    }

    public function update_leads($lead_id, $data_qontak){

    }

    public function store_leads_rekret(Request $request, $lead_id)
    {
        $input = $request->all();
        $input['phonenumber'] = preg_replace('/^(\+628|\+6208|628|08)/', '8', $input['phonenumber']);
        $input['name'] = preg_replace('/[^A-Za-z\ \-]/', '', $input['name']);
        if($input['name'] == '') $input['name'] = "No Name";
        $input['source_rekret_id'] = isset($request->source_rekret_id) ? $request->source_rekret_id : null;
        $request->replace($input);


        switch ($lead_id % 16) {
            case 0:
                $assigned = 155; // yafet
                break;
            case 1:
                $assigned = 17; // tulus
                break;
            case 2:
                $assigned = 34; // ale
                break;
            case 3:
                $assigned = 61; // mulyanah
                break;
            case 4:
                $assigned = 72; // ilham
                break;
            case 5:
                $assigned = 240; // rohmah
                break;
            case 6:
                $assigned = 249; // idrus
                break;
            case 7:
                $assigned = 250; // widy
                break;
            case 8:
                $assigned = 34; // ale 2
                break;
            case 9:
                $assigned = 61; // mulyanah 2
                break;
            case 10:
                $assigned = 72; // ilham 2
                break;
            case 11:
                $assigned = 240; // rohmah 2
                break;
            case 12:
                $assigned = 277; // abdul
                break;
            case 13:
                $assigned = 273; // deni
                break;
            case 14:
                $assigned = 277; // abdul 2
                break;
            case 15:
                $assigned = 273; // deni 2
                break;
            default:
                $assigned = 1;
                break;
        }

        $sc_temp = strtolower($input['source']);
        $sc = Source::whereRaw('LOWER(name) = ?', $sc_temp)
            ->select('id')->first();
        if(!$sc) {
            if(str_contains(strtolower($input['source']), 'google')){
                $sc_id = 99998;
            } elseif(str_contains(strtolower($input['source']), 'meta') || str_contains(strtolower($input['source']), 'facebook')){
                $sc_id = 99999;
            } elseif(str_contains(strtolower($input['source']), 'twitter')) {
                $sc_id = 99997;
            }
        } else {
            $sc_id = $sc->id;
        }

        if(!isset($sc_id)) Log::info($request->all());

        $headers = [
            'authtoken' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyIjoiYWRtaW4iLCJuYW1lIjoiQ1JNIEFkcyIsInBhc3N3b3JkIjpudWxsLCJBUElfVElNRSI6MTY4ODE5MzI5NiwiRVhQX0FQSV9USU1FIjoyNTU2MTE4NzQwfQ.6CqwMrrRx3K-i_nYKxisPXWqWNdD8fEhyYdYlONA8Xo'
        ];
        $json = [
            'source' => $sc_id,
            'status' => 11,
            'name' => $input['name'],
            'email' => $input['email'],
            'phonenumber' => $input['phonenumber'],
            'assigned' => $assigned,
            'dateadded' => date("Y-m-d h:i:s", strtotime($input['created_at']))
        ];

        $client = new \GuzzleHttp\Client([
            'allow_redirects' => false,
            'http_errors' => false,
            'headers' => $headers
        ]);
        $response = $client->post('https://crm.tpfx.co.id/api/leads', [
            'form_params' => $json
        ]);
        if($response->getStatusCode() != 200) {
            Log::info([$response->getStatusCode() => json_encode($json)]);
        }

        return redirect()->route('events.thank_you');
    }

    public function leads_form(Request $request)
    {
        $utm_source = $request->utm_source;
        $utm_medium = $request->utm_medium;
        $utm_campaign = $request->utm_campaign;
        $banner_image_desktop = null;
        $banner_image_mobile = null;
        switch ($utm_campaign) {
            case 'acuity':
                $download_button = 'INSTALL SEKARANG';
                $title = 'Download Acuity pada Metatrader';

                $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                $form_subheading = '';

                $banner_image_desktop = 'banner-acuity-desktop-compress-1.jpg';
                $banner_image_mobile = 'banner-acuity-mobile-compress-1.jpg';

                $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                $section_2_title_1 = 'ACUITY';
                $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                $section_2_image_1 = 'acuity-compress-1.jpg';
                $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                $section_2_image_2 = 'winner-acuity-compress-1.jpg';
                $section_2_title_3 = 'Platform Yang Sama';
                $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                $section_2_image_3 = 'platform-acuity-compress-1.jpg';
                $section_2_title_4 = 'Sentimen Market Alert';
                $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                $section_2_image_4 = 'sentimen-acuity-compress-1.jpg';
                $section_2_title_5 = 'Strategi Partner';
                $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                $section_2_image_5 = 'strategi-acuity-compress-1.jpg';
                $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                $section_2_image_6 = 'kalender-acuity-compress-1.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case 'detik':
                        $source = 330205;
                        break;
                    case 'email':
                        $source = 338863;
                        break;
                    case 'fbpage':
                        $source = 338869;
                        break;
                    case 'google':
                        $source = 338874;
                        break;
                    case 'igbio':
                        $source = 338886;
                        break;
                    case 'linkedin':
                        $source = 338891;
                        break;
                    case 'mgid.com':
                        $source = 342998;
                        break;
                    case ('facebook'):
                        $source = 338903;
                        break;
                    case ('social'):
                        $source = 338903;
                        break;
                    case 'telegram':
                        $source = 338910;
                        break;
                    case 'tiktok':
                        $source = 338912;
                        break;
                    case 'twitter':
                        $source = 338917;
                        break;
                    case 'whatsapp':
                        $source = 338922;
                        break;
                }
                break;
            case 'bigdeal':
                $download_button = 'Daftar Sekarang';
                $title = 'Trading Logam Mulia';

                $form_heading = 'Ikuti Merdeka Big Deals';
                $form_subheading = 'Deposit dan menangkan hadiah';

                $section_1_heading = 'Raih Hadiahnya Rayakan Meriahnya';
                $section_1_subheading = 'Rayakan kemerdekaan kali ini bersama TPFx Merdeka Big Deals! Buka Akun Live sekarang dan menangkan Mercedes C200, Mitsubishi New Xpander dan puluhan hadiah impian kamu lainnya sekarang tanpa diundi.';
                $section_1_row_heading = '';

                $banner_image_desktop = 'bigdeal-desktop.jpg';
                $banner_image_mobile = 'bigdeal-mobile.jpg';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image spread.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image leverage.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4.png';
                $section_2_title_4 = 'Trading Central';
                $section_2_description_4 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                $section_2_image_4 = 'acuity.jpg';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image support.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image dana.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';


                $section_5_heading = 'TPFx Ramadan Promo Reward Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';

                $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                $reward_cashback_row = 0;

                switch ($utm_source) {
                    case 'email':
                        $source = 456128;
                        break;
                    case 'google':
                        $source = 456094;
                        break;
                    case 'ig bio':
                        $source = 456109;
                        break;
                    case 'mgid':
                        $source = 456110;
                        break;
                    case 'news':
                        $source = 456165;
                        break;
                    case 'outbrain':
                        $source = 456113;
                        break;
                    case ('social' || 'facebook'):
                        $source = 456093;
                        break;
                    case 'tiktok':
                        $source = 456112;
                        break;
                    case 'twitter':
                        $source = 456111;
                        break;
                    case 'youtube':
                        $source = 456166;
                        break;
                }
                break;
            case 'cashback20rewards':
                $download_button = 'Promo Reward Emas';
                $title = 'Promo Reward Emas';

                $form_heading = 'Promo Reward Emas';
                $form_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';

                $section_1_heading = 'Promo Reward Emas';
                $section_1_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $banner_image_desktop = 'reward-emas-desktop-rev1.jpg';
                $banner_image_mobile = 'reward-emas-mobile-rev1.jpg';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Trading Central';
                $section_2_description_4 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                $section_2_image_4 = 'acuity-compress-1.jpg';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Promo Reward Emas Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';

                $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                $reward_cashback_row = 0;

                switch ($utm_source) {
                    case 'google':
                        $source = 353623;
                        break;
                    case 'outbrain':
                        $source = 362599;
                        break;
                    case ('facebook'):
                        $source = 353625;
                        break;
                    case ('social'):
                        $source = 353625;
                        break;
                    case 'twitter':
                        $source = 353626;
                        break;
                    case 'youtube':
                        $source = 358396;
                        break;
                    case 'email':
                        $source = 353627;
                        $download_button = 'Dapatkan Cashback';
                        $title = 'CASHBACK REWARDS 20%';

                        $form_heading = 'TPFx Cashback Rewards';
                        $form_subheading = 'Dapatkan Cashback 20% dari Nilai Deposit';

                        $section_1_heading = 'TPFx CASHBACK REWARDS 20%';
                        $section_1_subheading = 'Daftar sekarang untuk dapatkan keuntungan berlipat Anda';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                        $banner_image_desktop = 'cashback-20-email-desktop.jpg';
                        $banner_image_mobile = 'cashback-20-email-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Bersama TPFx?';
                        $section_2_title_1 = 'Spread Mulai 0,15';
                        $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                        $section_2_image_1 = 'image_spread-compress-1.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image_leverage-compress-1.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4-compress-1.png';
                        $section_2_title_4 = 'Trading Central';
                        $section_2_description_4 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_4 = 'acuity-compress-1.jpg';
                        $section_2_title_5 = 'TPFx Support 24 Jam';
                        $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                        $section_2_image_5 = 'image_support-compress-1.png';
                        $section_2_title_6 = 'Pencairan Dana Cepat';
                        $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                        $section_2_image_6 = 'image_dana-compress-1.png';

                        $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                        $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                        $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                        $section_4_subheading_1 = 'Likuiditas';
                        $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                        $section_4_subheading_2 = 'Leverage';
                        $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Dapatkan Cashback Reward Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';

                        $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                        $reward_cashback_row = 0;
                        break;
                }
                break;
            case 'compro':
                $download_button = 'INSTALL SEKARANG';
                $title = 'Trading Forex, Emas dan Indeks dengan Spread Terendah';

                $form_heading = 'Buat Akun Sekarang';
                $form_subheading = '';

                $banner_image_desktop = 'compro-baru.jpg';
                $banner_image_mobile = 'compro-baru-mobile.jpg';

                $section_1_heading = 'Dengan pengalaman lebih dari 18 tahun dalam industri berjangka TPFX menyediakan berbagai pilihan produk dengan berbagai fasilitas untuk memberikan pengalaman trading terbaik kepada para nasabah kami.';
                $section_1_subheading = '';
                $section_1_row_heading = '';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Broker Teregulasi';
                $section_2_description_1 = 'TPFx pialang resmi dan teregulasi oleh BAPPEBTI. Trading aman dengan Segregated Account';
                $section_2_image_1 = 'broker-teregulasi.jpg';
                $section_2_title_2 = 'Spread Start From Zero';
                $section_2_description_2 = 'Transaksi dengan spread paling rendah, kompetitif dan stabil mulai 0';
                $section_2_image_2 = 'spread-nol.jpg';
                $section_2_title_3 = 'Bebas biaya Tersembunyi';
                $section_2_description_3 = 'Tingkatkan profit dengan fasilitas biaya tersembunyi bebas komisi dan bebas swap';
                $section_2_image_3 = 'bebas-biaya.jpg';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Dapatkan edukasi dan analisa trading langsung dari Para Ahlinya';
                $section_2_image_4 = 'edukasi-trading.jpg';
                $section_2_title_5 = 'Tools Trading';
                $section_2_description_5 = 'Fasilitas Trading Central yang memberikan analisa terupdate dan trading layak nya professional';
                $section_2_image_5 = 'tools-trading.jpg';
                $section_2_title_6 = 'Full Support Team TPFx';
                $section_2_description_6 = 'Team kami siap membantu anda sampai 24 jam';
                $section_2_image_6 = 'full-support.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';


                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case 'detik':
                        $source = 500111;
                        break;
                    case 'email':
                        $source = 500112;
                        break;
                    case 'fbpage':
                        $source = 500113;
                        break;
                    case 'google':
                        $source = 500114;
                        break;
                    case 'igbio':
                        $source = 500115;
                        break;
                    case 'linkedin':
                        $source = 500116;
                        break;
                    case 'mgid.com':
                        $source = 500117;
                        break;
                    case ('facebook'):
                        $source = 500118;
                        break;
                    case ('social'):
                        $source = 500119;
                        break;
                    case 'telegram':
                        $source = 500120;
                        break;
                    case 'tiktok':
                        $source = 500121;
                        break;
                    case 'twitter':
                        $source = 500122;
                        break;
                    case 'whatsapp':
                        $source = 500123;
                        break;
                }
                break;
            case 'ebook':
                $download_button = 'DOWNLOAD EBOOK';
                $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                $form_subheading = '';

                $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                $banner_image_desktop = 'ebook-desktop-compress-1.jpg';
                $banner_image_mobile = 'ebook-mobile-compress-1.jpg';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;
switch ($utm_source) {

                    case 'google':
                        $source='google display ebook';
                        break;
                    case 'twitter':
                        $source='twitter image ebook';
                        break;
                }
                break;


                case 'ebooktc':
                    $download_button = 'DOWNLOAD EBOOK';
                    $title = 'Ebook Mahir Trading dengan
                    Trading Central Alpha Generation
                    ';

                    $form_heading = 'Download Ebook
                    Trading Central
                    ';
                    $form_subheading = '';

                    $section_1_heading = 'Ebook Mahir Trading dengan
                    Trading Central Alpha Generation
                    ';
                    $section_1_subheading = 'Download dan dapatkan ebook panduan trading jitu menggunakan kombinasi strategi dan 3 indikator.
                    ';
                    $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                    $banner_image_desktop = 'ebooktc-desktop.jpg';
                    $banner_image_mobile = 'ebooktc-mobile.jpg';

                    $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                    $section_2_title_1 = 'Spread Mulai 0,15';
                    $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                    $section_2_image_1 = 'image_spread-compress-1.png';
                    $section_2_title_2 = 'Leverage sampai 1 : 400';
                    $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                    $section_2_image_2 = 'image_leverage-compress-1.png';
                    $section_2_title_3 = 'Platform Professional';
                    $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                    $section_2_image_3 = 'mt4-compress-1.png';
                    $section_2_title_4 = 'Edukasi Nasabah';
                    $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                    $section_2_image_4 = 'image_education-compress-1.png';
                    $section_2_title_5 = 'TPFx Support 24 Jam';
                    $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                    $section_2_image_5 = 'image_support-compress-1.png';
                    $section_2_title_6 = 'Pencairan Dana Cepat';
                    $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                    $section_2_image_6 = 'image_dana-compress-1.png';

                    $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                    $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                    $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                    $section_4_subheading_1 = 'Likuiditas';
                    $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                    $section_4_subheading_2 = 'Leverage';
                    $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                    $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                    $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                    $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                    $section_5_cta = 'DAFTAR SEKARANG';
                    $reward_cashback_tnc = null;
                    $reward_cashback_row = 1;
                    switch ($utm_source) {

                    case 'google':
                        switch ($utm_medium) {
                            case 'video':
                                $source='google video ebooktc';
                            break;
                            case 'banner':
                                $source='google banner ebooktc';
                            break;
                        }
                        break;
                        case 'meta':
                            $source='meta form ebooktc';
                        break;
                        case 'twitter':
                            $source='twitter video ebooktc';
                        break;
                        case 'twitter':
                            $source='twitter video ebooktc';
                        break;
                        case 'igbio':
                            $source='igbio link ebooktc';
                        break;
                                }
                    break;


            case 'merdekatradefest':
                $download_button = 'Daftar Merdeka Tradefest';
                $title = 'TPFx Merdeka Tradefest';

                $form_heading = 'Daftar Merdeka Tradefest Gratis';
                $form_subheading = '';

                $section_1_heading = 'TPFx Merdeka Tradefest';
                $section_1_subheading = 'Buka Akun Live sekarang langsung bisa ikutan TPFx Merdeka Tradefest gratis. Hanya dengan transaksi sebanyak-banyaknya Kamu bisa mendapatkan Smartphone Impian Gratis tanpa diundi.';
                $section_1_row_heading = '';

                $banner_image_desktop = 'tradefest-desktop-v1.jpg';
                $banner_image_mobile = 'tradefest-mobile-v1.jpg';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image spread.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image leverage.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4.png';
                $section_2_title_4 = 'Trading Central';
                $section_2_description_4 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                $section_2_image_4 = 'acuity.jpg';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image support.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image dana.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';

                $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                $reward_cashback_row = 0;

                switch ($utm_source) {
                    case 'google':
                        switch ($utm_medium) {
                            case 'video':
                                $source='google video merdekatradefest';
                                break;
                            case 'banner':
                                $source='google banner merdekatradefest';
                                break;
                        }
                        break;
                    case 'twitter':
                        switch ($utm_medium) {
                            case 'video':
                                $source='twitter video merdekatradefest';
                                break;
                            case 'banner':
                                $source='twitter banner merdekatradefest';
                                break;
                        }
                        break;

                    case 'metaendform':
                        switch ($utm_medium) {
                            case 'video':
                                $source='metaendform video merdekatradefest';
                                break;
                            case 'banner':
                                $source='metaendform banner merdekatradefest';
                                break;
                        }
                        break;
                    case 'email':
                        $source='email text merdekatradefest';
                        break;

                    case 'twitterbio':
                        $source='twitterbio link merdekatradefest';
                        break;
                    case 'tiktokbio':
                        $source='tiktokbio link merdekatradefest';
                        break;
                    case 'igbio':
                        $source='igbio link merdekatradefest';
                        break;
                    case 'popup':
                        $source='popup banner merdekatradefest';
                        break;
                    case 'seputarforex':
                        $source='seputarforex artikel merdekatradefest';
                        break;
                }
                break;


            case 'merdeka-tradefest':
                $download_button = 'Daftar Merdeka Tradefest';
                $title = 'TPFx Merdeka Tradefest';

                $form_heading = 'Daftar Merdeka Tradefest Gratis';
                $form_subheading = '';

                $section_1_heading = 'TPFx Merdeka Tradefest';
                $section_1_subheading = 'Buka Akun Live sekarang langsung bisa ikutan TPFx Merdeka Tradefest gratis. Hanya dengan transaksi sebanyak-banyaknya Kamu bisa mendapatkan Smartphone Impian Gratis tanpa diundi.';
                $section_1_row_heading = '';

                $banner_image_desktop = 'tradefest-desktop-v2.jpg';
                $banner_image_mobile = 'tradefest-mobile-v2.jpg';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image spread.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image leverage.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4.png';
                $section_2_title_4 = 'Trading Central';
                $section_2_description_4 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                $section_2_image_4 = 'acuity.jpg';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image support.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image dana.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';

                $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                $reward_cashback_row = 0;

                switch ($utm_source) {
                    case 'google':
                        switch ($utm_medium) {
                            case 'video':
                                $source='google video merdeka-tradefest';
                                break;
                            case 'banner':
                                $source='google banner merdeka-tradefest';
                                break;
                        }
                        break;
                    case 'twitter':
                        switch ($utm_medium) {
                            case 'video':
                                $source='twitter video merdeka-tradefest';
                                break;
                            case 'banner':
                                $source='twitter banner merdeka-tradefest';
                                break;
                        }
                        break;

                    case 'metaendform':
                        switch ($utm_medium) {
                            case 'video':
                                $source='metaendform video merdeka-tradefest';
                                break;
                            case 'banner':
                                $source='metaendform banner merdeka-tradefest';
                                break;
                        }
                        break;
                    case 'email':
                        $source='email text merdeka-tradefest';
                        break;

                    case 'twitterbio':
                        $source='twitterbio link merdeka-tradefest';
                        break;
                    case 'tiktokbio':
                        $source='tiktokbio link merdeka-tradefest';
                        break;
                    case 'igbio':
                        $source='igbio link merdeka-tradefest';
                        break;
                    case 'popup':
                        $source='popup banner merdeka-tradefest';
                        break;
                    case 'seputarforex':
                        $source='seputarforex artikel merdeka-tradefest';
                        break;
                }
                break;

            case 'freeswap300':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Mulai Trading Tanpa Biaya Inap';

                $form_heading = 'MULAI TRADING ONLINE TANPA BIAYA INAP';
                $form_subheading = 'Bebas transaksi berhari-hari tanpa biaya inap mulai dari $300';

                $section_1_heading = 'MULAI TRADING ONLINE TANPA BIAYA INAP';
                $section_1_subheading = 'Bebas transaksi berhari-hari tanpa biaya inap mulai dari $300';
                $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case 'advertorial':
                        $source = 330207;
                        break;
                    case 'detik':
                        $source = 338860;
                        break;
                    case 'email':
                        $source = 338865;
                        break;
                    case 'fbpage':
                        $source = 338871;
                        break;
                    case 'google':
                        $source = 338876;
                        break;
                    case 'igbio':
                        $source = 338888;
                        break;
                    case 'smsblast':
                        $source = 338900;
                        break;
                    case ('facebook'):
                        $source = 338905;
                        break;
                    case ('social'):
                        $source = 338905;
                        break;
                    case 'tiktok':
                        $source = 338914;
                        break;
                    case 'twitter':
                        $source = 338919;
                        break;
                }
                break;
            case 'freeswap300':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Mulai Trading Tanpa Biaya Inap';

                $form_heading = 'MULAI TRADING ONLINE TANPA BIAYA INAP';
                $form_subheading = 'Bebas transaksi berhari-hari tanpa biaya inap mulai dari $300';

                $section_1_heading = 'MULAI TRADING ONLINE TANPA BIAYA INAP';
                $section_1_subheading = 'Bebas transaksi berhari-hari tanpa biaya inap mulai dari $300';
                $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case 'google':
                        $source = 338880;
                        break;
                }
                break;
            case 'gold015':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Emas Spread Rendah';

                $form_heading = 'Dapatkan Income Harian';
                $form_subheading = 'Trading Gold Spread Mulai 0,15';

                $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                $banner_image_desktop = 'gold015-desktop-compress-1.jpg';
                $banner_image_mobile = 'gold015-mobile-compress-1.jpg';

                $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Emas Online';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';


                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case 'advertorial':
                        $source = 330210;
                        break;
                    case 'detik':
                        $source = 338861;
                        break;
                    case 'email':
                        $source = 338866;
                        break;
                    case 'fbpage':
                        $source = 338872;
                        break;
                    case 'google':
                        $source = 338877;
                        break;
                    case 'igbio':
                        $source = 338889;
                        break;
                    case ('facebook'):
                        $source = 338906;
                        break;
                    case ('social'):
                        $source = 338906;
                        break;
                    case 'tiktok':
                        $source = 338915;
                        break;
                    case 'twitter':
                        $source = 339036;
                        break;
                    case 'smsblast':
                        $source = 338901;
                        break;
                }
                break;
            case 'ib':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Introducing Broker TPFx';

                $form_heading = '';
                $form_subheading = '';

                $banner_image_desktop = 'ib-desktop-baru.jpg';
                $banner_image_mobile = 'ib-mobile.jpg';

                $section_1_heading = 'Introducing Broker TPFx';
                $section_1_subheading = 'Program kemitraan dan peroleh komisi dari setiap perdagangan klien anda';
                $section_1_row_heading = '';

                $section_2_heading = 'Mengapa Menjadi IB di TPFx?';
                $section_2_title_1 = 'Komisi IB Terbesar';
                $section_2_description_1 = 'Komisi IB Terbesar nomor 1 di Indonesia';
                $section_2_image_1 = 'komisi_ib_terbesar.jpg';
                $section_2_title_2 = 'Broker Teregulasi';
                $section_2_description_2 = 'Bermitra dengan broker yang sudah terdaftar dan teregulasi oleh BAPPEBTI';
                $section_2_image_2 = 'Broker_Teregulasi.jpg';
                $section_2_title_3 = 'Full Support Team TPFx';
                $section_2_description_3 = 'Team Kami siap membantu Anda agar karir Anda semakin berkembang';
                $section_2_image_3 = 'tpfx_support.jpg';
                $section_2_title_4 = 'Pencairan Komisi Cepat';
                $section_2_description_4 = 'Penarikan dan pencairan komisi cepat di hari yang sama';
                $section_2_image_4 = 'Pencairan_Komisi_Cepat.jpg';
                $section_2_title_5 = 'Platform Professional';
                $section_2_description_5 = 'Eksekusi cepat dengan platform professional';
                $section_2_image_5 = 'professional_platform_rev.jpg';
                $section_2_title_6 = 'Trading Central';
                $section_2_description_6 = 'Transaksi menggunakan Trading Central dan hasilkan profit dengan maksimal';
                $section_2_image_6 = 'acuity-compress-1.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';
                 $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 0;

                switch ($utm_source) {
                    case ('facebook'):
                        $source = 338907;
                        break;
                    case ('social'):
                        $source = 338907;
                        break;
					case ('google'):
                        $source = 387594;
                        break;
					case ('igbio'):
                        $source = 387599;
                        break;
					case ('mgid.com'):
                        $source = 387600;
                        break;
					case ('news'):
                        $source = 387603;
                        break;
					case ('outbrain'):
                        $source = 387602;
                        break;
					case ('partipost'):
                        $source = 387601;
                        break;
					case ('tiktok'):
                        $source = 387598;
                        break;
					case ('twitter'):
                        $source = 387597;
                        break;
					case ('youtube'):
                        $source = 387595;
                        break;

                }
                break;
            case 'seminar':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'Live Trading bersama Novry Simanjuntak';
                $form_subheading = "Akan membahas tuntas mengenai Scalping dan membahas lebih jauh mengenai: Technical Analysis menggunakan Indikator dan Pattern, Trader style berdasarkan sistem trading, Live Trading with Novry Simajuntak";

                $banner_image_desktop = 'banner-seminar-novry-desktop.jpg';
                $banner_image_mobile = 'banner-seminar-novry-mobile.jpg';

                $section_1_heading = 'MAKE MONEY WITH SCALPING STRATEGY AND LIVE TRADING';
                $section_1_subheading = 'Jumat 17 Juni 2022 at Hotel Santika Premiere Hayam Wuruk';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case ('facebook'):
                        $source = 398829;
                        break;
                    case ('social'):
                        $source = 398829;
                        break;
                    case ('google'):
                        $source = 398827;
                        break;
                    case ('igbio'):
                        $source = 398832;
                        break;
                    case ('mgid.com'):
                        $source = 398833;
                        break;
                    case ('news'):
                        $source = 398836;
                        break;
                    case ('outbrain'):
                        $source = 398835;
                        break;
                    case ('partipost'):
                        $source = 398834;
                        break;
                    case ('tiktok'):
                        $source = 398831;
                        break;
                    case ('twitter'):
                        $source = 398830;
                        break;
                    case ('youtube'):
                        $source = 398828;
                        break;
                }
                break;
            case 'smartphonegratis':
                $download_button = 'Daftar Gebyar Smartphone Gratis';
                $title = 'TPFx Gebyar Smartphone Gratis';

                $form_heading = 'Daftar Gebyar Smartphone Gratis';
                $form_subheading = 'Deposit dan menangkan hadiah smartphone';

                $section_1_heading = 'Gebyar Smartphone Gratis TPFx';
                $section_1_subheading = 'Ikuti gebyar smartphone gratis TPFx untuk seluruh nasabah yang membuka Akun Live. Transaksi sebanyak-banyak nya dan dapatkan smartphone gratis impian kamu tanpa diundi sekarang';
                $section_1_row_heading = '';

                $banner_image_desktop = 'smartphone-gebyar-desktop-rev1.jpg';
                $banner_image_mobile = 'smartphone-gebyar-mobile.jpg';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image spread.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image leverage.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4.png';
                $section_2_title_4 = 'Trading Central';
                $section_2_description_4 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                $section_2_image_4 = 'acuity.jpg';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image support.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image dana.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';

                $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                $reward_cashback_row = 0;

                switch ($utm_source) {
                    case ('facebook'):
                        $source = 413246;
                        break;
                    case ('social'):
                        $source = 413246;
                        break;
                    case ('google'):
                        $source = 413244;
                        break;
                    case ('email'):
                        $source = 420414;
                        break;
                    case ('igbio'):
                        $source = 413249;
                        break;
                    case ('mgid.com'):
                        $source = 413250;
                        break;
                    case ('news'):
                        $source = 413253;
                        break;
                    case ('outbrain'):
                        $source = 413252;
                        break;
                    case ('partipost'):
                        $source = 413251;
                        break;
                    case ('tiktok'):
                        $source = 413248;
                        break;
                    case ('twitter'):
                        $source = 413247;
                        break;
                    case ('youtube'):
                        $source = 413245;
                        break;
                }
                break;
            case 'tpfxgadget':
                $download_button = 'Daftar Reward Gadget';
                $title = 'TPFx Bagi-Bagi Gadget';

                $form_heading = 'Daftar Reward Gadget';
                $form_subheading = 'Deposit dan menangkan hadiah gadget';

                $section_1_heading = 'TPFx Bagi-Bagi Gadget';
                $section_1_subheading = 'TPFx kini hadir bagi bagi puluhan gadget kepada seluruh nasabah tanpa diundi. Deposit dan menangkan hadiah gadget impian mu sekarang.';
                $section_1_row_heading = '';

                $banner_image_desktop = 'smartphone-bagibagi-desktop.jpg';
                $banner_image_mobile = 'smartphone-bagibagi-mobile.jpg';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image spread.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image leverage.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4.png';
                $section_2_title_4 = 'Trading Central';
                $section_2_description_4 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                $section_2_image_4 = 'acuity.jpg';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image support.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image dana.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';

                $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                $reward_cashback_row = 0;

                switch ($utm_source) {
                    case ('facebook'):
                        $source = 413502;
                        break;
                    case ('social'):
                        $source = 413502;
                        break;
                    case ('google'):
                        $source = 413501;
                        break;
                    case ('email'):
                        $source = 420413;
                        break;
                    case ('igbio'):
                        $source = 413503;
                        break;
                    case ('mgid.com'):
                        $source = 413504;
                        break;
                    case ('news'):
                        $source = 413505;
                        break;
                    case ('outbrain'):
                        $source = 413506;
                        break;
                    case ('partipost'):
                        $source = 413507;
                        break;
                    case ('tiktok'):
                        $source = 413508;
                        break;
                    case ('twitter'):
                        $source = 413509;
                        break;
                    case ('youtube'):
                        $source = 413510;
                        break;
                }
                break;
            case 'webinar':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Webinar';

                $form_heading = 'Daftar Webinar';
                $form_subheading = 'Ikuti Webinar & Belajar Trading Sekarang';

                $banner_image_desktop = 'banner-20220908-desktop.jpg';
                $banner_image_mobile = 'banner-20220908-mobile.jpg';

                $section_1_heading = 'Exclusive Live Trading';
                $section_1_subheading = 'Bertransaksi dan hasilkan prodit maksimal bersama Market Analyst TPFx';
                $section_1_row_heading = 'Webinar khusus untuk mempelajari seputar dunia trading dan diselenggarakan secara online dengan materi menarik dan pembicara professional';

                $section_2_heading = 'Mengapa Mengikuti Webinar dari TPFx?';
                $section_2_title_1 = 'Edukasi Terupdate';
                $section_2_description_1 = 'Materi dan Edukasi terupdate untuk calon trader professional';
                $section_2_image_1 = 'edukasi terupdate.png';
                $section_2_title_2 = 'Pembicara Professional';
                $section_2_description_2 = 'Dipandu langsung oleh Professional dan Expert Trader';
                $section_2_image_2 = 'Pembicara Professional.png';
                $section_2_title_3 = 'Edukasi Online';
                $section_2_description_3 = 'Edukasi secara online yang dapat diikuti kapan pun dan dimana pun';
                $section_2_image_3 = 'Edukasi Online.png';
                $section_2_title_4 = 'TPFX Support 24 Jam';
                $section_2_description_4 = 'TPFx yang siap membantu sampai 24 jam';
                $section_2_image_4 = '24 hours support.png';
                $section_2_title_5 = 'Edukasi Nasabah';
                $section_2_description_5 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemulal';
                $section_2_image_5 = 'Image Edukasi.png';
                $section_2_title_6 = 'Webinar dengan Pialang Terdaftar';
                $section_2_description_6 = 'Webinar dan Seminar dengan pialang legal, teregulasi dan terdaftar oleh BAPPEBTI';
                $section_2_image_6 = 'Image Terdaftar.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case 'email':
                        $source = 338867;
                        break;
                    case 'google':
                        $source = 338884;
                        break;
                    case 'igfeed':
                        $source = 338889;
                        break;
                    case ('social'):
                        $source = 338908;
                        break;
                    case ('facebook'):
                        $source = 338908;
                        break;
                    case 'whatsapp':
                        $source = 338923;
                        break;
                }
                break;
            case 'zerozerozero':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'zero-lp-d.jpg';
                $banner_image_mobile = 'zero-lp-m.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'TPFx memberikan peluang peningkatan profit trading bagi nasabah kami.
Kini anda dapat menikmati trading tanpa biaya inap, tanpa komisi dan juga kondisi spread yang stabil';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Kami menawarkan';
                 $section_2_subheading = 'kondisi trading paling menarik';
                $section_2_title_1 = 'Broker Teregulasi';
                $section_2_description_1 = 'TPFx pialang resmi dan teregulasi oleh BAPPEBTI. Trading aman dengan Segregated Account';
                $section_2_image_1 = 'broker-teregulasi.jpg';
                $section_2_title_2 = 'Spread Start From Zero';
                $section_2_description_2 = 'Transaksi dengan spread paling rendah, kompetitif dan stabil mulai 0';
                $section_2_image_2 = 'spread-nol.jpg';
                $section_2_title_3 = 'Bebas biaya Tersembunyi';
                $section_2_description_3 = 'Tingkatkan profit dengan fasilitas biaya tersembunyi bebas komisi dan bebas swap';
                $section_2_image_3 = 'bebas-biaya.jpg';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'edukasi-trading.jpg';
                $section_2_title_5 = 'Tools Trading';
                $section_2_description_5 = 'Fasilitas Trading Central yang memberikan analisa terupdate dan trading layak nya professional';
                $section_2_image_5 = 'tools-trading.jpg';
                $section_2_title_6 = 'Full Support Team TPFx';
                $section_2_description_6 = 'Team kami siap membantu anda sampai 24 jam';
                $section_2_image_6 = 'full-support.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Edukasi Nasabah';
                $section_4_description_1 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_4_subheading_2 = 'TPFx Support 24 Jam';
                $section_4_description_2 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_4_subheading_3 = 'Pencairan Dana Cepat';
                $section_4_description_3 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='Click Google Zerozerozero';
                break;
                
                case 'pmax':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'zero-lp-d.jpg';
                $banner_image_mobile = 'zero-lp-m.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'TPFx memberikan peluang peningkatan profit trading bagi nasabah kami.
Kini anda dapat menikmati trading tanpa biaya inap, tanpa komisi dan juga kondisi spread yang stabil';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Kami menawarkan';
                 $section_2_subheading = 'kondisi trading paling menarik';
                $section_2_title_1 = 'Broker Teregulasi';
                $section_2_description_1 = 'TPFx pialang resmi dan teregulasi oleh BAPPEBTI. Trading aman dengan Segregated Account';
                $section_2_image_1 = 'broker-teregulasi.jpg';
                $section_2_title_2 = 'Spread Start From Zero';
                $section_2_description_2 = 'Transaksi dengan spread paling rendah, kompetitif dan stabil mulai 0';
                $section_2_image_2 = 'spread-nol.jpg';
                $section_2_title_3 = 'Bebas biaya Tersembunyi';
                $section_2_description_3 = 'Tingkatkan profit dengan fasilitas biaya tersembunyi bebas komisi dan bebas swap';
                $section_2_image_3 = 'bebas-biaya.jpg';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'edukasi-trading.jpg';
                $section_2_title_5 = 'Tools Trading';
                $section_2_description_5 = 'Fasilitas Trading Central yang memberikan analisa terupdate dan trading layak nya professional';
                $section_2_image_5 = 'tools-trading.jpg';
                $section_2_title_6 = 'Full Support Team TPFx';
                $section_2_description_6 = 'Team kami siap membantu anda sampai 24 jam';
                $section_2_image_6 = 'full-support.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Edukasi Nasabah';
                $section_4_description_1 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_4_subheading_2 = 'TPFx Support 24 Jam';
                $section_4_description_2 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_4_subheading_3 = 'Pencairan Dana Cepat';
                $section_4_description_3 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='google banner pmax';
                break;
                
                case 'tiktokreach':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'zero-lp-d.jpg';
                $banner_image_mobile = 'zero-lp-m.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'TPFx memberikan peluang peningkatan profit trading bagi nasabah kami.
Kini anda dapat menikmati trading tanpa biaya inap, tanpa komisi dan juga kondisi spread yang stabil';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Kami menawarkan';
                 $section_2_subheading = 'kondisi trading paling menarik';
                $section_2_title_1 = 'Broker Teregulasi';
                $section_2_description_1 = 'TPFx pialang resmi dan teregulasi oleh BAPPEBTI. Trading aman dengan Segregated Account';
                $section_2_image_1 = 'broker-teregulasi.jpg';
                $section_2_title_2 = 'Spread Start From Zero';
                $section_2_description_2 = 'Transaksi dengan spread paling rendah, kompetitif dan stabil mulai 0';
                $section_2_image_2 = 'spread-nol.jpg';
                $section_2_title_3 = 'Bebas biaya Tersembunyi';
                $section_2_description_3 = 'Tingkatkan profit dengan fasilitas biaya tersembunyi bebas komisi dan bebas swap';
                $section_2_image_3 = 'bebas-biaya.jpg';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'edukasi-trading.jpg';
                $section_2_title_5 = 'Tools Trading';
                $section_2_description_5 = 'Fasilitas Trading Central yang memberikan analisa terupdate dan trading layak nya professional';
                $section_2_image_5 = 'tools-trading.jpg';
                $section_2_title_6 = 'Full Support Team TPFx';
                $section_2_description_6 = 'Team kami siap membantu anda sampai 24 jam';
                $section_2_image_6 = 'full-support.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Edukasi Nasabah';
                $section_4_description_1 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_4_subheading_2 = 'TPFx Support 24 Jam';
                $section_4_description_2 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_4_subheading_3 = 'Pencairan Dana Cepat';
                $section_4_description_3 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='tiktok videoaswin tiktokreach';
                break;


                case 'zerofollower':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'zero-lp-d.jpg';
                $banner_image_mobile = 'zero-lp-m.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'TPFx memberikan peluang peningkatan profit trading bagi nasabah kami.
Kini anda dapat menikmati trading tanpa biaya inap, tanpa komisi dan juga kondisi spread yang stabil';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Kami menawarkan';
                 $section_2_subheading = 'kondisi trading paling menarik';
                $section_2_title_1 = 'Broker Teregulasi';
                $section_2_description_1 = 'TPFx pialang resmi dan teregulasi oleh BAPPEBTI. Trading aman dengan Segregated Account';
                $section_2_image_1 = 'broker-teregulasi.jpg';
                $section_2_title_2 = 'Spread Start From Zero';
                $section_2_description_2 = 'Transaksi dengan spread paling rendah, kompetitif dan stabil mulai 0';
                $section_2_image_2 = 'spread-nol.jpg';
                $section_2_title_3 = 'Bebas biaya Tersembunyi';
                $section_2_description_3 = 'Tingkatkan profit dengan fasilitas biaya tersembunyi bebas komisi dan bebas swap';
                $section_2_image_3 = 'bebas-biaya.jpg';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'edukasi-trading.jpg';
                $section_2_title_5 = 'Tools Trading';
                $section_2_description_5 = 'Fasilitas Trading Central yang memberikan analisa terupdate dan trading layak nya professional';
                $section_2_image_5 = 'tools-trading.jpg';
                $section_2_title_6 = 'Full Support Team TPFx';
                $section_2_description_6 = 'Team kami siap membantu anda sampai 24 jam';
                $section_2_image_6 = 'full-support.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Edukasi Nasabah';
                $section_4_description_1 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_4_subheading_2 = 'TPFx Support 24 Jam';
                $section_4_description_2 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_4_subheading_3 = 'Pencairan Dana Cepat';
                $section_4_description_3 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='twitterlpclicklpzero banner zerofollower';
                break;

                case 'Websitetraffic':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'zero-lp-d.jpg';
                $banner_image_mobile = 'zero-lp-m.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'TPFx memberikan peluang peningkatan profit trading bagi nasabah kami.
Kini anda dapat menikmati trading tanpa biaya inap, tanpa komisi dan juga kondisi spread yang stabil';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Kami menawarkan';
                 $section_2_subheading = 'kondisi trading paling menarik';
                $section_2_title_1 = 'Broker Teregulasi';
                $section_2_description_1 = 'TPFx pialang resmi dan teregulasi oleh BAPPEBTI. Trading aman dengan Segregated Account';
                $section_2_image_1 = 'broker-teregulasi.jpg';
                $section_2_title_2 = 'Spread Start From Zero';
                $section_2_description_2 = 'Transaksi dengan spread paling rendah, kompetitif dan stabil mulai 0';
                $section_2_image_2 = 'spread-nol.jpg';
                $section_2_title_3 = 'Bebas biaya Tersembunyi';
                $section_2_description_3 = 'Tingkatkan profit dengan fasilitas biaya tersembunyi bebas komisi dan bebas swap';
                $section_2_image_3 = 'bebas-biaya.jpg';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'edukasi-trading.jpg';
                $section_2_title_5 = 'Tools Trading';
                $section_2_description_5 = 'Fasilitas Trading Central yang memberikan analisa terupdate dan trading layak nya professional';
                $section_2_image_5 = 'tools-trading.jpg';
                $section_2_title_6 = 'Full Support Team TPFx';
                $section_2_description_6 = 'Team kami siap membantu anda sampai 24 jam';
                $section_2_image_6 = 'full-support.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Edukasi Nasabah';
                $section_4_description_1 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_4_subheading_2 = 'TPFx Support 24 Jam';
                $section_4_description_2 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_4_subheading_3 = 'Pencairan Dana Cepat';
                $section_4_description_3 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='google video Websitetraffic';
                break;

                case 'Searchbrandedkeyword':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'zero-lp-d.jpg';
                $banner_image_mobile = 'zero-lp-m.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'TPFx memberikan peluang peningkatan profit trading bagi nasabah kami.
Kini anda dapat menikmati trading tanpa biaya inap, tanpa komisi dan juga kondisi spread yang stabil';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Kami menawarkan';
                 $section_2_subheading = 'kondisi trading paling menarik';
                $section_2_title_1 = 'Broker Teregulasi';
                $section_2_description_1 = 'TPFx pialang resmi dan teregulasi oleh BAPPEBTI. Trading aman dengan Segregated Account';
                $section_2_image_1 = 'broker-teregulasi.jpg';
                $section_2_title_2 = 'Spread Start From Zero';
                $section_2_description_2 = 'Transaksi dengan spread paling rendah, kompetitif dan stabil mulai 0';
                $section_2_image_2 = 'spread-nol.jpg';
                $section_2_title_3 = 'Bebas biaya Tersembunyi';
                $section_2_description_3 = 'Tingkatkan profit dengan fasilitas biaya tersembunyi bebas komisi dan bebas swap';
                $section_2_image_3 = 'bebas-biaya.jpg';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'edukasi-trading.jpg';
                $section_2_title_5 = 'Tools Trading';
                $section_2_description_5 = 'Fasilitas Trading Central yang memberikan analisa terupdate dan trading layak nya professional';
                $section_2_image_5 = 'tools-trading.jpg';
                $section_2_title_6 = 'Full Support Team TPFx';
                $section_2_description_6 = 'Team kami siap membantu anda sampai 24 jam';
                $section_2_image_6 = 'full-support.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Edukasi Nasabah';
                $section_4_description_1 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_4_subheading_2 = 'TPFx Support 24 Jam';
                $section_4_description_2 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_4_subheading_3 = 'Pencairan Dana Cepat';
                $section_4_description_3 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='google keyword Searchbrandedkeyword';
                break;
case 'displaybasiczero':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'zero-lp-d.jpg';
                $banner_image_mobile = 'zero-lp-m.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'TPFx memberikan peluang peningkatan profit trading bagi nasabah kami.
Kini anda dapat menikmati trading tanpa biaya inap, tanpa komisi dan juga kondisi spread yang stabil';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Kami menawarkan';
                 $section_2_subheading = 'kondisi trading paling menarik';
                $section_2_title_1 = 'Broker Teregulasi';
                $section_2_description_1 = 'TPFx pialang resmi dan teregulasi oleh BAPPEBTI. Trading aman dengan Segregated Account';
                $section_2_image_1 = 'broker-teregulasi.jpg';
                $section_2_title_2 = 'Spread Start From Zero';
                $section_2_description_2 = 'Transaksi dengan spread paling rendah, kompetitif dan stabil mulai 0';
                $section_2_image_2 = 'spread-nol.jpg';
                $section_2_title_3 = 'Bebas biaya Tersembunyi';
                $section_2_description_3 = 'Tingkatkan profit dengan fasilitas biaya tersembunyi bebas komisi dan bebas swap';
                $section_2_image_3 = 'bebas-biaya.jpg';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'edukasi-trading.jpg';
                $section_2_title_5 = 'Tools Trading';
                $section_2_description_5 = 'Fasilitas Trading Central yang memberikan analisa terupdate dan trading layak nya professional';
                $section_2_image_5 = 'tools-trading.jpg';
                $section_2_title_6 = 'Full Support Team TPFx';
                $section_2_description_6 = 'Team kami siap membantu anda sampai 24 jam';
                $section_2_image_6 = 'full-support.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Edukasi Nasabah';
                $section_4_description_1 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_4_subheading_2 = 'TPFx Support 24 Jam';
                $section_4_description_2 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_4_subheading_3 = 'Pencairan Dana Cepat';
                $section_4_description_3 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='google banner displaybasiczero';
                break;

                case 'displaybasicwandi':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'zero-lp-d.jpg';
                $banner_image_mobile = 'zero-lp-m.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'TPFx memberikan peluang peningkatan profit trading bagi nasabah kami.
Kini anda dapat menikmati trading tanpa biaya inap, tanpa komisi dan juga kondisi spread yang stabil';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Kami menawarkan';
                 $section_2_subheading = 'kondisi trading paling menarik';
                $section_2_title_1 = 'Broker Teregulasi';
                $section_2_description_1 = 'TPFx pialang resmi dan teregulasi oleh BAPPEBTI. Trading aman dengan Segregated Account';
                $section_2_image_1 = 'broker-teregulasi.jpg';
                $section_2_title_2 = 'Spread Start From Zero';
                $section_2_description_2 = 'Transaksi dengan spread paling rendah, kompetitif dan stabil mulai 0';
                $section_2_image_2 = 'spread-nol.jpg';
                $section_2_title_3 = 'Bebas biaya Tersembunyi';
                $section_2_description_3 = 'Tingkatkan profit dengan fasilitas biaya tersembunyi bebas komisi dan bebas swap';
                $section_2_image_3 = 'bebas-biaya.jpg';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'edukasi-trading.jpg';
                $section_2_title_5 = 'Tools Trading';
                $section_2_description_5 = 'Fasilitas Trading Central yang memberikan analisa terupdate dan trading layak nya professional';
                $section_2_image_5 = 'tools-trading.jpg';
                $section_2_title_6 = 'Full Support Team TPFx';
                $section_2_description_6 = 'Team kami siap membantu anda sampai 24 jam';
                $section_2_image_6 = 'full-support.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Edukasi Nasabah';
                $section_4_description_1 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_4_subheading_2 = 'TPFx Support 24 Jam';
                $section_4_description_2 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_4_subheading_3 = 'Pencairan Dana Cepat';
                $section_4_description_3 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='google banner displaybasicwandi';
                break;

                case 'mediaplacement':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'zero-lp-d.jpg';
                $banner_image_mobile = 'zero-lp-m.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'TPFx memberikan peluang peningkatan profit trading bagi nasabah kami.
Kini anda dapat menikmati trading tanpa biaya inap, tanpa komisi dan juga kondisi spread yang stabil';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Kami menawarkan';
                 $section_2_subheading = 'kondisi trading paling menarik';
                $section_2_title_1 = 'Broker Teregulasi';
                $section_2_description_1 = 'TPFx pialang resmi dan teregulasi oleh BAPPEBTI. Trading aman dengan Segregated Account';
                $section_2_image_1 = 'broker-teregulasi.jpg';
                $section_2_title_2 = 'Spread Start From Zero';
                $section_2_description_2 = 'Transaksi dengan spread paling rendah, kompetitif dan stabil mulai 0';
                $section_2_image_2 = 'spread-nol.jpg';
                $section_2_title_3 = 'Bebas biaya Tersembunyi';
                $section_2_description_3 = 'Tingkatkan profit dengan fasilitas biaya tersembunyi bebas komisi dan bebas swap';
                $section_2_image_3 = 'bebas-biaya.jpg';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'edukasi-trading.jpg';
                $section_2_title_5 = 'Tools Trading';
                $section_2_description_5 = 'Fasilitas Trading Central yang memberikan analisa terupdate dan trading layak nya professional';
                $section_2_image_5 = 'tools-trading.jpg';
                $section_2_title_6 = 'Full Support Team TPFx';
                $section_2_description_6 = 'Team kami siap membantu anda sampai 24 jam';
                $section_2_image_6 = 'full-support.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Edukasi Nasabah';
                $section_4_description_1 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_4_subheading_2 = 'TPFx Support 24 Jam';
                $section_4_description_2 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_4_subheading_3 = 'Pencairan Dana Cepat';
                $section_4_description_3 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='google banner mediaplacement';
                break;

                case 'gold012':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'zero-lp-d.jpg';
                $banner_image_mobile = 'zero-lp-m.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'TPFx memberikan peluang peningkatan profit trading bagi nasabah kami.
Kini anda dapat menikmati trading tanpa biaya inap, tanpa komisi dan juga kondisi spread yang stabil';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Kami menawarkan';
                 $section_2_subheading = 'kondisi trading paling menarik';
                $section_2_title_1 = 'Broker Teregulasi';
                $section_2_description_1 = 'TPFx pialang resmi dan teregulasi oleh BAPPEBTI. Trading aman dengan Segregated Account';
                $section_2_image_1 = 'broker-teregulasi.jpg';
                $section_2_title_2 = 'Spread Start From Zero';
                $section_2_description_2 = 'Transaksi dengan spread paling rendah, kompetitif dan stabil mulai 0';
                $section_2_image_2 = 'spread-nol.jpg';
                $section_2_title_3 = 'Bebas biaya Tersembunyi';
                $section_2_description_3 = 'Tingkatkan profit dengan fasilitas biaya tersembunyi bebas komisi dan bebas swap';
                $section_2_image_3 = 'bebas-biaya.jpg';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'edukasi-trading.jpg';
                $section_2_title_5 = 'Tools Trading';
                $section_2_description_5 = 'Fasilitas Trading Central yang memberikan analisa terupdate dan trading layak nya professional';
                $section_2_image_5 = 'tools-trading.jpg';
                $section_2_title_6 = 'Full Support Team TPFx';
                $section_2_description_6 = 'Team kami siap membantu anda sampai 24 jam';
                $section_2_image_6 = 'full-support.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Edukasi Nasabah';
                $section_4_description_1 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_4_subheading_2 = 'TPFx Support 24 Jam';
                $section_4_description_2 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_4_subheading_3 = 'Pencairan Dana Cepat';
                $section_4_description_3 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='google banner gold012';
                break;

            case 'TPFx Lead Gen':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'banner-zerozerozero-desktop-compress-1.jpg';
                $banner_image_mobile = 'banner-zerozerozero-mobile-compress-1.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case 'mgid.com':
                        $source = 356609;
                        break;
                }
                break;
            case 'TPFx_Lead_Gen_Desktop':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'banner-zerozerozero-desktop-compress-1.jpg';
                $banner_image_mobile = 'banner-zerozerozero-mobile-compress-1.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case 'mgid.com':
                        $source = 356609;
                        break;
                }
                break;
            case 'formebook':
                $download_button = 'DOWNLOAD EBOOK';
                $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                $form_subheading = '';

                $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                $banner_image_desktop = 'ebook-desktop-compress-1.jpg';
                $banner_image_mobile = 'ebook-mobile-compress-1.jpg';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case ('facebook'):
                        $source = 350434;
                        break;
                    case ('social'):
                        $source = 350434;
                        break;
                }
                break;
            case 'formgold015':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Emas Spread Rendah';

                $form_heading = 'Dapatkan Income Harian';
                $form_subheading = 'Trading Gold Spread Mulai 0,15';

                $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                $banner_image_desktop = 'gold015-desktop-compress-1.jpg';
                $banner_image_mobile = 'gold015-mobile-compress-1.jpg';

                $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Emas Online';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case ('facebook'):
                        $source = 342996;
                        break;
                    case ('social'):
                        $source = 342996;
                        break;
                }
                break;
            case 'searchacuity':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Emas Spread Rendah';

                $form_heading = 'Dapatkan Income Harian';
                $form_subheading = 'Trading Gold Spread Mulai 0,15';

                $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                $banner_image_desktop = 'gold015-desktop-compress-1.jpg';
                $banner_image_mobile = 'gold015-mobile-compress-1.jpg';

                $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Emas Online';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case 'google':
                        $source = 338878;
                        break;
                }
                break;
            case 'searchcashback20rewards':
                $download_button = 'Promo Reward Emas';
                $title = 'Promo Reward Emas';

                $form_heading = 'Promo Reward Emas';
                $form_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';

                $section_1_heading = 'Promo Reward Emas';
                $section_1_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $banner_image_desktop = 'reward-emas-desktop-rev1.jpg';
                $banner_image_mobile = 'reward-emas-mobile-rev1.jpg';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Trading Central';
                $section_2_description_4 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                $section_2_image_4 = 'acuity-compress-1.jpg';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Promo Reward Emas Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';

                $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                $reward_cashback_row = 0;

                switch ($utm_source) {
                    case 'google':
                        $source = 353624;
                        break;
                }
                break;
            case 'searchebook':
                $download_button = 'DOWNLOAD EBOOK';
                $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                $form_subheading = '';

                $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                $banner_image_desktop = 'ebook-desktop-compress-1.jpg';
                $banner_image_mobile = 'ebook-mobile-compress-1.jpg';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case 'google':
                        $source = 338879;
                        break;
                }
                break;
            case 'searchgold015':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Emas Spread Rendah';

                $form_heading = 'Dapatkan Income Harian';
                $form_subheading = 'Trading Gold Spread Mulai 0,15';

                $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                $banner_image_desktop = 'gold015-desktop-compress-1.jpg';
                $banner_image_mobile = 'gold015-mobile-compress-1.jpg';

                $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Emas Online';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                switch ($utm_source) {
                    case 'google':
                        $source = 338881;
                        break;
                }
                break;
            case 'searchib':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Introducing Broker TPFx';

                $form_heading = '';
                $form_subheading = '';

                $banner_image_desktop = 'Banner_IB_rev.jpg';
                $banner_image_mobile = 'Banner_IB_rev.jpg';

                $section_1_heading = 'Introducing Broker TPFx';
                $section_1_subheading = 'Program kemitraan dan peroleh komisi dari setiap perdagangan klien anda';
                $section_1_row_heading = '';

                $section_2_heading = 'Mengapa Menjadi IB di TPFx?';
                $section_2_title_1 = 'Komisi IB Terbesar';
                $section_2_description_1 = 'Komisi IB Terbesar nomor 1 di Indonesia';
                $section_2_image_1 = 'komisi_ib_terbesar.jpg';
                $section_2_title_2 = 'Broker Teregulasi';
                $section_2_description_2 = 'Bermitra dengan broker yang sudah terdaftar dan teregulasi oleh BAPPEBTI';
                $section_2_image_2 = 'Broker_Teregulasi.jpg';
                $section_2_title_3 = 'Full Support Team TPFx';
                $section_2_description_3 = 'Team Kami siap membantu Anda agar karir Anda semakin berkembang';
                $section_2_image_3 = 'tpfx_support.jpg';
                $section_2_title_4 = 'Pencairan Komisi Cepat';
                $section_2_description_4 = 'Penarikan dan pencairan komisi cepat di hari yang sama';
                $section_2_image_4 = 'Pencairan_Komisi_Cepat.jpg';
                $section_2_title_5 = 'Platform Professional';
                $section_2_description_5 = 'Eksekusi cepat dengan platform professional';
                $section_2_image_5 = 'professional_platform_rev.jpg';
                $section_2_title_6 = 'Trading Central';
                $section_2_description_6 = 'Transaksi menggunakan Trading Central dan hasilkan profit dengan maksimal';
                $section_2_image_6 = 'acuity-compress-1.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';
                $section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';


                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 0;

                switch ($utm_source) {
                    case ('google'):
                        $source = 387596;
                        break;
                }
                break;
            case 'searchkompetitor':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'banner-zerozerozero-desktop-compress-1.jpg';
                $banner_image_mobile = 'banner-zerozerozero-mobile-compress-1.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='Click Google Search Kompetitor';
                break;

            case 'searchzerozerozero':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'banner-zerozerozero-desktop-compress-1.jpg';
                $banner_image_mobile = 'banner-zerozerozero-mobile-compress-1.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='Click Google Search Zero Zero Zero';
                break;

                case 'trafficawareness':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'banner-zerozerozero-desktop-compress-1.jpg';
                $banner_image_mobile = 'banner-zerozerozero-mobile-compress-1.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='Click Google Traffic Awareness';
                break;

                case 'tradingview':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'banner-zerozerozero-desktop-compress-1.jpg';
                $banner_image_mobile = 'banner-zerozerozero-mobile-compress-1.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='Click Google Trading view';
                break;

                case 'investingcom':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'banner-zerozerozero-desktop-compress-1.jpg';
                $banner_image_mobile = 'banner-zerozerozero-mobile-compress-1.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='Click Google investing';
                break;
                case 'seputarforex':
                $download_button = 'DAFTAR SEKARANG';
                $title = 'Trading Bebas Biaya Tersembunyi';

                $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                $form_subheading = '';

                $banner_image_desktop = 'banner-zerozerozero-desktop-compress-1.jpg';
                $banner_image_mobile = 'banner-zerozerozero-mobile-compress-1.jpg';

                $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                $section_2_heading = 'Mengapa Trading Bersama TPFx ?';
                $section_2_title_1 = 'Spread Mulai 0,15';
                $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                $section_2_image_1 = 'image_spread-compress-1.png';
                $section_2_title_2 = 'Leverage sampai 1 : 400';
                $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                $section_2_image_2 = 'image_leverage-compress-1.png';
                $section_2_title_3 = 'Platform Professional';
                $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                $section_2_image_3 = 'mt4-compress-1.png';
                $section_2_title_4 = 'Edukasi Nasabah';
                $section_2_description_4 = 'Program Edukasi dari Para Trainer dan Praktisi bagi Nasabah Pemula';
                $section_2_image_4 = 'image_education-compress-1.png';
                $section_2_title_5 = 'TPFx Support 24 Jam';
                $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                $section_2_image_5 = 'image_support-compress-1.png';
                $section_2_title_6 = 'Pencairan Dana Cepat';
                $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                $section_2_image_6 = 'image_dana-compress-1.png';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='Click Google Seputar Forex';
                break;
                case 'tradingcentral':
                $download_button = 'INSTALL SEKARANG';
                $title = 'Trading Central Economic Insight';

                $form_heading = 'Buat Akun Sekarang';
                $form_subheading = '';

                $banner_image_desktop = 'banner-tc-desktop.jpg';
                $banner_image_mobile = 'banner-tc-mobile.jpg';

                $section_1_heading = 'Analisa Teknikal dan Riset Pasar Real Time Dengan Trading Central';
                $section_1_subheading = 'Dengan menggabungkan analisa berdasarkan riset pasar dan analisa teknikal menggunakan teknologi Alpha Generation yang memberikan Trading Signal terakurat secara real time. Maksimalkan profit dengan hasil trading terbaik menggunakan Trading Central';
                $section_1_row_heading = '';

                $section_2_heading = 'Berbagai keuntungan menggunakan Trading Central';
                $section_2_title_1 = 'Real Time Alpha Generation Expert Advisor';
                $section_2_description_1 = 'Dengan menggunakan 3 indikator utama yang mengidentifikasi peluang perdagangan dan memberi referensi peluang untuk masuk dan keluar pasar secara real time';
                $section_2_image_1 = 'alpha-generation.jpg';
                $section_2_title_2 = 'Terintegrasi ke Metatrader Anda';
                $section_2_description_2 = 'Permudah analisa dan transaksi secara real dengan sinyal trading yang terintegrasi langsung ke Metatrader anda';
                $section_2_image_2 = 'integrasi-mt.jpg';
                $section_2_title_3 = 'Premium Economic Calender Analysist';
                $section_2_description_3 = 'Dapatkan informasi dan pantau aktivitas pasar terbaru dari 38 negara yang berbeda yang berdasarkan hari, minggu, bulan dan rentang waktu tertentu.';
                $section_2_image_3 = 'economic-calendar.jpg';
                $section_2_title_4 = 'Intraday dan Daily Trading Signal';
                $section_2_description_4 = 'Fasilitas analisa secara teknikal secara online yang akan menganalisa level support dan resisten, serta memberikan prediksi intraday dan daily trading.';
                $section_2_image_4 = 'intraday.jpg';
                $section_2_title_5 = 'Economic Insight';
                $section_2_description_5 = 'Anda dapat memantau, mengantisipasi dan bertindak berdasarkan peristiwa ekonomi yang menggerakan pasar. Transaksi berdasarkan dampak dan volatilitas yang kuat di pasar.';
                $section_2_image_5 = 'economic-insight.jpg';
                $section_2_title_6 = 'Award Winning Technical Analysist';
                $section_2_description_6 = 'Pemenang penghargaan dalam Analisa Teknikal dan menjadi pemimpin dalam global dalam riset keuangan';
                $section_2_image_6 = 'award.jpg';
				$section_2_title_7 = 'Meliputi seluruh multi asset global';
                $section_2_description_7 = 'Meliputi seluruh analisa pasar multi asset global secara online selama 24 jam';
                $section_2_image_7 = 'cover.jpg';
				$section_2_title_8 = 'Tools Trading Para Treasury';
				$section_2_description_8 = 'Tools trading yang digunakan oleh Bank besar dunia untuk menganalisa seperti HSBC, Bank Of America, DBS, Royal Bank of Canada, National Australia Bank dll';
                $section_2_image_8 = 'treasury.jpg';
                $section_2_title_9 = 'Market Commentary';
				$section_2_description_9 = 'Tim Analisis yang memberikan informasi yang disertai dengan saran posisi menggunakan teknologi algo kuantum';
                $section_2_image_9 = 'market-com.jpg';

                $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                $section_4_heading = 'Keuntungan Trading Online Bersama TPFx';
                $section_4_subheading_1 = 'Likuiditas';
                $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                $section_4_subheading_2 = 'Leverage';
                $section_4_description_2 = 'Transaksi menggunakan margin yang lebih rendah dari nominal kontrak untuk open posisi';
                $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';
				$section_4_subheading_4 = 'Deposit Rendah';
                $section_4_description_4 = 'Memulai dengan modal rendah untuk pengalaman terbaik.';

                $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                $section_5_cta = 'DAFTAR SEKARANG';
                $reward_cashback_tnc = null;
                $reward_cashback_row = 1;

                $source='Click Google Trading Central';
                break;
        }

        $section_2_subheading = isset($section_2_subheading) ? $section_2_subheading : "";
        $section_4_subheading_4 = isset($section_4_subheading_4) ?
        $section_4_subheading_4 : "";
        $section_4_description_4 = isset($section_4_description_4) ?
        $section_4_description_4 : "";
        $section_2_title_7 = isset($section_2_title_7) ?
        $section_2_title_7 : "";
        $section_2_description_7 = isset($section_2_description_7) ?
        $section_2_description_7 : "";
        $section_2_image_7 = isset($section_2_image_7) ?
        $section_2_image_7 : "";

        $section_2_title_8 = isset($section_2_title_8) ?
        $section_2_title_8 : "";
        $section_2_description_8 = isset($section_2_description_8) ?
        $section_2_description_8 : "";
        $section_2_image_8 = isset($section_2_image_8) ?
        $section_2_image_8 : "";
        $section_2_title_9 = isset($section_2_title_9) ?
        $section_2_title_9 : "";
        $section_2_description_9 = isset($section_2_description_9) ?
        $section_2_description_9 : "";
        $section_2_image_9 = isset($section_2_image_9) ?
        $section_2_image_9 : "";

        return view('events.leads_form', compact(
            'source',
            'title',
            'download_button',
            'utm_campaign',
            'form_heading',
            'form_subheading',
            'section_1_heading',
            'section_1_subheading',
            'section_1_row_heading',
            'banner_image_desktop',
            'banner_image_mobile',
            'section_2_heading',
            'section_2_subheading',
            'section_2_title_1',
            'section_2_description_1',
            'section_2_title_2',
            'section_2_description_2',
            'section_2_title_3',
            'section_2_description_3',
            'section_2_title_4',
            'section_2_description_4',
            'section_2_title_5',
            'section_2_description_5',
            'section_2_title_6',
            'section_2_description_6',
            'section_2_title_7',
            'section_2_description_7',
            'section_2_title_8',
            'section_2_description_8',
            'section_2_title_9',
            'section_2_description_9',
            'section_2_image_1',
            'section_2_image_2',
            'section_2_image_3',
            'section_2_image_4',
            'section_2_image_5',
            'section_2_image_6',
            'section_2_image_7',
            'section_2_image_8',
            'section_2_image_9',
            'section_3_heading',
            'section_3_subheading',
            'section_4_heading',
            'section_4_subheading_1',
            'section_4_description_1',
            'section_4_subheading_2',
            'section_4_description_2',
            'section_4_subheading_3',
            'section_4_description_3',
            'section_4_subheading_4',
            'section_4_description_4',
            'section_5_heading',
            'section_5_cta',
            'reward_cashback_tnc',
            'reward_cashback_row'
        ));
    }

    public function thank_you()
    {
        return view('leads_form_success');
    }

    public function thank_you_ebook_basic()
    {
        return view('leads_form_success_ebook_basic');
    }

    public function import()
    {
        $leads = Import::get();
        foreach ($leads as $lead) {
            $source = 357555;
            $status = 344210;

            if($lead->id%14 == 0) {
                $assigned = 91075; // yahot
                $sales_name = "Yahot Marusaha";
            } else {
                switch ($lead->id % 7) {
                    case 0:
                        $assigned = 91071; // yafet
                        $sales_name = "Yafet Eleanore";
                        break;
                    case 1:
                        $assigned = 81868; // nita
                        $sales_name = "Hannita Batubara";
                        break;
                    case 2:
                        $assigned = 81850; // ale
                        $sales_name = "Ale Sandi Lowix";
                        break;
                    case 3:
                        $assigned = 81900; // ana
                        $sales_name = "Mulyanah";
                        break;
                    case 4:
                        $assigned = 83823; // ilham
                        $sales_name = "Ilham Akbar";
                        break;
                    case 5:
                        $assigned = 98686; // dani
                        $sales_name = "Mohamad Hardanih";
                        break;
                    case 6:
                        $assigned = 94940; // dean
                        $sales_name = "Dean Esteban";
                        break;
                    default:
                        $assigned = 80345;
                        $sales_name = "Admin";
                        break;
                }
            }

            $meta_account = isset($input['meta_account']) ? $input['meta_account'] : "";
            $size = isset($input['size']) ? $input['size'] : "";
            $meta_created_date = isset($input['meta_created_date']) ? $input['meta_created_date'] : "";
            $utm_campaign = isset($input['utm_campaign']) ? $input['utm_campaign'] : 'default';
            $sales_name = "";

            $form_params = [
                'email' => $lead->email,
                'name' => $lead->name,
                'phonenumber' => $lead->phone,
                'source' => $source,
                'status' => $status,
                'assigned' => $assigned,
                'utm_campaign' => $utm_campaign,
                'size' => $size,
                'meta_account' => $meta_account,
                'meta_created_date' => $meta_created_date,
                'sales_name' => $sales_name
            ];

            $qontak_controller = new QontakController;
            $qontak = $qontak_controller->qontak_store_contact($form_params);
            if (!$qontak) {
                continue;
            }
        }

        return 'ok';
    }

    public function reinput(){
        $leads = Lead::on('mysql_leads')->where('created_at', '>', '2022-05-11')->get();
        foreach($leads as $lead) {
            $parts = parse_url($lead->url);

            if(!array_key_exists('query', $parts)) {
                continue;
            }

            parse_str($parts['query'], $query);
            $utm_source = $query['utm_source'];
            $utm_campaign = $query['utm_campaign'];

            switch ($utm_campaign) {
                case 'acuity':
                    switch ($utm_source) {
                        case 'detik':
                            $source = 330205;
                            break;
                        case 'email':
                            $source = 338863;
                            break;
                        case 'fbpage':
                            $source = 338869;
                            break;
                        case 'google':
                            $source = 338874;
                            break;
                        case 'igbio':
                            $source = 338886;
                            break;
                        case 'linkedin':
                            $source = 338891;
                            break;
                        case 'mgid.com':
                            $source = 342998;
                            break;
                        case ('facebook'):
                            $source = 338903;
                            break;
                        case ('social'):
                            $source = 338903;
                            break;
                        case 'telegram':
                            $source = 338910;
                            break;
                        case 'tiktok':
                            $source = 338912;
                            break;
                        case 'twitter':
                            $source = 338917;
                            break;
                        case 'whatsapp':
                            $source = 338922;
                            break;
                    }
                    break;
                case 'cashback20rewards':
                    switch ($utm_source) {
                        case 'google':
                            $source = 353623;
                            break;
                        case 'outbrain':
                            $source = 362599;
                            break;
                        case ('facebook'):
                            $source = 353625;
                            break;
                        case ('social'):
                            $source = 353625;
                            break;
                        case 'twitter':
                            $source = 353626;
                            break;
                        case 'youtube':
                            $source = 358396;
                            break;
                        case 'email':
                            $source = 353627;
                            break;
                    }
                    break;
                case 'cashback20rewards':
                    switch ($utm_source) {
                        case 'google':
                            $source = 353623;
                            break;
                        case 'outbrain':
                            $source = 362599;
                            break;
                        case ('facebook'):
                            $source = 353625;
                            break;
                        case ('social'):
                            $source = 353625;
                            break;
                        case 'twitter':
                            $source = 353626;
                            break;
                        case 'youtube':
                            $source = 358396;
                            break;
                    }
                    break;
                case 'merdekatradefest':
                     $source='google banner merdekatradefest';
                    break;
                     case 'merdeka-tradefest':
                     $source='google banner merdeka-tradefest';
                    break;
                case 'ebook':
                 $source='twitter image ebook';
                break;
                case 'freeswap300':
                    switch ($utm_source) {
                        case 'advertorial':
                            $source = 330207;
                            break;
                        case 'detik':
                            $source = 338860;
                            break;
                        case 'email':
                            $source = 338865;
                            break;
                        case 'fbpage':
                            $source = 338871;
                            break;
                        case 'google':
                            $source = 338876;
                            break;
                        case 'igbio':
                            $source = 338888;
                            break;
                        case 'smsblast':
                            $source = 338900;
                            break;
                        case ('facebook'):
                            $source = 338905;
                            break;
                        case ('social'):
                            $source = 338905;
                            break;
                        case 'tiktok':
                            $source = 338914;
                            break;
                        case 'twitter':
                            $source = 338919;
                            break;
                    }
                    break;
                case 'freeswap300':
                    switch ($utm_source) {
                        case 'google':
                            $source = 338880;
                            break;
                    }
                    break;
                case 'gold015':
                    switch ($utm_source) {
                        case 'advertorial':
                            $source = 330210;
                            break;
                        case 'detik':
                            $source = 338861;
                            break;
                        case 'email':
                            $source = 338866;
                            break;
                        case 'fbpage':
                            $source = 338872;
                            break;
                        case 'google':
                            $source = 338877;
                            break;
                        case 'igbio':
                            $source = 338889;
                            break;
                        case ('facebook'):
                            $source = 338906;
                            break;
                        case ('social'):
                            $source = 338906;
                            break;
                        case 'tiktok':
                            $source = 338915;
                            break;
                        case 'twitter':
                            $source = 339036;
                            break;
                        case 'smsblast':
                            $source = 338901;
                            break;
                    }
                    break;
                case 'seminar':
                    switch ($utm_source) {
                        case ('facebook'):
                            $source = 398829;
                            break;
                        case ('social'):
                            $source = 398829;
                            break;
                        case ('google'):
                            $source = 398827;
                            break;
                        case ('igbio'):
                            $source = 398832;
                            break;
                        case ('mgid.com'):
                            $source = 398833;
                            break;
                        case ('news'):
                            $source = 398836;
                            break;
                        case ('outbrain'):
                            $source = 398835;
                            break;
                        case ('partipost'):
                            $source = 398834;
                            break;
                        case ('tiktok'):
                            $source = 398831;
                            break;
                        case ('twitter'):
                            $source = 398830;
                            break;
                        case ('youtube'):
                            $source = 398828;
                            break;
                    }
                    break;
                case 'webinar':
                    switch ($utm_source) {
                        case 'email':
                            $source = 338867;
                            break;
                        case 'google':
                            $source = 338884;
                            break;
                        case 'igfeed':
                            $source = 338889;
                            break;
                        case ('facebook'):
                            $source = 338908;
                            break;
                        case ('social'):
                            $source = 338908;
                            break;
                        case 'whatsapp':
                            $source = 338923;
                            break;
                    }
                    break;
                case 'zerozerozero':
                    $source='Click Google Search zerozerozero';
                break;
                case 'zerofollower':
                    $source='twitterlpclickzero banner zerofollower';
                break;
                 case 'Websitetraffic':
                    $source='google video Websitetraffic';
                break;

                case 'mediaplacement':
                    $source='google banner mediaplacement';
                break;

                case 'Searchbrandedkeyword':
                    $source='google keyword Searchbrandedkeyword';
                break;
                 case 'displaybasiczero':
                    $source='google banner displaybasiczero';
                break;

                 case 'displaybasicwandi':
                    $source='google banner displaybasicwandi';
                break;
                 case 'gold012':
                    $source='google banner gold012';
                break;
                case 'TPFx Lead Gen':
                    switch ($utm_source) {
                        case 'mgid.com':
                            $source = 356609;
                            break;
                    }
                    break;
                case 'TPFx_Lead_Gen_Desktop':
                    switch ($utm_source) {
                        case 'mgid.com':
                            $source = 356609;
                            break;
                    }
                    break;
                case 'formebook':
                    switch ($utm_source) {
                        case ('facebook'):
                            $source = 350434;
                            break;
                        case ('social'):
                            $source = 350434;
                            break;
                    }
                    break;
                case 'formgold015':
                    switch ($utm_source) {
                        case ('facebook'):
                            $source = 342996;
                            break;
                        case ('social'):
                            $source = 342996;
                            break;
                    }
                    break;
                case 'searchacuity':
                    switch ($utm_source) {
                        case 'google':
                            $source = 338878;
                            break;
                    }
                    break;
                case 'searchcashback20rewards':
                    switch ($utm_source) {
                        case 'google':
                            $source = 353624;
                            break;
                    }
                    break;
                case 'searchebook':
                    switch ($utm_source) {
                        case 'google':
                            $source = 338879;
                            break;
                    }
                    break;
                case 'searchgold015':
                    switch ($utm_source) {
                        case 'google':
                            $source = 338881;
                            break;
                    }
                    break;
                case 'searchkompetitor':
                   $source='Click Google Search Kompetitor';
                break;

                    break;
                case 'searchzerozerozero':
                    switch ($utm_source) {
                        case 'google':
                            $source = 338883;
                            break;
                    }
                    break;
            }

            $status = 344210;

            if($lead->id%14 == 0) {
                $assigned = 91075; // yahot
                $sales_name = "Yahot Marusaha";
            } else {
                switch ($lead->id % 7) {
                    case 0:
                        $assigned = 91071; // yafet
                        $sales_name = "Yafet Eleanore";
                        break;
                    case 1:
                        $assigned = 81868; // nita
                        $sales_name = "Hannita Batubara";
                        break;
                    case 2:
                        $assigned = 81850; // ale
                        $sales_name = "Ale Sandi Lowix";
                        break;
                    case 3:
                        $assigned = 81900; // ana
                        $sales_name = "Mulyanah";
                        break;
                    case 4:
                        $assigned = 83823; // ilham
                        $sales_name = "Ilham Akbar";
                        break;
                    case 5:
                        $assigned = 98686; // dani
                        $sales_name = "Mohamad Hardanih";
                        break;
                    case 6:
                        $assigned = 94940; // dean
                        $sales_name = "Dean Esteban";
                        break;
                    default:
                        $assigned = 80345;
                        $sales_name = "Admin";
                        break;
                }
            }

            $meta_account = isset($input['meta_account']) ? $input['meta_account'] : "";
            $size = isset($input['size']) ? $input['size'] : "";
            $meta_created_date = isset($input['meta_created_date']) ? $input['meta_created_date'] : "";
            $utm_campaign = isset($input['utm_campaign']) ? $input['utm_campaign'] : 'default';
            $sales_name = "";

            $form_params = [
                'email' => $lead->email,
                'name' => $lead->name,
                'phonenumber' => $lead->phone,
                'source' => $source,
                'status' => $status,
                'assigned' => $assigned,
                'utm_campaign' => $utm_campaign,
                'size' => $size,
                'meta_account' => $meta_account,
                'meta_created_date' => $meta_created_date,
                'sales_name' => $sales_name
            ];

            $qontak_controller = new QontakController;
            $qontak = $qontak_controller->qontak_store_contact($form_params);
            if(!$qontak) {
                continue;
            }
        }

        return 'ok';
    }
}
