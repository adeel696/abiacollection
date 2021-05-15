<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables;
use Session;
use Redirect;
use App\Models\Driver;
use App\Models\Agent;
use App\Models\Payment;
use DB;

class PaymentController extends Controller
{
	
	public function RecallIbris(Payment $payment)
    {
		$db_payment = $payment;
		
		$data = $db_payment->ibris_request_dump;
		if($data != "")
		{
			\Log::info('IBRIS Payload Recall: '.$data);
			$hashed = hash("sha512", $data.'pg88L85MXyj6Nedr0j+6sOui6ubhP6jB2oZPlJtfQPk=');
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://www.abiairs.gov.ng/assessment-api/api/vendor/payment/notification',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array(
					"vendorCode: ACCT0000059919",
					"hash:" . $hashed
				),
			));
			$response = curl_exec($curl);
			\Log::info('IBRIS Response Recall: '.$response);
			curl_close($curl);
			$responseData = json_decode($response);
			//print_r($responseData);
	
			$db_payment->PaymentRetrivialReference = $responseData->body->paymentRetrievalReference;
			$db_payment->save();
			
			return "OK";
		}
		else
		{
			return "None";
		}
    }
	
	public function getPaymentGrid(Request $request)
    {
		if(isset($request->Search))
		{
			$info_Payment = Payment::Select('payment.id', 'payment.userid', 'payment.paymentref', 'payment.PaymentRetrivialReference','payment.transactionreference', 'payment.amountPaid', 'payment.totalPayable', 'payment.settlementAmount', 'payment.paidon', 'payment.paymentmethod', 'payment.paymentstatus', 'payment.datecreated', 'payment.request_dump', 'payment.channel', 'agents.name', 'agents.msisdn', 'agents.email', 'agents.address', 'agents.type')->join('agents', 'payment.userid', '=', 'agents.id')->where('datecreated', '>=' , $request->From)->where('datecreated', '<=' , $request->To)->Get();
		}
		else
		{
			$info_Payment = Payment::Select('payment.id', 'payment.userid', 'payment.paymentref', 'payment.PaymentRetrivialReference','payment.transactionreference', 'payment.amountPaid', 'payment.totalPayable', 'payment.settlementAmount', 'payment.paidon', 'payment.paymentmethod', 'payment.paymentstatus', 'payment.datecreated', 'payment.request_dump', 'payment.channel', 'agents.name', 'agents.msisdn', 'agents.email', 'agents.address', 'agents.type')->join('agents', 'payment.userid', '=', 'agents.id')->Get();
		}
		
	   	return DataTables::of($info_Payment)
		->addColumn('edit', function ($info_Payment) {
				 return '<div class="btn-group btn-group-action">
								<a class="btn btn-info" style="margin-right:2px;" id="btnResend" href="javascript(0)" data-remote="'.url('/payment/recallibris/'.$info_Payment->id).'" title="Resend Data">Resend CS</a> 
                                </div>';
        })
		->escapeColumns([])
		->make(true);
    }
	public function getPayment()
    {
        return view('payment.index');
    }
}
