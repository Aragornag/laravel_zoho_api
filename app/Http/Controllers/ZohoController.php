<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class ZohoController extends Controller
{
    public function auth(Request $request)
    {
        $redirectTo = 'https://accounts.zoho.com/oauth/v2/auth' . '?' . http_build_query(
                [
                    'scope' => 'ZohoCRM.modules.ALL',
                    'client_id' => Config::get('services.zoho.client_id'),
                    'response_type' => 'code',
                    'access_type' => Config::get('services.zoho.access_type'),
                    'redirect_uri' => Config::get('services.zoho.redirect_uri'),
                ]);

        return redirect($redirectTo);
    }


    public function store(Request $request)
    {
        if (isset($tokenResult->refresh_token) && $tokenResult->refresh_token != '') {
            $input = $request->all();
            $client_id = Config::get('services.zoho.client_id');
            $client_secret = Config::get('services.zoho.client_secret');


            // Get ZohoCRM Token
            $tokenData = [];
            $tokenUrl = 'https://accounts.zoho.com/oauth/v2/token?refresh_token=' . $tokenResult->refresh_token . '&client_id=' . $client_id . '&client_secret=' . $client_secret . '&grant_type=refresh_token';

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_VERBOSE, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($curl, CURLOPT_TIMEOUT, 300);
            curl_setopt($curl, CURLOPT_POST, TRUE);//Regular post
            curl_setopt($curl, CURLOPT_URL, $tokenUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($tokenData));

            $tResult = curl_exec($curl);
            curl_close($curl);
            $tokenResult = json_decode($tResult);
			
        } else {
            $input = $request->all();
            $client_id = Config::get('services.zoho.client_id');
            $client_secret = Config::get('services.zoho.client_secret');

            $tokenUrl = 'https://accounts.zoho.com/oauth/v2/token?code=' . $input["code"] . '&client_id=' . $client_id . '&client_secret=' . $client_secret . '&redirect_uri=' . Config::get('services.zoho.redirect_uri') . '&grant_type=authorization_code';

            $tokenData = [];

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_VERBOSE, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($curl, CURLOPT_TIMEOUT, 300);
            curl_setopt($curl, CURLOPT_POST, TRUE);//Regular post
            curl_setopt($curl, CURLOPT_URL, $tokenUrl);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($tokenData));

            $tResult = curl_exec($curl);
            curl_close($curl);
            $tokenResult = json_decode($tResult);
        }

        if (isset($tokenResult->access_token) && $tokenResult->access_token != '') {

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://www.zohoapis.com/crm/v2/Deals',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                    "data":[
                        {
                            "Deal_Name":"NewDeal123",
                            "Stage":"Qualification"
                        }
                    ]
                }',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Zoho-oauthtoken ' . $tokenResult->access_token,
                    'Content-Type: text/plain',
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $contactResponse = json_decode($response);
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://www.zohoapis.com/crm/v2/tasks',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                    "data":[
                        {
                            "Subject": "taskapi2",
                            "se_module": "Deals",
                            "What_Id": "' . $contactResponse->data[0]->details->id . '",
                        }
                    ]
                }',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Zoho-oauthtoken ' . $tokenResult->access_token,
                    'Content-Type: text/plain',
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $contactResponse1 = json_decode($response);

            return dd($contactResponse1);
        }
        return $tokenResult;
    }
}
