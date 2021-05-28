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

class AgentController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('agent.index');
    }
	
	public function getAgentGrid()
    {
		$info_Agent = Agent::All();
	   	return DataTables::of($info_Agent)
		->escapeColumns([])
		->make(true);
    }
	public function getAgent()
    {
        return view('agent.agent');
    }
	
	public function getNewAgent()
    {
        return view('agent.newagent');
    }
	
	public function importNewAgent(Request $request)
    {
		$request->validate([
            'agents_file' => 'required|file'
        ]);
		
		$path = $request->file('agents_file')->getRealPath();
    	$TotalRows = array_map('str_getcsv', file($path));
		$Rows = array_slice($TotalRows, 1);
		
		$info_Agent_Count = 0;
		foreach($Rows as $Row)
		{
			//dd($Row[2]);
			if(isset($Row[2]) && $Row[2] != "")
			{
				$info_Agent = Agent::Where('msisdn','234'.substr($Row[2],-10))->Where('type', $Row[5])->First();
				if($info_Agent)
				{
					$info_Agent_Count = $info_Agent_Count+1;
				}
			}
		}
		
		if($info_Agent_Count > 0)
		{
			Session::flash('error_message', "Upload fails (Duplicate no.)");
			return Redirect::back();
		}

		$updatedRows = [];
		foreach($Rows as $Row)
		{
			if(isset($Row[2]) && $Row[2] != "")
			{
				//dd($Row);
				/*$db_Driver = new Driver;
				$db_Driver->fullname = $Row[0];
				$db_Driver->msisdn = '234'.substr($Row[2],-10);
				$db_Driver->plateno = $Row[0];
				$db_Driver->atin = $Row[1];
				$db_Driver->save();*/
				
				$db_Agent = new Agent;
				$db_Agent->name = $Row[0];
				$db_Agent->msisdn = '234'.substr($Row[2],-10);
				$db_Agent->email = $Row[3];
				$db_Agent->address = $Row[4];
				$db_Agent->type = $Row[5];
				$db_Agent->atin = $Row[1];
				
				if(strtolower($Row[5]) == "okada")
				{
					$db_Agent->account_reference = '234'.substr($Row[2],-10).'O';
				}
				else
				if(strtolower($Row[5]) == "keke")
				{
					$db_Agent->account_reference = '234'.substr($Row[2],-10).'K';
				}
				else
				if(strtolower($Row[5]) == "others")
				{
					$db_Agent->account_reference = '234'.substr($Row[2],-10).'S';
				}
				
				$db_Agent->save();
				
				$accountCreatedResponse = $this->monnifyReserveAccount("LIVE", '234'.substr($Row[2],-10), $db_Agent->account_reference, $Row[0], $Row[3], $Row[5]);
				$responseData = json_decode($accountCreatedResponse);
				if($responseData->requestSuccessful)
				{
					$message = "Dear ".$Row[0].",\nYour Agent account number is ".$responseData->responseBody->accountNumber." in Sterling Bank.\nUse USSD, POS or Mobile App to pay for your Ticket Order. Dial *8014*99# to check payment.";
					
					\Log::info("Send SMS: http://3.131.19.214:8802/?phonenumber=".('234'.substr($Row[2],-10))."&text=".urlencode($message)."&sender=SELFSERVE&user=selfserve&password=123456789");
					file_get_contents("http://3.131.19.214:8802/?phonenumber=".('234'.substr($Row[2],-10))."&text=".urlencode($message)."&sender=SELFSERVE&user=selfserve&password=123456789");
					
					
					$updatedRows[] = $Row;

					//$db_Driver->accountno = $responseData->responseBody->accountNumber;
					//$db_Driver->save();
					
					$db_Agent->accountcreatedresponse = $accountCreatedResponse;
					$db_Agent->accountno = $responseData->responseBody->accountNumber;
					$db_Agent->save();
				}
				else
				{
					Session::flash('error_message', "Create account fail");
					Session::flash('updatedRows', $updatedRows);
					//$db_Driver->delete();
					$db_Agent->delete();
					return Redirect::back();
				}
			
			}
			
			///print_r($db_Driver);
			///print_r($db_Agent);
		}
		
		Session::flash('updatedRows', $updatedRows);
		Session::flash('success_message', "File uploaded");
		//dd(0);
        return Redirect('newagent');
    }
	
	function monnifyReserveAccount($istest = "LIVE", $msisdn, $account_reference, $fullname, $customerEmail, $type) 
	{
		$url = "https://api.monnify.com/api/v1/bank-transfer/reserved-accounts";
		$login_url = "https://api.monnify.com/api/v1/auth/login";
		if ($istest == "TEST") 
		{
			$url = "https://sandbox.monnify.com/api/v1/bank-transfer/reserved-accounts";
			$login_url = "https://sandbox.monnify.com/api/v1/auth/login";
		}
		$customerEmail =  ($customerEmail == "NA" ?  "selfserverng@gmail.com" : $customerEmail);
		$customerEmail =  ($customerEmail == "N/A" ?  "selfserverng@gmail.com" : $customerEmail);
		
		if(strtolower($type) == "okada")
		{
			/*$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_696124412434", "feePercentage" => 0, "splitPercentage" => 57.5, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_777366599327", "feePercentage" => 0, "splitPercentage" => 6.25, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_396131675212", "feePercentage" => 0, "splitPercentage" => 11.5, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_923548935133", "feePercentage" => 0, "splitPercentage" => 11.5, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_473624252548", "feePercentage" => 0, "splitPercentage" => 5.75, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_837784943424", "feePercentage" => 100, "splitPercentage" => 7.5, "feeBearer" => true);*/
			/*$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_777366599327", "feePercentage" => 0, "splitPercentage" => 12.95, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_473624252548", "feePercentage" => 0, "splitPercentage" => 11.92, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_696124412434", "feePercentage" => 0, "splitPercentage" => 59.59, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_837784943424", "feePercentage" => 100, "splitPercentage" => 15.54, "feeBearer" => true);*/

			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_777366599327", "feePercentage" => 0, "splitPercentage" => 6.94, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_473624252548", "feePercentage" => 0, "splitPercentage" => 4.54, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_696124412434", "feePercentage" => 0, "splitPercentage" => 31.94, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_837784943424", "feePercentage" => 100, "splitPercentage" => 8.25, "feeBearer" => true);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_317431997858", "feePercentage" => 0, "splitPercentage" => 48.33, "feeBearer" => false);

			$username = 'MK_PROD_9V6KP4EZ5T';
			$password = '3VBV6G53V9STR554ZYW4H2T4EH72WERP';
			$contractCode = "725275533691";
		}
		else
		if(strtolower($type) == "keke")
		{
			/*$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_347125728976", "feePercentage" => 0, "splitPercentage" => 74.68, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_476695577389", "feePercentage" => 0, "splitPercentage" => 8.12, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_489851861579", "feePercentage" => 100, "splitPercentage" => 9.73, "feeBearer" => true);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_727483551233", "feePercentage" => 0, "splitPercentage" => 7.47, "feeBearer" => false);*/

			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_476695577389", "feePercentage" => 0, "splitPercentage" => 23.24, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_727483551233", "feePercentage" => 0, "splitPercentage" => 4.54, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_347125728976", "feePercentage" => 0, "splitPercentage" => 63.89, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_489851861579", "feePercentage" => 100, "splitPercentage" => 8.33, "feeBearer" => true);
			
			$username = 'MK_PROD_4Y65HT74M4';
			$password = 'HG55Q22MRS4DPXZSK8KHWV9GLKDFC82Z';
			$contractCode = "662131815131";
		}
		else
		if(strtolower($type) == "others")
		{
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_794774544824", "feePercentage" => 0, "splitPercentage" => 6.25, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_393441595619", "feePercentage" => 0, "splitPercentage" => 86.25, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_774412756725", "feePercentage" => 0, "splitPercentage" => 6.25, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_884513776734", "feePercentage" => 100, "splitPercentage" => 1.25, "feeBearer" => true);
			
			$username = 'MK_PROD_QAC28QUESH';
			$password = 'QNZXRQPRRZ4ATFWQZYBEE2QUU7QXRF3G';
			$contractCode = "768651769665";
		}
		
		$accountname = strtoupper($fullname);
		$data = json_encode(array("accountReference" => $account_reference, "accountName"  => $accountname, "currencyCode" => "NGN", "contractCode" => $contractCode, "customerEmail" => $customerEmail, "customerName" => $fullname, "incomeSplitConfig" => $incomeSplitconfig));
		
		
		
		//Login
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $login_url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_HTTPHEADER => array(
				"Authorization: Basic " . base64_encode("$username:$password")
			),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		
		$responseData = json_decode($response);
		$token = ($responseData->responseBody->accessToken);
		
		//Call Account
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json",
				"Authorization: Bearer " . $token
			),
		));
		$response = curl_exec($curl);
		\Log::info('monnifyReserveAccount: '.$response);
		curl_close($curl);
		
		return $response;
	}
	
	function MonnifyCallback(Request $request)
	{
		$json = (file_get_contents('php://input'));
		$decodeData = json_decode($json);

		\Log::info('MonnifyCallback: '.$json);
		\Log::info('Msisdn: '.'234'.substr($decodeData->product->reference,3));		
		
		$info_Agent = Agent::Where('account_reference','234'.substr($decodeData->product->reference,3))->First();
		$db_payment = new Payment;
		$db_payment->userid = $info_Agent->id;
		$db_payment->paymentref = $decodeData->paymentReference;
		$db_payment->transactionreference = $decodeData->transactionReference;
		$db_payment->amountPaid =  $decodeData->amountPaid;
		$db_payment->totalPayable =  $decodeData->totalPayable;
		$db_payment->settlementAmount =  $decodeData->settlementAmount;
		$db_payment->paidon =  $decodeData->paidOn;
		$db_payment->paymentmethod =  $decodeData->paymentMethod;
		$db_payment->paymentstatus =  $decodeData->paymentStatus;
		$db_payment->datecreated = date('Y-m-d');
		$db_payment->request_dump = $json;
		$db_payment->channel = 'Monnify';
		$db_payment->save();
		
		//Send SMS
		$transactionReference = explode("|",$decodeData->transactionReference);
		$message = "Reciept:\nDate: ".$decodeData->paidOn."\nRef: ".$decodeData->transactionReference."\nPayer: ".$info_Agent->name."\nAmount Paid: N".$decodeData->totalPayable."\nID: ".$info_Agent->atin."\nDial *8014*99# to confirm payments";
		
		\Log::info("Send SMS: http://3.131.19.214:8802/?phonenumber=".$info_Agent->msisdn."&text=".urlencode($message)."&sender=SELFSERVE&user=selfserve&password=123456789");
		file_get_contents("http://3.131.19.214:8802/?phonenumber=".$info_Agent->msisdn."&text=".urlencode($message)."&sender=SELFSERVE&user=selfserve&password=123456789");


		//IBRIS
		$data = '{"paymentGatewayProvider": "selfserve","paymentProviderNotificationLogId": "'.($db_payment->id*1000).'","paymentProviderReferenceNumber": "'.$decodeData->paymentReference.'","paymentDate": '.(\DateTime::createFromFormat('d/m/Y g:i:s A', $decodeData->paidOn)->format('"d-m-Y H:i:s"')).',"paymentProviderCustomerName": "'.$info_Agent->name.'","paymentProviderCustomerPhoneNumber": "'.$decodeData->product->reference.'","paymentProviderCustomerReference": "'.$info_Agent->atin.'","paymentProviderChannel": "ussd","totalAmountInKobo": '.((int)($decodeData->totalPayable)*100).',"paymentLineItem": [{"amountPaidInKobo": '.((int)($decodeData->totalPayable)*100).',"paymentAgencyCode": "20008001","paymentRevenueCode": "12040275"}],"taxPayerIdentificationNumber": "'.$info_Agent->atin.'","taxYear": "2021"}';
		\Log::info('IBRIS Payload: '.$data);
		
		//Test
		//$hashed = hash("sha512", $data.'7vczyovkpjD+co6yW9OfSUW8fTN8f4CP2Hc/JHm6Wlk=');
		//Live
		$hashed = hash("sha512", $data.'pg88L85MXyj6Nedr0j+6sOui6ubhP6jB2oZPlJtfQPk=');
		
		
		$db_payment->ibris_request_dump = $data;
		$db_payment->save();
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		//CURLOPT_URL => 'http://ics3staging.abiairs.gov.ng/assessment-api/api/vendor/payment/validation', //Test
		CURLOPT_URL => 'https://www.abiairs.gov.ng/assessment-api/api/vendor/payment/notification', //Live
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_HTTPHEADER => array(
				//"vendorCode: ACCT0000013435", //Test
				"vendorCode: ACCT0000059919", //Live
				"hash:" . $hashed
			),
		));
		$response = curl_exec($curl);
		\Log::info('IBRIS Response: '.$response);
		curl_close($curl);
		$responseData = json_decode($response);
		//print_r($responseData);

		$db_payment->PaymentRetrivialReference = $responseData->body->paymentRetrievalReference;
		$db_payment->save();
		
		//print_r($responseData);

		return "OK";
	}
}
