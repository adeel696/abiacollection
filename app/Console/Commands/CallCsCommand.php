<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentAtin;
use App\Models\SendCsLog;
use App\Models\ShopFee;
use App\Imports\PaymentAtinImport;
use Carbon\Carbon;

class CallCsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'call:cs';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Paymment ATIN Call CS';

    /**
     * Create a new command instance.
     *
     * @return void
     */
	public function __construct()
    { 
		parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
		\Log::info("Call CS PaymentAtins");

		$info_PaymentAtins = PaymentAtin::All();//->Where('id','=','2');
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
					
					
					$data = '{"paymentGatewayProvider": "selfserve","propertyId": "'.$info_PaymentAtin->property_id.'","paymentProviderNotificationLogId": "'.($db_SendCsLog->id*100000).($info_PaymentAtin->id*234567).'","paymentProviderReferenceNumber": "'.$info_PaymentAtin->payment_id.$i.'","paymentDate": "'.(date("d-m-Y H:i:s")).'","paymentProviderCustomerName": "'.$info_PaymentAtin->store_name.'","paymentProviderCustomerPhoneNumber": "'.$info_PaymentAtin->mobile_number.'","paymentProviderCustomerReference": "'.$info_PaymentAtin->atin.'","paymentProviderChannel": "ussd","totalAmountInKobo": '.((int)($info_ShopFees[$i]->fixed_fee)*100).',"paymentLineItem": [{"amountPaidInKobo": '.((int)($info_ShopFees[$i]->fixed_fee)*100).',"paymentAgencyCode": "'.$info_ShopFees[$i]->agency_code.'","paymentRevenueCode": "'.$info_ShopFees[$i]->revenue_code.'"}],"taxPayerIdentificationNumber": "'.$info_PaymentAtin->atin.'","taxYear": "2021"}';
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
				else
				{
					break;
				}
			}
			
			$info_PaymentAtin->amount = $CurrentAmount;
			$info_PaymentAtin->Save();
		}
    }
}
