<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{

    public function store(Request $request)
    {
        $validation = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|max:255|unique:leads,email',
            'phonenumber' => 'required|string|max:255|unique:leads,phone',
            'source' => 'numeric',
            'status' => 'numeric',
        ];
        if($request->utm_campaign == 'clientarea'){
            $validation['email'] = 'required|string|max:255';
            $validation['phonenumber'] = 'required|string|max:255';
        }
        $request->validate($validation);
        $input = $request->all();

        if($input['utm_campaign'] == 'clientarea') {
            $url = $input['referral_code'];
        } else {
            $url = url()->previous();
        }

        if($input['utm_campaign'] != 'clientarea') {
            $lead_id = Lead::insertGetId([
                'name' => $input['name'],
                'email' => $input['email'],
                'phone' => $input['phonenumber'],
                'url' => $url,
            ]);

            if ($input['utm_campaign'] != 'clientarea' && !$lead_id) {
                abort(500, 'Error input leads to DB');
            }
        }

        if(isset($input['referral_code'])) {
            switch (strtolower($input['referral_code'])) {
                case 'ale3':
                    $assigned = 81850;
                    $sales_name = "Ale Sandi Lowix";
                    break;
                case 'ana01':
                    $assigned = 81900;
                    $sales_name = "Mulyanah";
                    break;
                case 'n01':
                    $assigned = 81868;
                    $sales_name = "Hannita Batubara";
                    break;
                case 'pdg':
                    $assigned = 83823;
                    $sales_name = "Ilham Akbar";
                    break;
                default:
                    $assigned = 80345;
                    $sales_name = "Admin";
                    break;
            }
        } else {
            switch ($lead_id % 4) {
                case 0:
                    $assigned = 81850; // ale
                    $sales_name = "Ale Sandi Lowix";
                    break;
                case 1:
                    $assigned = 81900; // ana
                    $sales_name = "Mulyanah";
                    break;
                case 2:
                    $assigned = 81868; // nita
                    $sales_name = "Hannita Batubara";
                    break;
                case 3:
                    $assigned = 83823; // ilham
                    $sales_name = "Ilham Akbar";
                    break;
                default:
                    $assigned = 80345;
                    $sales_name = "Admin";
                    break;
            }
        }

        $meta_account = isset($input['meta_account']) ? $input['meta_account'] : 0;
        $size = isset($input['size']) ? $input['size'] : 0;
        $meta_created_date = isset($input['meta_created_date']) ? $input['meta_created_date'] : date('Y-m-d H:i:s');
        $utm_campaign = isset($input['utm_campaign']) ? $input['utm_campaign'] : 'default';

        $form_params = [
            'email' => $input['email'],
            'name' => $input['name'],
            'phonenumber' => $input['phonenumber'],
            'source' => $input['source'],
            'status' => $input['status'],
            'assigned' => $assigned,
            'meta_account' => $meta_account,
            'meta_created_date' => $meta_created_date,
            'sales_name' => $sales_name,
            'size' => $size
        ];

        $qontak_controller = new QontakController;
        $check_qontak = $qontak_controller->qontak_check_contact($form_params);
        if($check_qontak > 0) {
            $form_params['contact_id'] = $check_qontak[0];
            $form_params['lead_id'] = $check_qontak[1];
            if($utm_campaign == 'clientarea'){
                $form_params['status'] = 344216;
            }
            $store_qontak = $qontak_controller->qontak_update_contact($form_params);
        } else {
            if($utm_campaign == 'clientarea'){
                $form_params['status'] = 365703;
            }
            $store_qontak = $qontak_controller->qontak_store_contact($form_params);
        }

        /*if ($input['utm_campaign'] == 'webinar') {
            Mail::to($input['email'])->send(new RegisterWebinar());
        }*/

        if ($utm_campaign == 'ebook') {
            return redirect()->route('events.thank_you_ebook_basic');
        } elseif($utm_campaign == 'clientarea') {
            return true;
        } else {
            return redirect()->route('events.thank_you');
        }
    }
}
