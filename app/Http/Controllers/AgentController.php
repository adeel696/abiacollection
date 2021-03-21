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
		
		$info_Driver_Count = Driver::WhereIn('msisdn',array_column($Rows,2))->Get()->Count();
		
		if($info_Driver_Count > 0)
		{
			Session::flash('error_message', "Upload fails (Duplicate no.)");
			return Redirect::back();
		}
		$updatedRows = [];
		foreach($Rows as $Row)
		{
			//dd($Row);
			$db_Driver = new Driver;
			$db_Driver->fullname = $Row[0];
			$db_Driver->msisdn = $Row[2];
			$db_Driver->plateno = $Row[0];
			$db_Driver->save();
			
			$db_Agent = new Agent;
			$db_Agent->name = $Row[0];
			$db_Agent->msisdn = $Row[2];
			$db_Agent->email = $Row[3];
			$db_Agent->address = $Row[4];
			$db_Agent->type = $Row[5];
			$db_Agent->save();
			
			$accountCreatedResponse = $this->monnifyReserveAccount("LIVE", $Row[2], $Row[0], $Row[3], $Row[5]);
			$responseData = json_decode($accountCreatedResponse);
			if($responseData->requestSuccessful)
			{
				$message = "Your monnify account has been created successfully";
				file_get_contents("https://app.multitexter.com/v2/app/sms?email=tech@iyconsoft.com&message=".urlencode($message)."&recipients=".$Row[2]."&forcednd=1&password=sayntt123&sender_name=SELFSERVE");
				$updatedRows[] = $Row;
				$db_Driver->accountno = $responseData->responseBody->accountNumber;
				$db_Driver->save();
				$db_Agent->accountcreatedresponse = $accountCreatedResponse;
				$db_Agent->accountno = $responseData->responseBody->accountNumber;
				$db_Agent->save();
			}
			else
			{
				Session::flash('error_message', "Create account fail");
				Session::flash('updatedRows', $updatedRows);
				$db_Driver->delete();
				$db_Agent->delete();
				return Redirect::back();
			}
			
			
			
			///print_r($db_Driver);
			///print_r($db_Agent);
		}
		
		Session::flash('updatedRows', $updatedRows);
		Session::flash('success_message', "File uploaded");
		//dd(0);
        return Redirect('newagent');
    }
	
	function monnifyReserveAccount($istest = "LIVE", $msisdn, $fullname, $customerEmail, $type) 
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
		
		if($type == "independent")
		{
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_696124412434", "feePercentage" => 0, "splitPercentage" => 57.5, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_777366599327", "feePercentage" => 0, "splitPercentage" => 6.25, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_396131675212", "feePercentage" => 0, "splitPercentage" => 11.5, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_923548935133", "feePercentage" => 0, "splitPercentage" => 11.5, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_837784943424", "feePercentage" => 100, "splitPercentage" => 7.5, "feeBearer" => true);
			$username = 'MK_PROD_9V6KP4EZ5T';
			$password = '3VBV6G53V9STR554ZYW4H2T4EH72WERP';
			$contractCode = "725275533691";
		}
		else
		{
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_347125728976", "feePercentage" => 0, "splitPercentage" => 74.68, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_476695577389", "feePercentage" => 0, "splitPercentage" => 8.12, "feeBearer" => false);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_489851861579", "feePercentage" => 100, "splitPercentage" => 9.73, "feeBearer" => true);
			$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_727483551233", "feePercentage" => 0, "splitPercentage" => 7.47, "feeBearer" => false);
			
			$username = 'MK_PROD_4Y65HT74M4';
			$password = 'HG55Q22MRS4DPXZSK8KHWV9GLKDFC82Z';
			$contractCode = "662131815131";
		}
		
		$accountname = strtoupper($fullname);
		$data = json_encode(array("accountReference" => 'z1012'.$msisdn, "accountName"  => $accountname, "currencyCode" => "NGN", "contractCode" => $contractCode, "customerEmail" => $customerEmail, "customerName" => $fullname, "incomeSplitConfig" => $incomeSplitconfig));
		
		
		
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
		curl_close($curl);
		
		return $response;
	}
	
	function MonnifyCallback(Request $request)
	{
		$json = (file_get_contents('php://input'));
		$decodeData = json_decode($json);
				
		$transactionReference = explode("|",$decodeData->transactionReference);
		$message = "Reciept:\nDate: ".$decodeData->paidOn."\nRef: ".$decodeData->transactionReference."\nPayer: ".$decodeData->accountDetails->accountName."\nAmount Paid: N".$decodeData->totalPayable."\nID: ".$transactionReference[2]."\nDial *8014*99# to confirm payments";
		
		$info_Agent = Agent::Where('msisdn',$decodeData->product->reference)->First();
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
		//$db_payment->save();
		
		
		//IBRIS
		$data = '{"paymentGatewayProvider": "lyconsoft","paymentProviderNotificationLogId": "11123","paymentProviderReferenceNumber": "'.$decodeData->paymentReference.'","paymentDate": "'.$decodeData->paidOn.'","paymentProviderCustomerName": "'.$decodeData->accountDetails->accountName.'","paymentProviderCustomerPhoneNumber": "'.$decodeData->product->reference.'","paymentProviderCustomerReference": "'.$decodeData->product->reference.'","paymentProviderChannel": "ussd","totalAmountInKobo": '.$decodeData->totalPayable.',"paymentLineItem": [{"amountPaidInKobo": '.$decodeData->totalPayable.',"paymentAgencyCode": "20008001","paymentRevenueCode": "12040275"}],"taxPayerIdentificationNumber": "'.$decodeData->product->reference.'","taxYear": "2021"}';
		
		echo $hashed = hash("sha512", $data.'7vczyovkpjD+co6yW9OfSUW8fTN8f4CP2Hc/JHm6Wlk=');
		dd($data);
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => 'http://ics3staging.abiairs.gov.ng/assessment-api/api/vendor/payment/notification',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_HTTPHEADER => array(
				"vendorCode: ACCT0000013435",
				"hash:" . $hashed
			),
		));
		$response = curl_exec($curl);
		curl_close($curl);
		$responseData = json_decode($response);
		dd($responseData);
	}
}
