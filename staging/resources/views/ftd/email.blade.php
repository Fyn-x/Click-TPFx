@component('mail::message')
| Name | Data |
| :--- | :--- |
| Nama Lengkap | {{ $input['name'] }} |
| Email | {{ $input['email'] }}|
| Sumber FTD | {{ $input['source'] }} |
@isset($input['source_detail'])
| Sumber CRM | {{ $input['source_detail'] }} |
@endisset
| Nasabah/IB | {{ $input['master_ib'] }}|
@isset($input['name_ib'])
@if(count($input['name_ib']) > 0)
@foreach ($input['name_ib'] as $key=>$ib)
| Nama IB {{ $key+1 }} | {{ $input['name_ib'][$key] }} |
@if($input['email_ib'][$key] != "")
| Email IB {{ $key+1 }} | {{ $input['email_ib'][$key] }} |
@endif
| Rebate Metal IB {{ $key+1 }} | {{ $input['metal_ib'][$key] }} |
| Rebate Forex IB {{ $key+1 }} | {{ $input['forex_ib'][$key] }} |
| Rebate index IB {{ $key+1 }} | {{ $input['index_ib'][$key] }}|
@if($input['cfd_ib'][$key] != "")
| Rebate CFD IB {{ $key+1 }} | {{ $input['cfd_ib'][$key] }} |
@endif
@endforeach
@endif
@endisset
@if ($input['master_ib'] != "IB")
| Tipe Akun | {{ $input['account_type'] }} |
@isset($input['commission'])
| Komisi | $ {{ $input['commission'] }} |
@endisset
@isset($input['freecomm'])
| Free Commission | {{ $input['freecomm'] }} |
@endisset
@empty($input['commission'])
| Komisi | Free Commission |
@endempty
| Mark Up | $ {{ $input['markup'] }} |
| Rate | {{ $input['rate'] }} |
| Leverage | 1:{{ $input['leverage'] }} |
| Amount | $ {{ $input['amount'] }} |
@endif
| Nama Marketing | {{ $input['name_marketing'] }} |
| Email Marketing | {{ $input['email_marketing'] }} |
| Nama TL/BDM | {{ $input['name_tl'] }} |
| Nama SPV/SBM | {{ $input['name_spv'] }} |
@if($input['name_assm'] != "")
| Nama Assistant Manager | {{ $input['name_assm'] }} |
@endif
| Nama Group | {{ $input['group'] }} |
@isset($input['notes'])
| Notes | {{ $input['notes'] }} |
@endisset
@endcomponent
