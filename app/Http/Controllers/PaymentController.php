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
	
	public function getPaymentGrid()
    {
		$info_Payment = Payment::Select('payment.id', 'payment.userid', 'payment.paymentref', 'payment.transactionreference', 'payment.amountPaid', 'payment.totalPayable', 'payment.settlementAmount', 'payment.paidon', 'payment.paymentmethod', 'payment.paymentstatus', 'payment.datecreated', 'payment.request_dump', 'payment.channel', 'agents.name', 'agents.msisdn', 'agents.email', 'agents.address', 'agents.type')->join('agents', 'payment.userid', '=', 'agents.id')->Get();
	   	return DataTables::of($info_Payment)
		->escapeColumns([])
		->make(true);
    }
	public function getPayment()
    {
        return view('payment.index');
    }
}
