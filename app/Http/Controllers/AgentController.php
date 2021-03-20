<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables;
use Session;
use Redirect;
use App\Models\Driver;
use App\Models\Agent;
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
			$db_Agent->email = $Row[3];
			$db_Agent->address = $Row[4];
			$db_Agent->type = $Row[5];
			$db_Agent->save();
			
			$accountNumber = $this->monnifyReserveAccount("LIVE", $Row[2], $Row[0], $Row[3], $Row[5]);
			
			if($accountNumber == "0")
			{
				Session::flash('error_message', "Create account fail");
				Session::flash('updatedRows', $updatedRows);
				$db_Driver->delete();
				$db_Agent->delete();
				return Redirect::back();
			}
			else
			{
				$message = "Your monnify account has been created successfully";
				file_get_contents("https://app.multitexter.com/v2/app/sms?email=tech@iyconsoft.com&message=".urlencode($message)."&recipients=".$Row[2]."&forcednd=1&password=sayntt123&sender_name=SELFSERVE");
				$updatedRows[] = $Row;
				$db_Driver->accountno = $accountNumber;
				$db_Driver->save();
				$db_Agent->accountno = $accountNumber;
				$db_Agent->save();
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
		$data = json_encode(array("accountReference" => $msisdn, "accountName"  => $accountname, "currencyCode" => "NGN", "contractCode" => $contractCode, "customerEmail" => $customerEmail, "customerName" => $fullname, "incomeSplitConfig" => $incomeSplitconfig));
		
		
		
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
		
		$responseData = json_decode($response);
		if($responseData->requestSuccessful)
		{
			return $responseData->responseBody->accountNumber;
		}
		else
		{
			return 0;
			//print_r($data);
			//dd($responseData);
		}
	}
	
}
