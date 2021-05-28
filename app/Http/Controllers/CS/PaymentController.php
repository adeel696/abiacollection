<?php

namespace App\Http\Controllers\CS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables;
use App\Models\PaymentAtin;
use App\Models\SendCsLog;
use App\Models\ShopFee;
use App\Imports\PaymentAtinImport;
use Maatwebsite\Excel\Facades\Excel;
use Session;
use Redirect;
use DB;

class PaymentController extends Controller
{
	
	public function CallCS()
	{
		$info_PaymentAtins = PaymentAtin::All()->Where('id','=','15112');
		$info_ShopFees = ShopFee::OrderBy('id')->Get();
		foreach($info_PaymentAtins as $info_PaymentAtin)
		{
			$info_SendCsLogs = SendCsLog::Where('payment_atin_id',$info_PaymentAtin->id)->OrderBy('shop_fees_id', 'desc')->First();
			
			$currentPayRow=0;
			if($info_SendCsLogs)
			{
				$currentPayRow = $info_SendCsLogs->shop_fees_id;
			}
			
			$CurrentAmount = $info_PaymentAtin->amount;
			for($i = $currentPayRow; $i <=6; $i++)
			{
				if($CurrentAmount >= $info_ShopFees[$i]->fixed_fee)
				{
					
					$db_SendCsLog = new SendCsLog;
					$db_SendCsLog->payment_atin_id = $info_PaymentAtin->id;
					$db_SendCsLog->current_atin_amount = $CurrentAmount;
					$db_SendCsLog->shop_fees_id = $info_ShopFees[$i]->id;
					$db_SendCsLog->amount = $info_ShopFees[$i]->fixed_fee;
					$db_SendCsLog->created_at = date('Y-m-d h:i:s');
					$db_SendCsLog->Save();
					
					$CurrentAmount = $CurrentAmount-$info_ShopFees[$i]->fixed_fee;
					
					
					$data = '{"paymentGatewayProvider": "selfserve","paymentProviderNotificationLogId": "TBD","paymentProviderReferenceNumber": "'.$info_PaymentAtin->payment_id.'","paymentDate": '.(date("d-m-Y H:i:s")).',"paymentProviderCustomerName": "'.$info_PaymentAtin->store_name.'","paymentProviderCustomerPhoneNumber": "2349085465633","paymentProviderCustomerReference": "'.$info_PaymentAtin->atin.'","paymentProviderChannel": "ussd","totalAmountInKobo": '.((int)($info_ShopFees[$i]->fixed_fee)*100).',"paymentLineItem": [{"amountPaidInKobo": '.((int)($info_ShopFees[$i]->fixed_fee)*100).',"paymentAgencyCode": "20008001","paymentRevenueCode": "12010002"}],"taxPayerIdentificationNumber": "'.$info_PaymentAtin->atin.'","taxYear": "2021"}';
					\Log::info('IBRIS Payload: '.$data);
					
					//Test
					$hashed = hash("sha512", $data.'7vczyovkpjD+co6yW9OfSUW8fTN8f4CP2Hc/JHm6Wlk=');
					//Live
					//$hashed = hash("sha512", $data.'pg88L85MXyj6Nedr0j+6sOui6ubhP6jB2oZPlJtfQPk=');
					
					$db_SendCsLog->transactionreference = $info_PaymentAtin->payment_id;
					$db_SendCsLog->request_dump = $data;
					$db_SendCsLog->save();
					
					$curl = curl_init();
					curl_setopt_array($curl, array(
					CURLOPT_URL => 'http://ics3staging.abiairs.gov.ng/assessment-api/api/vendor/payment/validation', //Test
					//CURLOPT_URL => 'https://www.abiairs.gov.ng/assessment-api/api/vendor/payment/notification', //Live
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "POST",
					CURLOPT_POSTFIELDS => $data,
					CURLOPT_HTTPHEADER => array(
							"vendorCode: ACCT0000013435", //Test
							//"vendorCode: ACCT0000059919", //Live
							"hash:" . $hashed
						),
					));
					$response = curl_exec($curl);
					\Log::info('IBRIS Response: '.$response);
					curl_close($curl);
					$responseData = json_decode($response);
					//print_r($responseData);
					
					$db_SendCsLog->ibris_dump = $response;
					if(isset($responseData->body->paymentRetrievalReference))
						$db_SendCsLog->PaymentRetrivialReference = $responseData->body->paymentRetrievalReference;
					$db_SendCsLog->save();
					
				}
			}
			
			$info_PaymentAtin->amount = $CurrentAmount;
			$info_PaymentAtin->Save();
		}
		return 1;;
	}
	
	public function getImportPaymentAtin()
	{
		return view('cs.import');
	}
	
	public function importPaymentAtin(Request $request)
    {
		$request->validate([
            'payment_file' => 'required|file'
        ]);
		
		$paymentAtinImport = new PaymentAtinImport;
        Excel::import($paymentAtinImport,request()->file('payment_file'));
		
		if($paymentAtinImport->Error == "")
		{
			Session::flash('success_message', "File uploaded");
        	return Redirect('cs/import');
		}
		else
		{
			Session::flash('error_message', $paymentAtinImport->Error);
        	return Redirect('cs/import');
		}
    }
	
	
	public function getPaymentAtin()
	{
		return view('cs.paymentatin');
	}
	
	public function PaymentAtinGrid()
	{
		$info_PaymentAtin = PaymentAtin::All();
		
	   	return DataTables::of($info_PaymentAtin)
/*		->addColumn('edit', function ($info_Payment) {
				 return '<div class="btn-group btn-group-action">
								<a class="btn btn-info" style="margin-right:2px;" id="btnResend" href="javascript(0)" data-remote="'.url('/payment/recallibris/'.$info_Payment->id).'" title="Resend Data">Resend CS</a> 
                                </div>';
        })*/
		->escapeColumns([])
		->make(true);
	}
	
	public function getCSLog()
	{
		return view('cs.cslog');
	}
	
	public function CSLogGrid()
	{
		$info_SendCsLog = SendCsLog::All();
		
	   	return DataTables::of($info_SendCsLog)
		->editColumn('payment_atin_id', function ($info_SendCsLog) {
				 return $info_SendCsLog->PaymentAtin()->First()->atin;
        })
		->editColumn('shop_fees_id', function ($info_SendCsLog) {
				 return $info_SendCsLog->ShopFee()->First()->revenue_name;
        })
		->escapeColumns([])
		->make(true);
	}
	
}
