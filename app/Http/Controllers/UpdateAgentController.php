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

class UpdateAgentController extends Controller
{
   
	function upAgent() 
	{
		return 1;
		$info_Agents = Agent::Where('type','keke')->Get();
		$token = "";
		foreach($info_Agents as $info_Agent)
		{
			if(strtolower($info_Agent->type) == "okada")
			{
				$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_777366599327", "feePercentage" => 0, "splitPercentage" => 6.94, "feeBearer" => false);
				$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_473624252548", "feePercentage" => 0, "splitPercentage" => 4.54, "feeBearer" => false);
				$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_696124412434", "feePercentage" => 0, "splitPercentage" => 31.94, "feeBearer" => false);
				$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_837784943424", "feePercentage" => 100, "splitPercentage" => 8.25, "feeBearer" => true);
				$incomeSplitconfig[] = array ("subAccountCode" => "MFY_SUB_317431997858", "feePercentage" => 0, "splitPercentage" => 48.33, "feeBearer" => false);
	
				$username = 'MK_PROD_9V6KP4EZ5T';
				$password = '3VBV6G53V9STR554ZYW4H2T4EH72WERP';
				$contractCode = "725275533691";
				
				echo $url = "https://api.monnify.com/api/v1/bank-transfer/reserved-accounts/update-income-split-config/0".substr($info_Agent->msisdn,-10);
				$login_url = "https://api.monnify.com/api/v1/auth/login";
			}
			else
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
				
				echo $url = "https://api.monnify.com/api/v1/bank-transfer/reserved-accounts/update-income-split-config/".$info_Agent->msisdn;
				$login_url = "https://api.monnify.com/api/v1/auth/login";
			}
			
			if($token == "")
			{
				$data = json_encode($incomeSplitconfig);
				echo ($data);
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
			}
			
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
			CURLOPT_CUSTOMREQUEST => "PUT",
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json",
					"Authorization: Bearer " . $token
				),
			));
			$response = curl_exec($curl);
			\Log::info('monnifyReserveAccount: '.$response);
			curl_close($curl);
			
			print($response);
			
		}
	}
}
