<?php

namespace App\Http\Controllers;

use App\Mail\RegisterWebinar;
use App\Models\Lead;
use App\Models\Import;
use App\Models\QontakToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use stdClass;

class EventController extends Controller
{

    public function store_leads(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:leads,email',
            'phonenumber' => 'required|string|max:255',
            'source' => 'required',
            'status' => 'required',
        ]);
        $input = $request->all();

        $lead_id = Lead::insertGetId([
            'name' => $input['name'],
            'email' => $input['email'],
            'phone' => $input['phonenumber'],
            'url' => url()->previous()
        ]);

        if(!$lead_id) {
            abort(500, 'Error input leads to DB');
        }

        switch($lead_id % 3) {
            case 0:
                $assigned = 81850;
                break;
            case 1:
                $assigned = 81900;
                break;
            case 2:
                $assigned = 81868;
                break;
            default:
                $assigned = 80345;
                break;
        }
        $form_params = [
            'email' => $input['email'],
            'name' => $input['name'],
            'phonenumber' => $input['phonenumber'],
            'source' => $input['source'],
            'status' => $input['status'],
            'assigned' => $assigned
        ];

        //$crm = $this->crm($form_params);
        $qontak = $this->qontak_store_contact($form_params);

        if($input['utm_campaign'] == 'webinar'){
            Mail::to($input['email'])->send(new RegisterWebinar());
        }

        if($input['utm_campaign'] == 'ebook'){
            return redirect()->route('events.thank_you_ebook_basic');
        } else {
            return redirect()->route('events.thank_you');
        }
    }

    public function qontak_login() {
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'http_errors' => false
        ]);

        $data = [
            'grant_type' => "password",
            'username' => "admin_tpfx",
            'password' => "2NQ,JnLa94}k-=t#"
        ];
        $response = $client->request('POST', 'https://www.qontak.com/oauth/token', [
            'form_params' => $data
        ]);
        $auth = json_decode($response->getBody());

        $qontak_token_id = QontakToken::insertGetId([
            'access_token' => $auth->access_token,
            'refresh_token' => $auth->refresh_token,
        ]);

        return $auth;
    }

    public function qontak_store_contact($data)
    {
        $login = QontakToken::latest()->first();
        if($login == null) {
            $this->qontak_login();
            $login = QontakToken::latest()->first();
        }
        $headers = [
            'Authorization' => 'Bearer ' . $login->access_token,
        ];

        $new_contact = [
            "first_name" => $data['name'],
            "last_name" => "",
            "job_title" => "",
            "creator_id" => null,
            "creator_name" => "",
            "email" => $data['email'],
            "telephone" => $data['phonenumber'],
            "crm_status_id" => null,
            "city" => "",
            "zipcode" => "",
            "date_of_birth" => null,
            "crm_source_id" => $data['source'],
            "crm_gender_id" => null,
            "income" => "",
            "upload_id" => null,
            "customer_id" => "",
            "crm_company_id" => null,
            "crm_company_name" => null,
            "crm_deal_ids" => [],
            "crm_deal_name" => [],
            "additional_fields" => []
        ];

        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'allow_redirects' => false,
            'http_errors' => false
        ]);
        $response = $client->request('POST', 'https://www.qontak.com/api/v3.1/contacts', [
            'json' => $new_contact
        ]);

        if($response->getStatusCode() == 200) {
            return $this->qontak_store_leads($data, json_decode($response->getBody())->response->id);
        } elseif($response->getStatusCode() == 401) {
            $this->qontak_login();
            return $this->qontak_store_contact($data);
        } elseif($response->getStatusCode() == 422) {
            return null;
        }
    }

    public function qontak_store_leads($data, $contact_id)
    {
        $login = QontakToken::latest()->first();
        if($login == null) {
            $this->qontak_login();
            $login = QontakToken::latest()->first();
        }
        $headers = [
            'Authorization' => 'Bearer ' . $login->access_token,
        ];

        $new_deals = [
            "creator_id" => $data['assigned'],
            "name" => "Leads - " . $data['name'],
            "crm_pipeline_id" => 56932,
            "crm_stage_id" => 344210,
            "crm_source_id" => $data['source'],
            "start_date" => null,
            "expired_date" => null,
            "crm_priority_id" => null,
            "crm_priority_name" => null,
            "crm_company_id" => null,
            "crm_company_name" => null,
            "crm_lead_ids" => [$contact_id],
            "additional_fields" => [
                [
                    "id" => 4415397,
                    "value" => ""
                ],
                [
                    "id" => 4415398,
                    "value" => ""
                ],
                [
                    "id" => 4428993,
                    "value" => ""
                ]
            ]
        ];

        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'allow_redirects' => false,
            'http_errors' => false
        ]);
        $response = $client->request('POST', 'https://www.qontak.com/api/v3.1/deals', [
            'json' => $new_deals
        ]);

        if(isset($response) && $response->getStatusCode() == 200) {
            return true;
        } elseif($response->getStatusCode() == 401) {
            $this->qontak_login();
            return $this->qontak_store_leads($data, $contact_id);
        } elseif($response->getStatusCode() == 422) {
            return null;
        }
    }

    public function leads_form(Request $request)
    {
        $utm_source = $request->utm_source;
        $utm_campaign = $request->utm_campaign;
        $banner_image_desktop = null;
        $banner_image_mobile = null;
        switch($utm_source) {
            case 'google':
                switch($utm_campaign) {
                    case 'gold015':
                        $source = 338877;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Trading Emas Spread Rendah';

                        $form_heading = 'Dapatkan Income Harian';
                        $form_subheading = 'Trading Gold Spread Mulai 0,15';

                        $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                        $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                        $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                        $banner_image_desktop = 'gold015-desktop.jpg';
                        $banner_image_mobile = 'gold015-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                        $section_2_title_1 = 'Spread Mulai 0,15';
                        $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
                        $section_2_title_5 = 'TPFx Support 24 Jam';
                        $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                        $section_2_image_5 = 'image support.png';
                        $section_2_title_6 = 'Pencairan Dana Cepat';
                        $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                        $section_2_image_6 = 'image dana.png';

                        $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                        $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                        $section_4_heading = 'Keuntungan Trading Emas Online';
                        $section_4_subheading_1 = 'Likuiditas';
                        $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                        $section_4_subheading_2 = 'Leverage';
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'searchgold015':
                        $source = 338881;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Trading Emas Spread Rendah';

                        $form_heading = 'Dapatkan Income Harian';
                        $form_subheading = 'Trading Gold Spread Mulai 0,15';

                        $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                        $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                        $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                        $banner_image_desktop = 'gold015-desktop.jpg';
                        $banner_image_mobile = 'gold015-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                        $section_2_title_1 = 'Spread Mulai 0,15';
                        $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
                        $section_2_title_5 = 'TPFx Support 24 Jam';
                        $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                        $section_2_image_5 = 'image support.png';
                        $section_2_title_6 = 'Pencairan Dana Cepat';
                        $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                        $section_2_image_6 = 'image dana.png';

                        $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                        $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                        $section_4_heading = 'Keuntungan Trading Emas Online';
                        $section_4_subheading_1 = 'Likuiditas';
                        $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                        $section_4_subheading_2 = 'Leverage';
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'webinar':
                        $source = 338884;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Webinar';

                        $form_heading = 'Daftar Webinar';
                        $form_subheading = 'Ikuti Webinar & Belajar Trading Sekarang';

                        $banner_image_desktop = 'banner-20220415-desktop.jpg';
                        $banner_image_mobile = 'banner-20220415-mobile.jpg';

                        $section_1_heading = 'Cara Cuan Cepat Dengan Jurus  6 Pola Candle Price Action';
                        $section_1_subheading = 'Teknik Price Action Merupakan teknik dengan cara memantau pasar dan melihat pola-pola grafik yang ada di pasar. Ikuti webinar dan temukan 6 pola dalam candle price action sekarang';
                        $section_1_row_heading = 'Webinar khusus untuk mempelajari seputar dunia trading dan diselenggarakan secara online dengan materi menarik dan pembicara professional untuk  menghasilkan profit dan menjadikan Anda trader professional';

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
                        $section_2_title_5 = 'Edukasi Trading';
                        $section_2_description_5 = 'Edukasi dan analisa trading langsung dari professional';
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
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'freeswap300':
                        $source = 338876;
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
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'acuity':
                        $source = 338874;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Download Acuity pada Metatrader';

                        $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-acuity-desktop.jpg';
                        $banner_image_mobile = 'banner-acuity-mobile.jpg';

                        $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                        $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                        $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                        $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                        $section_2_title_1 = 'ACUITY';
                        $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_1 = 'acuity.jpg';
                        $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                        $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                        $section_2_image_2 = 'winner-acuity.jpg';
                        $section_2_title_3 = 'Platform Yang Sama';
                        $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                        $section_2_image_3 = 'platform-acuity.jpg';
                        $section_2_title_4 = 'Sentimen Market Alert';
                        $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                        $section_2_image_4 = 'sentimen-acuity.jpg';
                        $section_2_title_5 = 'Strategi Partner';
                        $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                        $section_2_image_5 = 'strategi-acuity.jpg';
                        $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                        $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                        $section_2_image_6 = 'kalender-acuity.jpg';

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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'searchacuity':
                        $source = 338878;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Download Acuity pada Metatrader';

                        $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                        $form_subheading = 'Bebas transaksi berhari-hari tanpa biaya inap mulai dari $300';

                        $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                        $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                        $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                        $banner_image_desktop = 'banner-acuity-desktop.jpg';
                        $banner_image_mobile = 'banner-acuity-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                        $section_2_title_1 = 'ACUITY';
                        $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_1 = 'acuity.jpg';
                        $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                        $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                        $section_2_image_2 = 'winner-acuity.jpg';
                        $section_2_title_3 = 'Platform Yang Sama';
                        $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                        $section_2_image_3 = 'platform-acuity.jpg';
                        $section_2_title_4 = 'Sentimen Market Alert';
                        $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                        $section_2_image_4 = 'sentimen-acuity.jpg';
                        $section_2_title_5 = 'Strategi Partner';
                        $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                        $section_2_image_5 = 'strategi-acuity.jpg';
                        $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                        $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                        $section_2_image_6 = 'kalender-acuity.jpg';

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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'ebook':
                        $source = 338875;
                        $download_button = 'DOWNLOAD EBOOK';
                        $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                        $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                        $form_subheading = '';

                        $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                        $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                        $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                        $banner_image_desktop = 'ebook-desktop.jpg';
                        $banner_image_mobile = 'ebook-mobile.jpg';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'searchebook':
                        $source = 338879;
                        $download_button = 'DOWNLOAD EBOOK';
                        $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                        $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                        $form_subheading = '';

                        $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                        $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                        $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                        $banner_image_desktop = 'ebook-desktop.jpg';
                        $banner_image_mobile = 'ebook-mobile.jpg';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'zerozerozero':
                        $source = 338885;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'searchzerozerozero':
                        $source = 338883;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'searchzerozerozero':
                        $source = 338883;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'searchkompetitor':
                        $source = 338882;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'cashback20rewards':
                        $source = 353623;
                        $download_button = 'TPFx Ramadan Promo';
                        $title = 'TPFx Ramadan Promo';

                        $form_heading = 'TPFx Ramadan Promo';
                        $form_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';

                        $section_1_heading = 'TPFx Ramadan Promo';
                        $section_1_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                        $banner_image_desktop = 'reward-emas-desktop-rev.jpg';
                        $banner_image_mobile = 'reward-emas-mobile-rev.jpg';

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
                        $section_2_title_4 = 'ACUITY';
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

                        $section_5_heading = 'TPFx Ramadan Promo Reward Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';

                        $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                        $reward_cashback_row = 0;

                        break;
                    case 'searchcashback20rewards':
                        $source = 353624;
                        $download_button = 'TPFx Ramadan Promo';
                        $title = 'TPFx Ramadan Promo';

                        $form_heading = 'TPFx Ramadan Promo';
                        $form_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';

                        $section_1_heading = 'TPFx Ramadan Promo';
                        $section_1_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                        $banner_image_desktop = 'reward-emas-desktop-rev.jpg';
                        $banner_image_mobile = 'reward-emas-mobile-rev.jpg';

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
                        $section_2_title_4 = 'ACUITY';
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

                        $section_5_heading = 'TPFx Ramadan Promo Reward Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';

                        $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                        $reward_cashback_row = 0;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'social':
                switch($utm_campaign) {
                    case 'gold015':
                        $source = 338906;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Trading Emas Spread Rendah';

                        $form_heading = 'Dapatkan Income Harian';
                        $form_subheading = 'Trading Gold Spread Mulai 0,15';

                        $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                        $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                        $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                        $banner_image_desktop = 'gold015-desktop.jpg';
                        $banner_image_mobile = 'gold015-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                        $section_2_title_1 = 'Spread Mulai 0,15';
                        $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
                        $section_2_title_5 = 'TPFx Support 24 Jam';
                        $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                        $section_2_image_5 = 'image support.png';
                        $section_2_title_6 = 'Pencairan Dana Cepat';
                        $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                        $section_2_image_6 = 'image dana.png';

                        $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                        $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                        $section_4_heading = 'Keuntungan Trading Emas Online';
                        $section_4_subheading_1 = 'Likuiditas';
                        $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                        $section_4_subheading_2 = 'Leverage';
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'webinar':
                        $source = 338908;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Webinar';

                        $form_heading = 'Daftar Webinar';
                        $form_subheading = 'Ikuti Webinar & Belajar Trading Sekarang';

                        $banner_image_desktop = 'banner-20220415-desktop.jpg';
                        $banner_image_mobile = 'banner-20220415-mobile.jpg';

                        $section_1_heading = 'Cara Cuan Cepat Dengan Jurus  6 Pola Candle Price Action';
                        $section_1_subheading = 'Teknik Price Action Merupakan teknik dengan cara memantau pasar dan melihat pola-pola grafik yang ada di pasar. Ikuti webinar dan temukan 6 pola dalam candle price action sekarang';
                        $section_1_row_heading = 'Webinar khusus untuk mempelajari seputar dunia trading dan diselenggarakan secara online dengan materi menarik dan pembicara professional untuk  menghasilkan profit dan menjadikan Anda trader professional';

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
                        $section_2_title_5 = 'Edukasi Trading';
                        $section_2_description_5 = 'Edukasi dan analisa trading langsung dari professional';
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
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'freeswap300':
                        $source = 338905;
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
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'acuity':
                        $source = 338903;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Download Acuity pada Metatrader';

                        $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-acuity-desktop.jpg';
                        $banner_image_mobile = 'banner-acuity-mobile.jpg';

                        $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                        $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                        $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                        $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                        $section_2_title_1 = 'ACUITY';
                        $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_1 = 'acuity.jpg';
                        $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                        $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                        $section_2_image_2 = 'winner-acuity.jpg';
                        $section_2_title_3 = 'Platform Yang Sama';
                        $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                        $section_2_image_3 = 'platform-acuity.jpg';
                        $section_2_title_4 = 'Sentimen Market Alert';
                        $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                        $section_2_image_4 = 'sentimen-acuity.jpg';
                        $section_2_title_5 = 'Strategi Partner';
                        $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                        $section_2_image_5 = 'strategi-acuity.jpg';
                        $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                        $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                        $section_2_image_6 = 'kalender-acuity.jpg';

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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'ebook':
                        $source = 338904;
                        $download_button = 'DOWNLOAD EBOOK';
                        $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                        $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                        $form_subheading = '';

                        $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                        $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                        $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                        $banner_image_desktop = 'ebook-desktop.jpg';
                        $banner_image_mobile = 'ebook-mobile.jpg';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'zerozerozero':
                        $source = 338909;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'formgold015':
                        $source = 342996;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Trading Emas Spread Rendah';

                        $form_heading = 'Dapatkan Income Harian';
                        $form_subheading = 'Trading Gold Spread Mulai 0,15';

                        $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                        $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                        $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                        $banner_image_desktop = 'gold015-desktop.jpg';
                        $banner_image_mobile = 'gold015-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                        $section_2_title_1 = 'Spread Mulai 0,15';
                        $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
                        $section_2_title_5 = 'TPFx Support 24 Jam';
                        $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                        $section_2_image_5 = 'image support.png';
                        $section_2_title_6 = 'Pencairan Dana Cepat';
                        $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                        $section_2_image_6 = 'image dana.png';

                        $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                        $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                        $section_4_heading = 'Keuntungan Trading Emas Online';
                        $section_4_subheading_1 = 'Likuiditas';
                        $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                        $section_4_subheading_2 = 'Leverage';
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'formebook':
                        $source = 350434;
                        $download_button = 'DOWNLOAD EBOOK';
                        $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                        $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                        $form_subheading = '';

                        $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                        $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                        $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                        $banner_image_desktop = 'ebook-desktop.jpg';
                        $banner_image_mobile = 'ebook-mobile.jpg';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'cashback20rewards':
                        $source = 353625;
                        $download_button = 'TPFx Ramadan Promo';
                        $title = 'TPFx Ramadan Promo';

                        $form_heading = 'TPFx Ramadan Promo';
                        $form_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';

                        $section_1_heading = 'TPFx Ramadan Promo';
                        $section_1_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                        $banner_image_desktop = 'reward-emas-desktop-rev.jpg';
                        $banner_image_mobile = 'reward-emas-mobile-rev.jpg';

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
                        $section_2_title_4 = 'ACUITY';
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

                        $section_5_heading = 'TPFx Ramadan Promo Reward Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';

                        $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                        $reward_cashback_row = 0;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'youtube':
                switch($utm_campaign) {
                    case 'cashback20rewards':
                        $source = 358396;
                        $download_button = 'TPFx Ramadan Promo';
                        $title = 'TPFx Ramadan Promo';

                        $form_heading = 'TPFx Ramadan Promo';
                        $form_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';

                        $section_1_heading = 'TPFx Ramadan Promo';
                        $section_1_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                        $banner_image_desktop = 'reward-emas-desktop-rev.jpg';
                        $banner_image_mobile = 'reward-emas-mobile-rev.jpg';

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
                        $section_2_title_4 = 'ACUITY';
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

                        $section_5_heading = 'TPFx Ramadan Promo Reward Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';

                        $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                        $reward_cashback_row = 0;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'igbio':
                switch($utm_campaign) {
                    case 'gold015':
                        $source = 338889;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Trading Emas Spread Rendah';

                        $form_heading = 'Dapatkan Income Harian';
                        $form_subheading = 'Trading Gold Spread Mulai 0,15';

                        $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                        $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                        $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                        $banner_image_desktop = 'gold015-desktop.jpg';
                        $banner_image_mobile = 'gold015-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                        $section_2_title_1 = 'Spread Mulai 0,15';
                        $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
                        $section_2_title_5 = 'TPFx Support 24 Jam';
                        $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                        $section_2_image_5 = 'image support.png';
                        $section_2_title_6 = 'Pencairan Dana Cepat';
                        $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                        $section_2_image_6 = 'image dana.png';

                        $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                        $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                        $section_4_heading = 'Keuntungan Trading Emas Online';
                        $section_4_subheading_1 = 'Likuiditas';
                        $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                        $section_4_subheading_2 = 'Leverage';
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'freeswap300':
                        $source = 338888;
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
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'acuity':
                        $source = 338886;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Download Acuity pada Metatrader';

                        $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-acuity-desktop.jpg';
                        $banner_image_mobile = 'banner-acuity-mobile.jpg';

                        $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                        $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                        $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                        $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                        $section_2_title_1 = 'ACUITY';
                        $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_1 = 'acuity.jpg';
                        $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                        $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                        $section_2_image_2 = 'winner-acuity.jpg';
                        $section_2_title_3 = 'Platform Yang Sama';
                        $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                        $section_2_image_3 = 'platform-acuity.jpg';
                        $section_2_title_4 = 'Sentimen Market Alert';
                        $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                        $section_2_image_4 = 'sentimen-acuity.jpg';
                        $section_2_title_5 = 'Strategi Partner';
                        $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                        $section_2_image_5 = 'strategi-acuity.jpg';
                        $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                        $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                        $section_2_image_6 = 'kalender-acuity.jpg';

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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'ebook':
                        $source = 338887;
                        $download_button = 'DOWNLOAD EBOOK';
                        $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                        $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                        $form_subheading = '';

                        $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                        $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                        $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                        $banner_image_desktop = 'ebook-desktop.jpg';
                        $banner_image_mobile = 'ebook-mobile.jpg';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'zerozerozero':
                        $source = 338890;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'igfeed':
                switch($utm_campaign) {
                    case 'webinar':
                        $source = 358878;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Webinar';

                        $form_heading = 'Daftar Webinar';
                        $form_subheading = 'Ikuti Webinar & Belajar Trading Sekarang';

                        $banner_image_desktop = 'banner-20220415-desktop.jpg';
                        $banner_image_mobile = 'banner-20220415-mobile.jpg';

                        $section_1_heading = 'Cara Cuan Cepat Dengan Jurus  6 Pola Candle Price Action';
                        $section_1_subheading = 'Teknik Price Action Merupakan teknik dengan cara memantau pasar dan melihat pola-pola grafik yang ada di pasar. Ikuti webinar dan temukan 6 pola dalam candle price action sekarang';
                        $section_1_row_heading = 'Webinar khusus untuk mempelajari seputar dunia trading dan diselenggarakan secara online dengan materi menarik dan pembicara professional untuk  menghasilkan profit dan menjadikan Anda trader professional';

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
                        $section_2_title_5 = 'Edukasi Trading';
                        $section_2_description_5 = 'Edukasi dan analisa trading langsung dari professional';
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
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'smsblast':
                switch($utm_campaign) {
                    case 'gold015':
                        $source = 338901;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Trading Emas Spread Rendah';

                        $form_heading = 'Dapatkan Income Harian';
                        $form_subheading = 'Trading Gold Spread Mulai 0,15';

                        $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                        $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                        $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                        $banner_image_desktop = 'gold015-desktop.jpg';
                        $banner_image_mobile = 'gold015-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                        $section_2_title_1 = 'Spread Mulai 0,15';
                        $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
                        $section_2_title_5 = 'TPFx Support 24 Jam';
                        $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                        $section_2_image_5 = 'image support.png';
                        $section_2_title_6 = 'Pencairan Dana Cepat';
                        $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                        $section_2_image_6 = 'image dana.png';

                        $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                        $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                        $section_4_heading = 'Keuntungan Trading Emas Online';
                        $section_4_subheading_1 = 'Likuiditas';
                        $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                        $section_4_subheading_2 = 'Leverage';
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'freeswap300':
                        $source = 338900;
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
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'ebook':
                        $source = 338899;
                        $download_button = 'DOWNLOAD EBOOK';
                        $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                        $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                        $form_subheading = '';

                        $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                        $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                        $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                        $banner_image_desktop = 'ebook-desktop.jpg';
                        $banner_image_mobile = 'ebook-mobile.jpg';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'zerozerozero':
                        $source = 338902;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'detik':
                switch($utm_campaign) {
                    case 'gold015':
                        $source = 338861;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Trading Emas Spread Rendah';

                        $form_heading = 'Dapatkan Income Harian';
                        $form_subheading = 'Trading Gold Spread Mulai 0,15';

                        $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                        $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                        $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                        $banner_image_desktop = 'gold015-desktop.jpg';
                        $banner_image_mobile = 'gold015-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                        $section_2_title_1 = 'Spread Mulai 0,15';
                        $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
                        $section_2_title_5 = 'TPFx Support 24 Jam';
                        $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                        $section_2_image_5 = 'image support.png';
                        $section_2_title_6 = 'Pencairan Dana Cepat';
                        $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                        $section_2_image_6 = 'image dana.png';

                        $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                        $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                        $section_4_heading = 'Keuntungan Trading Emas Online';
                        $section_4_subheading_1 = 'Likuiditas';
                        $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                        $section_4_subheading_2 = 'Leverage';
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'freeswap300':
                        $source = 338860;
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
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'acuity':
                        $source = 330205;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Download Acuity pada Metatrader';

                        $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-acuity-desktop.jpg';
                        $banner_image_mobile = 'banner-acuity-mobile.jpg';

                        $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                        $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                        $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                        $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                        $section_2_title_1 = 'ACUITY';
                        $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_1 = 'acuity.jpg';
                        $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                        $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                        $section_2_image_2 = 'winner-acuity.jpg';
                        $section_2_title_3 = 'Platform Yang Sama';
                        $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                        $section_2_image_3 = 'platform-acuity.jpg';
                        $section_2_title_4 = 'Sentimen Market Alert';
                        $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                        $section_2_image_4 = 'sentimen-acuity.jpg';
                        $section_2_title_5 = 'Strategi Partner';
                        $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                        $section_2_image_5 = 'strategi-acuity.jpg';
                        $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                        $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                        $section_2_image_6 = 'kalender-acuity.jpg';

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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'ebook':
                        $source = 330209;
                        $download_button = 'DOWNLOAD EBOOK';
                        $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                        $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                        $form_subheading = '';

                        $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                        $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                        $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                        $banner_image_desktop = 'ebook-desktop.jpg';
                        $banner_image_mobile = 'ebook-mobile.jpg';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'zerozerozero':
                        $source = 338890;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'email':
                switch($utm_campaign) {
                    case 'gold015':
                        $source = 338866;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Trading Emas Spread Rendah';

                        $form_heading = 'Dapatkan Income Harian';
                        $form_subheading = 'Trading Gold Spread Mulai 0,15';

                        $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                        $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                        $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                        $banner_image_desktop = 'gold015-desktop.jpg';
                        $banner_image_mobile = 'gold015-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                        $section_2_title_1 = 'Spread Mulai 0,15';
                        $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
                        $section_2_title_5 = 'TPFx Support 24 Jam';
                        $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                        $section_2_image_5 = 'image support.png';
                        $section_2_title_6 = 'Pencairan Dana Cepat';
                        $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                        $section_2_image_6 = 'image dana.png';

                        $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                        $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                        $section_4_heading = 'Keuntungan Trading Emas Online';
                        $section_4_subheading_1 = 'Likuiditas';
                        $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                        $section_4_subheading_2 = 'Leverage';
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'webinar':
                        $source = 338867;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Webinar';

                        $form_heading = 'Daftar Webinar';
                        $form_subheading = 'Ikuti Webinar & Belajar Trading Sekarang';

                        $banner_image_desktop = 'banner-20220415-desktop.jpg';
                        $banner_image_mobile = 'banner-20220415-mobile.jpg';

                        $section_1_heading = 'Cara Cuan Cepat Dengan Jurus  6 Pola Candle Price Action';
                        $section_1_subheading = 'Teknik Price Action Merupakan teknik dengan cara memantau pasar dan melihat pola-pola grafik yang ada di pasar. Ikuti webinar dan temukan 6 pola dalam candle price action sekarang';
                        $section_1_row_heading = 'Webinar khusus untuk mempelajari seputar dunia trading dan diselenggarakan secara online dengan materi menarik dan pembicara professional untuk  menghasilkan profit dan menjadikan Anda trader professional';

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
                        $section_2_title_5 = 'Edukasi Trading';
                        $section_2_description_5 = 'Edukasi dan analisa trading langsung dari professional';
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
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'freeswap300':
                        $source = 338865;
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
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'acuity':
                        $source = 338863;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Download Acuity pada Metatrader';

                        $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-acuity-desktop.jpg';
                        $banner_image_mobile = 'banner-acuity-mobile.jpg';

                        $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                        $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                        $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                        $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                        $section_2_title_1 = 'ACUITY';
                        $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_1 = 'acuity.jpg';
                        $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                        $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                        $section_2_image_2 = 'winner-acuity.jpg';
                        $section_2_title_3 = 'Platform Yang Sama';
                        $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                        $section_2_image_3 = 'platform-acuity.jpg';
                        $section_2_title_4 = 'Sentimen Market Alert';
                        $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                        $section_2_image_4 = 'sentimen-acuity.jpg';
                        $section_2_title_5 = 'Strategi Partner';
                        $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                        $section_2_image_5 = 'strategi-acuity.jpg';
                        $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                        $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                        $section_2_image_6 = 'kalender-acuity.jpg';

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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'ebook':
                        $source = 338864;
                        $download_button = 'DOWNLOAD EBOOK';
                        $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                        $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                        $form_subheading = '';

                        $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                        $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                        $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                        $banner_image_desktop = 'ebook-desktop.jpg';
                        $banner_image_mobile = 'ebook-mobile.jpg';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'zerozerozero':
                        $source = 338868;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'cashback20rewards':
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
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'ACUITY';
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

                        $section_5_heading = 'Dapatkan Cashback Reward Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';

                        $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                        $reward_cashback_row = 0;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'fbpage':
                switch($utm_campaign) {
                    case 'gold015':
                        $source = 338872;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Trading Emas Spread Rendah';

                        $form_heading = 'Dapatkan Income Harian';
                        $form_subheading = 'Trading Gold Spread Mulai 0,15';

                        $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                        $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                        $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                        $banner_image_desktop = 'gold015-desktop.jpg';
                        $banner_image_mobile = 'gold015-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                        $section_2_title_1 = 'Spread Mulai 0,15';
                        $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
                        $section_2_title_5 = 'TPFx Support 24 Jam';
                        $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                        $section_2_image_5 = 'image support.png';
                        $section_2_title_6 = 'Pencairan Dana Cepat';
                        $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                        $section_2_image_6 = 'image dana.png';

                        $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                        $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                        $section_4_heading = 'Keuntungan Trading Emas Online';
                        $section_4_subheading_1 = 'Likuiditas';
                        $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                        $section_4_subheading_2 = 'Leverage';
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'freeswap300':
                        $source = 338871;
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
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'acuity':
                        $source = 338869;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Download Acuity pada Metatrader';

                        $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-acuity-desktop.jpg';
                        $banner_image_mobile = 'banner-acuity-mobile.jpg';

                        $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                        $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                        $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                        $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                        $section_2_title_1 = 'ACUITY';
                        $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_1 = 'acuity.jpg';
                        $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                        $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                        $section_2_image_2 = 'winner-acuity.jpg';
                        $section_2_title_3 = 'Platform Yang Sama';
                        $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                        $section_2_image_3 = 'platform-acuity.jpg';
                        $section_2_title_4 = 'Sentimen Market Alert';
                        $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                        $section_2_image_4 = 'sentimen-acuity.jpg';
                        $section_2_title_5 = 'Strategi Partner';
                        $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                        $section_2_image_5 = 'strategi-acuity.jpg';
                        $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                        $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                        $section_2_image_6 = 'kalender-acuity.jpg';

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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'ebook':
                        $source = 338870;
                        $download_button = 'DOWNLOAD EBOOK';
                        $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                        $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                        $form_subheading = '';

                        $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                        $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                        $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                        $banner_image_desktop = 'ebook-desktop.jpg';
                        $banner_image_mobile = 'ebook-mobile.jpg';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'zerozerozero':
                        $source = 338873;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'tiktok':
                switch($utm_campaign) {
                    case 'gold015':
                        $source = 338915;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Trading Emas Spread Rendah';

                        $form_heading = 'Dapatkan Income Harian';
                        $form_subheading = 'Trading Gold Spread Mulai 0,15';

                        $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                        $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                        $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                        $banner_image_desktop = 'gold015-desktop.jpg';
                        $banner_image_mobile = 'gold015-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                        $section_2_title_1 = 'Spread Mulai 0,15';
                        $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
                        $section_2_title_5 = 'TPFx Support 24 Jam';
                        $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                        $section_2_image_5 = 'image support.png';
                        $section_2_title_6 = 'Pencairan Dana Cepat';
                        $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                        $section_2_image_6 = 'image dana.png';

                        $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                        $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                        $section_4_heading = 'Keuntungan Trading Emas Online';
                        $section_4_subheading_1 = 'Likuiditas';
                        $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                        $section_4_subheading_2 = 'Leverage';
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'freeswap300':
                        $source = 338914;
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
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'acuity':
                        $source = 338912;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Download Acuity pada Metatrader';

                        $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-acuity-desktop.jpg';
                        $banner_image_mobile = 'banner-acuity-mobile.jpg';

                        $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                        $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                        $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                        $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                        $section_2_title_1 = 'ACUITY';
                        $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_1 = 'acuity.jpg';
                        $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                        $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                        $section_2_image_2 = 'winner-acuity.jpg';
                        $section_2_title_3 = 'Platform Yang Sama';
                        $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                        $section_2_image_3 = 'platform-acuity.jpg';
                        $section_2_title_4 = 'Sentimen Market Alert';
                        $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                        $section_2_image_4 = 'sentimen-acuity.jpg';
                        $section_2_title_5 = 'Strategi Partner';
                        $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                        $section_2_image_5 = 'strategi-acuity.jpg';
                        $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                        $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                        $section_2_image_6 = 'kalender-acuity.jpg';

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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'ebook':
                        $source = 338913;
                        $download_button = 'DOWNLOAD EBOOK';
                        $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                        $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                        $form_subheading = '';

                        $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                        $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                        $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                        $banner_image_desktop = 'ebook-desktop.jpg';
                        $banner_image_mobile = 'ebook-mobile.jpg';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'zerozerozero':
                        $source = 338916;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'advertorial':
                switch($utm_campaign) {
                    case 'gold015':
                        $source = 330210;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Trading Emas Spread Rendah';

                        $form_heading = 'Dapatkan Income Harian';
                        $form_subheading = 'Trading Gold Spread Mulai 0,15';

                        $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                        $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                        $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                        $banner_image_desktop = 'gold015-desktop.jpg';
                        $banner_image_mobile = 'gold015-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                        $section_2_title_1 = 'Spread Mulai 0,15';
                        $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
                        $section_2_title_5 = 'TPFx Support 24 Jam';
                        $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                        $section_2_image_5 = 'image support.png';
                        $section_2_title_6 = 'Pencairan Dana Cepat';
                        $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                        $section_2_image_6 = 'image dana.png';

                        $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                        $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                        $section_4_heading = 'Keuntungan Trading Emas Online';
                        $section_4_subheading_1 = 'Likuiditas';
                        $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                        $section_4_subheading_2 = 'Leverage';
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'freeswap300':
                        $source = 330207;
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
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'ebook':
                        $source = 330208;
                        $download_button = 'DOWNLOAD EBOOK';
                        $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                        $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                        $form_subheading = '';

                        $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                        $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                        $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                        $banner_image_desktop = 'ebook-desktop.jpg';
                        $banner_image_mobile = 'ebook-mobile.jpg';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'zerozerozero':
                        $source = 330206;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'whatsapp':
                switch($utm_campaign) {
                    case 'webinar':
                        $source = 338923;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Webinar';

                        $form_heading = 'Daftar Webinar';
                        $form_subheading = 'Ikuti Webinar & Belajar Trading Sekarang';

                        $banner_image_desktop = 'banner-20220415-desktop.jpg';
                        $banner_image_mobile = 'banner-20220415-mobile.jpg';

                        $section_1_heading = 'Cara Cuan Cepat Dengan Jurus  6 Pola Candle Price Action';
                        $section_1_subheading = 'Teknik Price Action Merupakan teknik dengan cara memantau pasar dan melihat pola-pola grafik yang ada di pasar. Ikuti webinar dan temukan 6 pola dalam candle price action sekarang';
                        $section_1_row_heading = 'Webinar khusus untuk mempelajari seputar dunia trading dan diselenggarakan secara online dengan materi menarik dan pembicara professional untuk  menghasilkan profit dan menjadikan Anda trader professional';

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
                        $section_2_title_5 = 'Edukasi Trading';
                        $section_2_description_5 = 'Edukasi dan analisa trading langsung dari professional';
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
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'acuity':
                        $source = 338922;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Download Acuity pada Metatrader';

                        $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-acuity-desktop.jpg';
                        $banner_image_mobile = 'banner-acuity-mobile.jpg';

                        $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                        $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                        $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                        $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                        $section_2_title_1 = 'ACUITY';
                        $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_1 = 'acuity.jpg';
                        $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                        $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                        $section_2_image_2 = 'winner-acuity.jpg';
                        $section_2_title_3 = 'Platform Yang Sama';
                        $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                        $section_2_image_3 = 'platform-acuity.jpg';
                        $section_2_title_4 = 'Sentimen Market Alert';
                        $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                        $section_2_image_4 = 'sentimen-acuity.jpg';
                        $section_2_title_5 = 'Strategi Partner';
                        $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                        $section_2_image_5 = 'strategi-acuity.jpg';
                        $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                        $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                        $section_2_image_6 = 'kalender-acuity.jpg';

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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'zerozerozero':
                        $source = 339037;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'twitter':
                switch($utm_campaign) {
                    case 'gold015':
                        $source = 339036;
                        $download_button = 'DAFTAR SEKARANG';
                        $title = 'Trading Emas Spread Rendah';

                        $form_heading = 'Dapatkan Income Harian';
                        $form_subheading = 'Trading Gold Spread Mulai 0,15';

                        $section_1_heading = 'Trading Emas Online Spread Terendah 0.15';
                        $section_1_subheading = 'Manfaatkan Peluang Profit Harian Melalui Pergerakan Emas Dunia';
                        $section_1_row_heading = 'Mulai bertransaksi di pasar emas dunia sekarang. Manfaatkan pergerakan emas harian  dan raih peluang profit harian bersama TPFx';

                        $banner_image_desktop = 'gold015-desktop.jpg';
                        $banner_image_mobile = 'gold015-mobile.jpg';

                        $section_2_heading = 'Mengapa Trading Emas Online Bersama TPFx?';
                        $section_2_title_1 = 'Spread Mulai 0,15';
                        $section_2_description_1 = 'Spread rendah dan stabil mulai dari 0,15';
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
                        $section_2_title_5 = 'TPFx Support 24 Jam';
                        $section_2_description_5 = 'TPFx support yang siap membantu sampai 24 jam';
                        $section_2_image_5 = 'image support.png';
                        $section_2_title_6 = 'Pencairan Dana Cepat';
                        $section_2_description_6 = 'Penarikan dan pencairan dana cepat dan mudah di hari yang sama';
                        $section_2_image_6 = 'image dana.png';

                        $section_3_heading = 'TPFx Broker Aman Dan Teregulasi';
                        $section_3_subheading = 'TPFx merupakan salah satu anggota Pialang Bursa Berjangka dan pialang yang terdaftar serta teregulasi oleh BAPPEBTI yang telah beroperasi lebih dari 17 tahun serta menyediakan sarana dan prasarana perdagangan kontrak berjangka untuk memberikan pengalaman trading terbaik. Sebaagai broker yang sudah berpengalaman TPFx mengedepankan layanan seperti edukasi trading, produk derivatif yang bervarian, spread rendah dan stabil, eksekusi market real-time dan pencairan dana yang cepat dan mudah.';

                        $section_4_heading = 'Keuntungan Trading Emas Online';
                        $section_4_subheading_1 = 'Likuiditas';
                        $section_4_description_1 = 'Raih peluang profit harian emas online di pasar paling likuid';
                        $section_4_subheading_2 = 'Leverage';
                        $section_4_description_2 = 'Transaksi emang dengan posisi besar menggunakan dana deposit yang rendah';
                        $section_4_subheading_3 = 'Transaksi 2 Dua Arah';
                        $section_4_description_3 = 'Transaksi yang memanfaatkan transaksi 2 arah baik beli dan jual';

                        $section_5_heading = 'Transaksi Emas Online Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'freeswap300':
                        $source = 338919;
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
                        $section_2_image_1 = 'image spread.png';
                        $section_2_title_2 = 'Leverage sampai 1 : 400';
                        $section_2_description_2 = 'Transaksi emas online menggunakan leverage 1 : 400';
                        $section_2_image_2 = 'image leverage.png';
                        $section_2_title_3 = 'Platform Professional';
                        $section_2_description_3 = 'Eksekusi cepat dengan platform profesional';
                        $section_2_image_3 = 'mt4.png';
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'acuity':
                        $source = 338917;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Download Acuity pada Metatrader';

                        $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-acuity-desktop.jpg';
                        $banner_image_mobile = 'banner-acuity-mobile.jpg';

                        $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                        $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                        $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                        $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                        $section_2_title_1 = 'ACUITY';
                        $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_1 = 'acuity.jpg';
                        $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                        $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                        $section_2_image_2 = 'winner-acuity.jpg';
                        $section_2_title_3 = 'Platform Yang Sama';
                        $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                        $section_2_image_3 = 'platform-acuity.jpg';
                        $section_2_title_4 = 'Sentimen Market Alert';
                        $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                        $section_2_image_4 = 'sentimen-acuity.jpg';
                        $section_2_title_5 = 'Strategi Partner';
                        $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                        $section_2_image_5 = 'strategi-acuity.jpg';
                        $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                        $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                        $section_2_image_6 = 'kalender-acuity.jpg';

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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'ebook':
                        $source = 338918;
                        $download_button = 'DOWNLOAD EBOOK';
                        $title = 'E-Book Dasar-Dasar Trading dan Strategi Trading';

                        $form_heading = 'DOWNLOAD EBOOK TRADING SEKARANG';
                        $form_subheading = '';

                        $section_1_heading = 'EBOOK DASAR-DASAR TRADING DAN STRATEGI TRADING';
                        $section_1_subheading = 'Download dan dapatkan strategi trading bagi pemula hingga professional';
                        $section_1_row_heading = 'Sebagai pemula yang belum pernah Trading Online, E-Book Trading ini akan menjadi referensi yang tepat sebagai dasar trading beserta analisa-analisa yang dapat diaplikasikan pada perdagangan berjangka';

                        $banner_image_desktop = 'ebook-desktop.jpg';
                        $banner_image_mobile = 'ebook-mobile.jpg';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'zerozerozero':
                        $source = 338920;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'cashback20rewards':
                        $source = 353626;
                        $download_button = 'TPFx Ramadan Promo';
                        $title = 'TPFx Ramadan Promo';

                        $form_heading = 'TPFx Ramadan Promo';
                        $form_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';

                        $section_1_heading = 'TPFx Ramadan Promo';
                        $section_1_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                        $banner_image_desktop = 'reward-emas-desktop-rev.jpg';
                        $banner_image_mobile = 'reward-emas-mobile-rev.jpg';

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
                        $section_2_title_4 = 'ACUITY';
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

                        $section_5_heading = 'TPFx Ramadan Promo Reward Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';

                        $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                        $reward_cashback_row = 0;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'telegram':
                switch($utm_campaign) {
                    case 'acuity':
                        $source = 338910;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Download Acuity pada Metatrader';

                        $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-acuity-desktop.jpg';
                        $banner_image_mobile = 'banner-acuity-mobile.jpg';

                        $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                        $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                        $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                        $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                        $section_2_title_1 = 'ACUITY';
                        $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_1 = 'acuity.jpg';
                        $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                        $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                        $section_2_image_2 = 'winner-acuity.jpg';
                        $section_2_title_3 = 'Platform Yang Sama';
                        $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                        $section_2_image_3 = 'platform-acuity.jpg';
                        $section_2_title_4 = 'Sentimen Market Alert';
                        $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                        $section_2_image_4 = 'sentimen-acuity.jpg';
                        $section_2_title_5 = 'Strategi Partner';
                        $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                        $section_2_image_5 = 'strategi-acuity.jpg';
                        $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                        $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                        $section_2_image_6 = 'kalender-acuity.jpg';

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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'linkedin':
                switch($utm_campaign) {
                    case 'acuity':
                        $source = 338891;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Download Acuity pada Metatrader';

                        $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-acuity-desktop.jpg';
                        $banner_image_mobile = 'banner-acuity-mobile.jpg';

                        $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                        $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                        $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                        $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                        $section_2_title_1 = 'ACUITY';
                        $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_1 = 'acuity.jpg';
                        $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                        $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                        $section_2_image_2 = 'winner-acuity.jpg';
                        $section_2_title_3 = 'Platform Yang Sama';
                        $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                        $section_2_image_3 = 'platform-acuity.jpg';
                        $section_2_title_4 = 'Sentimen Market Alert';
                        $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                        $section_2_image_4 = 'sentimen-acuity.jpg';
                        $section_2_title_5 = 'Strategi Partner';
                        $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                        $section_2_image_5 = 'strategi-acuity.jpg';
                        $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                        $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                        $section_2_image_6 = 'kalender-acuity.jpg';

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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'zerozerozero':
                        $source = 342997;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'mgid.com':
                switch($utm_campaign) {
                    case 'acuity':
                        $source = 342998;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Download Acuity pada Metatrader';

                        $form_heading = 'DOWNLOAD ACUITY PADA METATRADER';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-acuity-desktop.jpg';
                        $banner_image_mobile = 'banner-acuity-mobile.jpg';

                        $section_1_heading = 'ANALISA DATA DAN TRANSAKSI REAL TIME DENGAN ACUITY EXPERT ADVISOR';
                        $section_1_subheading = 'Menganalisa jutaan artikel dan memberikan sinyal trading secara real time, maksimalkan profit dengan hasil trading terbaik dengan Acuity';
                        $section_1_row_heading = 'Mulai tingkatkan dan maksimalkan profit anda transaksi berhari-hari tanpa dikenakan biaya inap dan biaya terendah se-Indonesia';

                        $section_2_heading = 'Mengapa Trading Menggunakan Acuity Expert Advisor?';
                        $section_2_title_1 = 'ACUITY';
                        $section_2_description_1 = 'Merupakan trading tool yang mengirimkan sinyal secara real time berdasarkan sentimen pasar Fx, Komoditas, Oil dan Index Saham';
                        $section_2_image_1 = 'acuity.jpg';
                        $section_2_title_2 = 'The Winner Technical Analyst Awards 2021';
                        $section_2_description_2 = 'Acuity Trading telah dinobatkan sebagai pemenang Penghargaan Data AI Terbaik 2021 oleh Analis Teknis';
                        $section_2_image_2 = 'winner-acuity.jpg';
                        $section_2_title_3 = 'Platform Yang Sama';
                        $section_2_description_3 = 'Sinyal Trading dan Transaksi langsung pada platform yang sama dengan cepat dan mudah';
                        $section_2_image_3 = 'platform-acuity.jpg';
                        $section_2_title_4 = 'Sentimen Market Alert';
                        $section_2_description_4 = 'Berita terbaru dengan sentimen alert untuk pilihan mata uang anda';
                        $section_2_image_4 = 'sentimen-acuity.jpg';
                        $section_2_title_5 = 'Strategi Partner';
                        $section_2_description_5 = 'Acuity memiliki strategi partner dengan Dow Jones, Wall Street Journal dan memiliki lebih dari 32.000 aset';
                        $section_2_image_5 = 'strategi-acuity.jpg';
                        $section_2_title_6 = 'Berita dan Kalender Ekonomi';
                        $section_2_description_6 = 'Artikel dan Kalender Ekonomi terlengkap untuk memandu dan mengarahkan strategi trading anda';
                        $section_2_image_6 = 'kalender-acuity.jpg';

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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    case 'TPFx Lead Gen':
                        $source = 356609;
                        $download_button = 'INSTALL SEKARANG';
                        $title = 'Trading Bebas Biaya Tersembunyi';

                        $form_heading = 'MULAI TRADING BEBAS BIAYA SEKARANG';
                        $form_subheading = '';

                        $banner_image_desktop = 'banner-zerozerozero-desktop.jpg';
                        $banner_image_mobile = 'banner-zerozerozero-mobile.jpg';

                        $section_1_heading = 'TRADING BEBAS BIAYA TERSEMBUNYI';
                        $section_1_subheading = 'Tingkatkan profit anda dengan fasilitas bebas komisi, bebas swap dan bebas spread melebar';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

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
                        $section_2_title_4 = 'Edukasi Trading';
                        $section_2_description_4 = 'Edukasi dan analisa trading langsung dari professional';
                        $section_2_image_4 = 'image education.png';
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
                        $reward_cashback_tnc = null;
                        $reward_cashback_row = 1;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            case 'outbrain':
                switch($utm_campaign) {
                    case 'cashback20rewards':
                        $source = 362599;
                        $download_button = 'TPFx Ramadan Promo';
                        $title = 'TPFx Ramadan Promo';

                        $form_heading = 'TPFx Ramadan Promo';
                        $form_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';

                        $section_1_heading = 'TPFx Ramadan Promo';
                        $section_1_subheading = 'Deposit dan Menangkan Puluhan Gram Emas';
                        $section_1_row_heading = 'Maksimalkan transaksi anda dengan kesempatan trading menggunakan akun bebas biaya. Trading nyaman tanpa khawatir biaya tersembunyi';

                        $banner_image_desktop = 'reward-emas-desktop-rev.jpg';
                        $banner_image_mobile = 'reward-emas-mobile-rev.jpg';

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
                        $section_2_title_4 = 'ACUITY';
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

                        $section_5_heading = 'TPFx Ramadan Promo Reward Sekarang Bersama TPFx';
                        $section_5_cta = 'DAFTAR SEKARANG';

                        $reward_cashback_tnc = '*syarat dan ketentuan berlaku';
                        $reward_cashback_row = 0;

                        break;
                    default:
                        $source = 338911;
                        $download_button = 'BUAT AKUN';
                        $title = 'Leads Generation';
                        $image = 'home-6.jpg';
                        break;
                }
                break;
            default:
                $source = 338911;
                $download_button = 'BUAT AKUN';
                $title = 'Leads Generation';
                $image = 'home-6.jpg';
                break;
        }

        return view('events.leads_form', compact('source', 'title', 'download_button', 'utm_campaign',
            'form_heading', 'form_subheading',
            'section_1_heading', 'section_1_subheading', 'section_1_row_heading',
            'banner_image_desktop', 'banner_image_mobile',
            'section_2_heading', 'section_2_title_1', 'section_2_description_1', 'section_2_title_2', 'section_2_description_2',
            'section_2_title_3', 'section_2_description_3', 'section_2_title_4', 'section_2_description_4',
            'section_2_title_5', 'section_2_description_5', 'section_2_title_6', 'section_2_description_6',
            'section_2_image_1', 'section_2_image_2', 'section_2_image_3', 'section_2_image_4', 'section_2_image_5', 'section_2_image_6',
            'section_3_heading', 'section_3_subheading', 'section_4_heading',
            'section_4_subheading_1', 'section_4_description_1', 'section_4_subheading_2', 'section_4_description_2',
            'section_4_subheading_3', 'section_4_description_3', 'section_5_heading', 'section_5_cta',
            'reward_cashback_tnc', 'reward_cashback_row'));
    }

    public function thank_you(){
        return view('leads_form_success');
    }

    public function thank_you_ebook_basic(){
        return view('leads_form_success_ebook_basic');
    }

    public function reinput(){
        $leads = Lead::where('created_at', '>', '2022-04-07')->get();
        foreach($leads as $lead) {
            $parts = parse_url($lead->url);

            if(!array_key_exists('query', $parts)) {
                continue;
            }

            parse_str($parts['query'], $query);
            $utm_source = $query['utm_source'];
            $utm_campaign = $query['utm_campaign'];

            switch($utm_source) {
                case 'google':
                    switch($utm_campaign) {
                        case 'gold015':
                            $source = 338877;
                            break;
                        case 'searchgold015':
                            $source = 338881;
                            break;
                        case 'webinar':
                            $source = 338884;
                            break;
                        case 'freeswap300':
                            $source = 338876;
                            break;
                        case 'acuity':
                            $source = 338874;
                            break;
                        case 'searchacuity':
                            $source = 338878;
                            break;
                        case 'ebook':
                            $source = 338875;
                            break;
                        case 'searchebook':
                            $source = 338879;
                            break;
                        case 'zerozerozero':
                            $source = 338885;
                            break;
                        case 'searchzerozerozero':
                            $source = 338883;
                            break;
                        case 'searchzerozerozero':
                            $source = 338883;
                            break;
                        case 'searchkompetitor':
                            $source = 338882;
                            break;
                        case 'cashback20rewards':
                            $source = 353623;
                            break;
                        case 'searchcashback20rewards':
                            $source = 353624;
                            break;
                    }
                    break;
                case 'social':
                    switch($utm_campaign) {
                        case 'gold015':
                            $source = 338906;
                            break;
                        case 'webinar':
                            $source = 338908;
                            break;
                        case 'freeswap300':
                            $source = 338905;
                            break;
                        case 'acuity':
                            $source = 338903;
                            break;
                        case 'ebook':
                            $source = 338904;
                            break;
                        case 'zerozerozero':
                            $source = 338909;
                            break;
                        case 'formgold015':
                            $source = 342996;
                            break;
                        case 'cashback20rewards':
                            $source = 353625;
                            break;
                    }
                    break;
                case 'igbio':
                    switch($utm_campaign) {
                        case 'gold015':
                            $source = 338889;
                            break;
                        case 'freeswap300':
                            $source = 338888;
                            break;
                        case 'acuity':
                            $source = 338886;
                            break;
                        case 'ebook':
                            $source = 338887;
                            break;
                        case 'zerozerozero':
                            $source = 338890;
                            break;
                    }
                    break;
                case 'smsblast':
                    switch($utm_campaign) {
                        case 'gold015':
                            $source = 338901;
                            break;
                        case 'freeswap300':
                            $source = 338900;
                            break;
                        case 'ebook':
                            $source = 338899;
                            break;
                        case 'zerozerozero':
                            $source = 338902;
                            break;
                    }
                    break;
                case 'detik':
                    switch($utm_campaign) {
                        case 'gold015':
                            $source = 338861;
                            break;
                        case 'freeswap300':
                            $source = 338860;
                            break;
                        case 'acuity':
                            $source = 330205;
                            break;
                        case 'ebook':
                            $source = 330209;
                            break;
                        case 'zerozerozero':
                            $source = 338890;
                            break;
                    }
                    break;
                case 'email':
                    switch($utm_campaign) {
                        case 'gold015':
                            $source = 338866;
                            break;
                        case 'webinar':
                            $source = 338867;
                            break;
                        case 'freeswap300':
                            $source = 338865;
                            break;
                        case 'acuity':
                            $source = 338863;
                            break;
                        case 'ebook':
                            $source = 338864;
                            break;
                        case 'zerozerozero':
                            $source = 338868;
                            break;
                        case 'cashback20rewards':
                            $source = 353627;
                            break;
                    }
                    break;
                case 'fbpage':
                    switch($utm_campaign) {
                        case 'gold015':
                            $source = 338872;
                            break;
                        case 'freeswap300':
                            $source = 338871;
                            break;
                        case 'acuity':
                            $source = 338869;
                            break;
                        case 'ebook':
                            $source = 338870;
                            break;
                        case 'zerozerozero':
                            $source = 338873;
                            break;
                    }
                    break;
                case 'tiktok':
                    switch($utm_campaign) {
                        case 'gold015':
                            $source = 338915;
                            break;
                        case 'freeswap300':
                            $source = 338914;
                            break;
                        case 'acuity':
                            $source = 338912;
                            break;
                        case 'ebook':
                            $source = 338913;
                            break;
                        case 'zerozerozero':
                            $source = 338916;
                            break;
                    }
                    break;
                case 'advertorial':
                    switch($utm_campaign) {
                        case 'gold015':
                            $source = 330210;
                            break;
                        case 'freeswap300':
                            $source = 330207;
                            break;
                        case 'ebook':
                            $source = 330208;
                            break;
                        case 'zerozerozero':
                            $source = 330206;
                            break;
                    }
                    break;
                case 'whatsapp':
                    switch($utm_campaign) {
                        case 'webinar':
                            $source = 338923;
                            break;
                        case 'acuity':
                            $source = 338922;
                            break;
                        case 'zerozerozero':
                            $source = 339037;
                            break;
                    }
                    break;
                case 'twitter':
                    switch($utm_campaign) {
                        case 'gold015':
                            $source = 339036;
                            break;
                        case 'freeswap300':
                            $source = 338919;
                            break;
                        case 'acuity':
                            $source = 338917;
                            break;
                        case 'ebook':
                            $source = 338918;
                            break;
                        case 'zerozerozero':
                            $source = 338920;
                            break;
                        case 'cashback20rewards':
                            $source = 353626;
                            break;
                    }
                    break;
                case 'telegram':
                    switch($utm_campaign) {
                        case 'acuity':
                            $source = 338910;
                            break;
                    }
                    break;
                case 'linkedin':
                    switch($utm_campaign) {
                        case 'acuity':
                            $source = 338891;
                            break;
                        case 'zerozerozero':
                            $source = 342997;
                            break;
                    }
                    break;
                case 'mgid.com':
                    switch($utm_campaign) {
                        case 'acuity':
                            $source = 342998;
                            break;
                        case 'zerozerozero':
                            $source = 356609;
                            break;
                    }
                    break;
            }

            $status = 344210;

            switch($lead->id % 3) {
                case 0:
                    $assigned = 81850;
                    break;
                case 1:
                    $assigned = 81900;
                    break;
                case 2:
                    $assigned = 81868;
                    break;
                default:
                    $assigned = 80345;
                    break;
            }
            $form_params = [
                'email' => $lead->email,
                'name' => $lead->name,
                'phonenumber' => $lead->phone,
                'source' => $source,
                'status' => $status,
                'assigned' => $assigned
            ];

            //$crm = $this->crm($form_params);
            $qontak = $this->qontak_store_contact($form_params);
            if(!$qontak) {
                continue;
            }
        }

        return 'ok';
    }

    public function import(){
        $leads = Import::get();
        foreach($leads as $lead) {
            $source = 357555;
            $status = 344210;

            switch($lead->id % 3) {
                case 0:
                    $assigned = 81850;
                    break;
                case 1:
                    $assigned = 81900;
                    break;
                case 2:
                    $assigned = 81868;
                    break;
                default:
                    $assigned = 80345;
                    break;
            }
            $form_params = [
                'email' => $lead->email,
                'name' => $lead->name,
                'phonenumber' => $lead->phone,
                'source' => $source,
                'status' => $status,
                'assigned' => $assigned
            ];

            //$crm = $this->crm($form_params);
            $qontak = $this->qontak_store_contact($form_params);
            if(!$qontak) {
                continue;
            }
        }

        return 'ok';
    }
}
