<?php

namespace App\Http\Controllers;

use App\Models\EndYearEnrollment;
use App\Models\EndYearPackage;
use App\Models\EndYearClaim;
use Illuminate\Http\Request;

class EndYearController extends Controller
{
    public function enroll_create()
    {
        $packages = EndYearPackage::orderBy('id', 'asc')
            ->get();
        foreach($packages as $package){
            $package->min = $package->min/1000000;
            $package->max = $package->max/1000000;
        }
        return view('freeholiday.enroll', compact('packages'));
    }
    public function enroll_store(Request $request)
    {
        $packages = EndYearPackage::orderBy('id', 'asc')
            ->get();
        foreach($packages as $package){
            $package->min = $package->min/1000000;
            $package->max = $package->max/1000000;
        }

        $request->validate([
            'name' => "required|max:255|string",
            'account' => "required|numeric|min:40000000|max:49999999",
            'account_type' => "required|string|max:255",
            'phone' => "required|numeric",
            'email' => "required|email",
            'source' => "required|string|max:255",
            'new_account' => "required|numeric|max:1|min:0",
            'deposit' => "required|numeric|max:999999999",
            'endyear_packages_id' => "required|numeric",
            'sales_name' => "required|string|max:255",
        ]);

        $endyear_package_id = EndYearPackage::where('id', $request->endyear_packages_id)->first();
        if($request->deposit < $endyear_package_id->deposit){
            return redirect()
            ->back()
            ->withErrors('Deposit tidak mencapai batas minimal dari paket yang anda pilih.')
            ->withInput($request->all());
        }

        $request->phone = preg_replace("/^0|62+/","",$request->phone);

        $enrollment_data = [
            'name' => $request->name,
            'account' => $request->account,
            'account_type' => $request->account_type,
            'phone' => $request->phone,
            'email' => $request->email,
            'source' => $request->source,
            'new_account' => $request->new_account,
            'deposit' => $request->deposit,
            'created_at' => date('Y-m-d H:i:s'),
            'endyear_packages_id' => $request->endyear_packages_id,
            'sales_name' => $request->sales_name
        ];

        $enrollment_id = EndYearEnrollment::insertGetId($enrollment_data);

        if(isset($enrollment_id)){
            $client = new \GuzzleHttp\Client([
                'allow_redirects' => false,
                'http_errors' => false,
            ]);
            $response = $client->request('POST', 'https://hook.us1.make.com/kor1wu73ks7brq7uxkl8f6e2akcm1uv5', [
                'json' => $enrollment_data,
            ]);
        }

        if ($response->getStatusCode() == 200) {
            return redirect('/free-holiday/enrollment')
            ->with(compact('enrollment_id','packages'))
            ->withSuccess('Enrollment anda sudah berhasil diterima oleh admin. Silakan catat Enrollment ID anda adalah ' . $enrollment_id);
        } else {
            return redirect()
            ->back()
            ->withErrors('Terjadi kesalahan, silakan hubungi admin.')
            ->withInput($request->all());
        }
    }
    public function claim_create()
    {
        $packages = EndYearPackage::orderBy('id', 'asc')
            ->get();
        foreach($packages as $package){
            $package->min = $package->min/1000000;
            $package->max = $package->max/1000000;
        }
        return view('freeholiday.claim', compact('packages'));
    }
    public function claim_store(Request $request)
    {
        $packages = EndYearPackage::orderBy('id', 'asc')
            ->get();
        foreach($packages as $package){
            $package->min = $package->min/1000000;
            $package->max = $package->max/1000000;
        }

        $request->validate([
            'name' => "required|max:255|string",
            'account' => "required|numeric|min:40000000|max:49999999",
            'account_type' => "required|string|max:255",
            'phone' => "required|numeric",
            'email' => "required|email",
            'new_account' => "required|numeric|max:1|min:0",
            'deposit' => "required|numeric|max:999999999",
            'date_enroll' => "required|date",
            'endyear_packages_id' => "required|numeric",
            'sales_name' => "required|string|max:255",
            'name_receiver' => "required|max:255|string",
            'address_receiver' => "required|max:255|string",
            'name_spv' => "required|max:255|string",
            'phone_sales' => "required|numeric",
            'phone_receiver' => "required|numeric",
        ]);

        $request->phone = preg_replace("/^0|62+/","",$request->phone);
        $request->phone_sales = preg_replace("/^0|62+/","",$request->phone);
        $request->phone_receiver = preg_replace("/^0|62+/","",$request->phone);

        $claim_data = [
            'name' => $request->name,
            'account' => $request->account,
            'account_type' => $request->account_type,
            'phone' => $request->phone,
            'email' => $request->email,
            'new_account' => $request->new_account,
            'deposit' => $request->deposit,
            'date_enroll' => $request->date_enroll,
            'created_at' => date('Y-m-d H:i:s'),
            'endyear_packages_id' => $request->endyear_packages_id,
            'sales_name' => $request->sales_name,
            'name_receiver' => $request->name_receiver,
            'address_receiver' => $request->address_receiver,
            'name_spv' => $request->name_spv,
            'phone_sales' => $request->phone_sales,
            'phone_receiver' => $request->phone_receiver,
        ];

        $claim_id = EndYearClaim::insertGetId($claim_data);

        if(isset($claim_id)){
            $client = new \GuzzleHttp\Client([
                'allow_redirects' => false,
                'http_errors' => false,
            ]);
            $response = $client->request('POST', 'https://hook.us1.make.com/yiub1qnuqec70rv5xrb73qix1nj68in8', [
                'json' => $claim_data,
            ]);
        }

        if ($response->getStatusCode() == 200) {
            return redirect('/free-holiday/claim')
            ->with(compact('claim_id','packages'))
            ->withSuccess('Klaim anda sudah berhasil diterima oleh admin. Silakan catat Claim ID anda adalah ' . $claim_id);
        } else {
            return redirect()
            ->back()
            ->withErrors('Terjadi kesalahan, silakan hubungi admin.')
            ->withInput($request->all());
        }
    }
}
