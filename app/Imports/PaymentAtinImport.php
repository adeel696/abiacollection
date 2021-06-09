<?php

namespace App\Imports;

use App\Models\PaymentAtin;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use DateTime;
use Auth;
use DB;
 
class PaymentAtinImport implements ToCollection, WithHeadingRow
{
	public $Error;
	
	
    public function collection(Collection $rows)
    {		
		$this->Error = "";
		foreach ($rows as $row) 
        {
			//dd($row);
			try
			{
				if(isset($row["payment_id"]) && $row["payment_id"] != "" && isset($row["atin"]) && $row["atin"] != "")
				{
			
					$db_PaymentAtin = PaymentAtin::Where('atin',$row["atin"])->First();
					
					if($db_PaymentAtin)
					{
						$db_PaymentAtin->payment_id = $row["payment_id"];
						$db_PaymentAtin->property_id = $row["property_id"];
						$db_PaymentAtin->atin = $row["atin"];
						$db_PaymentAtin->mobile_number = $row["mobile_number"];
						$db_PaymentAtin->amount = ($db_PaymentAtin->amount + floatval(str_replace(",","",$row["amount"])));
						$db_PaymentAtin->store_name = $row["store_name"];
						$db_PaymentAtin->market_name = $row["market_name"];
						$db_PaymentAtin->zone = $row["zone"];
						$db_PaymentAtin->owners_name = $row["owners_name"];
						$db_PaymentAtin->save();
					}
					else
					{
						$db_PaymentAtin = new PaymentAtin;
						$db_PaymentAtin->payment_id = $row["payment_id"];
						$db_PaymentAtin->property_id = $row["property_id"];
						$db_PaymentAtin->atin = $row["atin"];
						$db_PaymentAtin->mobile_number = $row["mobile_number"];
						$db_PaymentAtin->amount = floatval(str_replace(",","",$row["amount"]));
						$db_PaymentAtin->store_name = $row["store_name"];
						$db_PaymentAtin->market_name = $row["market_name"];
						$db_PaymentAtin->zone = $row["zone"];
						$db_PaymentAtin->owners_name = $row["owners_name"];
						$db_PaymentAtin->save();
					}
				}
			}
			catch(\Exception $e)
			{
				//dd($row);
				$this->Error = $e;
			}
			
		}
    }
}
