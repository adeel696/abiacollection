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
					$db_SendCsLog->transactionreference = '123';
					$db_SendCsLog->PaymentRetrivialReference = '123';
					$db_SendCsLog->request_dump = '123';
					$db_SendCsLog->ibris_dump = '123';
					$db_SendCsLog->created_at = date('Y-m-d h:i:s');
					$db_SendCsLog->Save();
					
					$CurrentAmount = $CurrentAmount-$info_ShopFees[$i]->fixed_fee;
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
	
}
