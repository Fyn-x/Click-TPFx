<?php

namespace App\Http\Controllers;

use App\Models\QontakToken;
use App\Models\QontakWebhook;
use App\Models\RekretLead;
use App\Models\RekretCustomField;
use App\Http\Controllers\EventController;
use App\Http\Controllers\QontakController;
use Illuminate\Http\Request;
use Log;

class QontakController extends Controller
{

    public static function qontak_login()
    {
        $headers = [
            'Content-Type' => 'application/json',
        ];
        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'http_errors' => false,
        ]);

        $data = [
            "grant_type" => "password",
            "username" => "support@tpfx.co.id",
            "password" => "Qontak123!",
            "client_id" => "RRrn6uIxalR_QaHFlcKOqbjHMG63elEdPTair9B9YdY",
            "client_secret" => "Sa8IGIh_HpVK1ZLAF0iFf7jU760osaUNV659pBIZR00"
        ];
        $response = $client->request('POST', 'https://service-chat.qontak.com/oauth/token', [
            'form_params' => $data,
        ]);
        $auth = json_decode($response->getBody());

        $qontak_token_id = QontakToken::insertGetId([
            'access_token' => $auth->access_token,
            'refresh_token' => $auth->refresh_token,
        ]);

        return $auth;
    }

    public function qontak_check_contact($data)
    {
        $login = QontakToken::latest()->first();
        if ($login == null) {
            QontakController::qontak_login();
            $login = QontakToken::latest()->first();
        }
        $headers = [
            'Authorization' => 'Bearer ' . $login->access_token,
            'Content-Type' => 'application/json',
        ];

        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'allow_redirects' => false,
            'http_errors' => false,
        ]);
        $check_email = $client->request('GET', 'https://www.qontak.com/api/v3.1/contacts', [
            'query' => [
                'email' => $data['email']
            ],
        ]);
        if ($check_email->getStatusCode() == 200) {
            if(count(json_decode($check_email->getBody())->response) > 0) {
                return [json_decode($check_email->getBody())->response[0]->id, json_decode($check_email->getBody())->response[0]->crm_deal_ids[0]];
            } else {
                $check_phone = $client->request('GET', 'https://www.qontak.com/api/v3.1/contacts', [
                    'query' => [
                        'phone' => $data['phonenumber']
                    ],
                ]);
                if ($check_phone->getStatusCode() == 200) {
                    if(count(json_decode($check_phone->getBody())->response) > 0) {
                        return [json_decode($check_phone->getBody())->response[0]->id, json_decode($check_phone->getBody())->response[0]->crm_deal_ids[0]];
                    } else {
                        return 0;
                    }
                } elseif ($check_email->getStatusCode() == 401) {
                    QontakController::qontak_login();
                    return QontakController::qontak_check_contact($data);
                } elseif ($check_email->getStatusCode() == 422) {
                    return null;
                }
            }
        } elseif ($check_email->getStatusCode() == 401) {
            QontakController::qontak_login();
            return QontakController::qontak_check_contact($data);
        } elseif ($check_email->getStatusCode() == 422) {
            return null;
        }
    }

    public function qontak_store_contact($data, $lead_id)
    {
        $login = QontakToken::latest()->first();
        if ($login == null) {
            QontakController::qontak_login();
            $login = QontakToken::latest()->first();
        }
        $headers = [
            'Authorization' => 'Bearer ' . $login->access_token,
        ];

        $new_contact = [
            "full_name" => $data['name'],
            "channel_integration_id" => "5e489c08-bd72-40eb-944d-3b5435a311c3",
            "account_uniq_id" => "62".$data['phonenumber'],
            "channel" => "wa_cloud"
        ];

        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'allow_redirects' => false,
            'http_errors' => false,
        ]);
        $response = $client->request('POST', 'https://service-chat.qontak.com/api/open/v1/contact_objects', [
            'json' => json_encode($new_contact),
        ]);

        if ($response->getStatusCode() == 200) {
            return QontakController::qontak_broadcast_message($data, $lead_id, json_decode($response->getBody())->data->id);
        } elseif ($response->getStatusCode() == 401) {
            QontakController::qontak_login();
            return QontakController::qontak_store_contact($data, $lead_id);
        } elseif ($response->getStatusCode() == 422) {
            return null;
        }
    }

    public function qontak_broadcast_message($data, $lead_id)
    {
        $login = QontakToken::latest()->first();
        if ($login == null) {
            QontakController::qontak_login();
            $login = QontakToken::latest()->first();
        }
        $headers = [
            'Authorization' => 'Bearer ' . $login->access_token,
            'Content-Type' => 'application/json',
        ];

        $new_broadcast = [
            "to_name" => $data['name'],
            "to_number" => "62".$data['phonenumber'],
            "message_template_id" => "34958621-1fb0-4c6e-90ae-7acbe0052dfa",
            "channel_integration_id" => "5e489c08-bd72-40eb-944d-3b5435a311c3",
            "language"=> [
                "code" => "id"
            ],
            "parameters" => [
                "body" => [
                  [
                    "key" => "1",
                    "value_text" => $data['name'],
                    "value" => "name"
                  ]
                ]
            ]
        ];
        Log::info([204 => $new_broadcast]);

        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'allow_redirects' => false,
            'http_errors' => false,
        ]);
        $response = $client->request('POST', 'https://service-chat.qontak.com/api/open/v1/broadcasts/whatsapp/direct', [
            'body' => json_encode($new_broadcast),
        ]);

        if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
            $data_qontak = [
                "room_lead_roomid" => json_decode($response->getBody())->data->id
            ];
            return $data_qontak;
        } elseif ($response->getStatusCode() == 401) {
            QontakController::qontak_login();
            return QontakController::qontak_broadcast_message($data, $lead_id);
        } else {
            Log::info([$response->getStatusCode() => ['qontak_broadcast_message', $response->getBody()]]);
        }
    }

    public function qontak_assign_room($qontak_room_id, $agents_qontak_ids, $phonenumber)
    {
        $login = QontakToken::latest()->first();
        if ($login == null) {
            QontakController::qontak_login();
            $login = QontakToken::latest()->first();
        }
        $headers = [
            'Authorization' => 'Bearer ' . $login->access_token,
            'Content-Type' => 'application/json',
        ];

        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'allow_redirects' => false,
            'http_errors' => false,
        ]);
        foreach($agents_qontak_ids as $agents_qontak_id){
            $response = $client->request('POST', "https://service-chat.qontak.com/api/open/v1/rooms/".$qontak_room_id."/agents/".$agents_qontak_id->value);
            RekretLead::where('phonenumber', substr($phonenumber,2))
                ->where('is_qontak_broadcast', 0)
                ->update([
                    'is_qontak_broadcast' => 1
                ]);
        }

        if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
            return response('ok', 200);
        } elseif ($response->getStatusCode() == 401) {
            QontakController::qontak_login();
            return QontakController::qontak_assign_room($qontak_room_id, $agents_qontak_ids, $phonenumber);
        } else {
            Log::info([$response->getStatusCode() => ['qontak_assign_room', $response->getBody()]]);
        }
    }

    public function qontak_webhook_message(Request $request){
        $response = $request->all();
        QontakWebhook::insert([
            'text' => json_encode($response)
        ]);
        $status = $response['status'];
        $room_id = $response['room']['id'];
        $phonenumber = $response['room']['account_uniq_id'];

        $login = QontakToken::latest()->first();
        if ($login == null) {
            QontakController::qontak_login();
            $login = QontakToken::latest()->first();
        }
        $headers = [
            'Authorization' => 'Bearer ' . $login->access_token,
            'Content-Type' => 'application/json',
        ];
        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'allow_redirects' => false,
            'http_errors' => false,
        ]);
        $response = $client->request('GET', "https://service-chat.qontak.com/api/open/v1/rooms/".$room_id);

        if ($response->getStatusCode() == 200 || $response->getStatusCode() == 201) {
            if(count(json_decode($response->getBody())->data->agent_ids) < 2){
                $lead = RekretLead::where('phonenumber', substr($phonenumber,2))
                    ->select('id', 'assigned')
                    ->first();

                $headers = [
                    'authtoken' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyIjoiYWRtaW4iLCJuYW1lIjoiQ1JNIEFkcyIsInBhc3N3b3JkIjpudWxsLCJBUElfVElNRSI6MTY4ODE5MzI5NiwiRVhQX0FQSV9USU1FIjoyNTU2MTE4NzQwfQ.6CqwMrrRx3K-i_nYKxisPXWqWNdD8fEhyYdYlONA8Xo'
                ];
                $client = new \GuzzleHttp\Client([
                    'allow_redirects' => false,
                    'http_errors' => false,
                    'headers' => $headers
                ]);

                $json_update_leads = [
                    'qontak_lead_roomid' => $room_id
                ];

                $response_update_leads = $client->post('https://crm.tpfx.co.id/api/qontak/update/'.$lead->id, [
                    'form_params' => $json_update_leads
                ]);

                if(($status == 'delivered' || $status == 'created' || $status == 'read')){
                    $agents = explode(',',$lead->assigned);
                    $agents_qontak_ids = RekretCustomField::whereIn('relid', $agents)
                        ->where('fieldid', 3)
                        ->select('relid', 'value')
                        ->get();
                    $qontak_assign_room = QontakController::qontak_assign_room($room_id, $agents_qontak_ids, $phonenumber);
                }

                return response('ok', 200);
            } else {

            }
        } elseif ($response->getStatusCode() == 401) {
            QontakController::qontak_login();
            return QontakController::qontak_webhook_message($request);
        } else {
            Log::info([$response->getStatusCode() => ['qontak_webhook_message', $response->getBody()]]);
        }
    }

    public function qontak_update_contact($data)
    {
        $login = QontakToken::latest()->first();
        if ($login == null) {
            QontakController::qontak_login();
            $login = QontakToken::latest()->first();
        }
        $headers = [
            'Authorization' => 'Bearer ' . $login->access_token,
        ];

        $update_contact = [
            "first_name" => $data['name'],
            "email" => $data['email'],
            "telephone" => $data['phonenumber'],
        ];

        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'allow_redirects' => false,
            'http_errors' => false,
        ]);
        $response = $client->request('PUT', 'https://www.qontak.com/api/v3.1/contacts/' . $data['contact_id'], [
            'json' => $update_contact,
        ]);

        if ($response->getStatusCode() == 200) {
            return QontakController::qontak_update_leads($data, $data['lead_id']);
        } elseif ($response->getStatusCode() == 401) {
            QontakController::qontak_login();
            return QontakController::qontak_update_contact($data);
        } elseif ($response->getStatusCode() == 422) {
            return null;
        }
    }

    public function qontak_store_leads($data, $contact_id)
    {
        $login = QontakToken::latest()->first();
        if ($login == null) {
            QontakController::qontak_login();
            $login = QontakToken::latest()->first();
        }
        $headers = [
            'Authorization' => 'Bearer ' . $login->access_token,
        ];

        $client_area_name = isset($data['client_area']) ? $data['client_area'] : 'Leads';

        $new_deals = [
            "creator_id" => $data['assigned'],
            "name" => $client_area_name . " - " . $data['name'],
            "crm_pipeline_id" => 56932,
            "crm_stage_id" => $data['status'],
            "crm_source_id" => $data['source'],
            "currency" => "USD",
            "size" => $data['size'],
            "crm_lead_ids" => [$contact_id],
            "additional_fields" => [
                [
                    "id" => 4415397, //meta account
                    "value" => $data['meta_account'],
                ],
                [
                    "id" => 4415398, //meta created date
                    "value" => date('d/m/Y', strtotime($data['meta_created_date'])),
                ],
                [
                    "id" => 4428993, //sales name
                    "value" => $data['sales_name'],
                ],
            ],
        ];

        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'allow_redirects' => false,
            'http_errors' => false,
        ]);
        $response = $client->request('POST', 'https://www.qontak.com/api/v3.1/deals', [
            'json' => $new_deals,
        ]);

        if (isset($response) && $response->getStatusCode() == 200) {
            return true;
        } elseif ($response->getStatusCode() == 401) {
            QontakController::qontak_login();
            return QontakController::qontak_store_leads($data, $contact_id);
        } elseif ($response->getStatusCode() == 422) {
            return null;
        }
    }

    public function qontak_update_leads($data, $lead_id)
    {
        $login = QontakToken::latest()->first();
        if ($login == null) {
            QontakController::qontak_login();
            $login = QontakToken::latest()->first();
        }
        $headers = [
            'Authorization' => 'Bearer ' . $login->access_token,
        ];

        $update_deals = [
            "creator_id" => $data['assigned'],
            "name" => "Leads - " . $data['name'],
            "crm_pipeline_id" => 56932,
            "crm_stage_id" => $data['status'],
            "currency" => "USD",
            "size" => $data['size'],
            "additional_fields" => [
                [
                    "id" => 4415397,
                    "value" => $data['meta_account'],
                ],
                [
                    "id" => 4415398,
                    "value" => date('d/m/Y', strtotime($data['meta_created_date'])),
                ],
                [
                    "id" => 4428993,
                    "value" => $data['sales_name'],
                ],
            ],
        ];

        $client = new \GuzzleHttp\Client([
            'headers' => $headers,
            'allow_redirects' => false,
            'http_errors' => false,
        ]);
        $response = $client->request('PUT', 'https://www.qontak.com/api/v3.1/deals/' . $lead_id, [
            'json' => $update_deals,
        ]);

        if (isset($response) && $response->getStatusCode() == 200) {
            return true;
        } elseif ($response->getStatusCode() == 401) {
            QontakController::qontak_login();
            return QontakController::qontak_update_leads($data, $lead_id);
        } elseif ($response->getStatusCode() == 422) {
            return null;
        }
    }
}
