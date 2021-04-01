<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use Redirect;
use App\Models\Driver;
use App\Models\Agent;
use App\Models\Payment;
use App\Models\UssdSession;
use DB;

class USSDController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
		$Session = UssdSession::Where('msisdn',$request->msisdn)->First();
		
		if(!$Session || $request->message == '*8014*98')
		{
			UssdSession::Where('msisdn',$request->msisdn)->Delete();

			$Session = new UssdSession;
			$Session->msisdn = $request->msisdn;
			$Session->current_state = "1";
		}
		
		$sendMessage="";
		$output = [];
		switch($Session->current_state)
		{
			/*case "1":
				$sendMessage = "Welcome to Abia SelfServe\n1. My Payments\n2. Shop\n3. Agent\n4. Bus";
				
				$output['session_operation'] = "continue";
				$output['session_msg'] = $sendMessage;
				
				$Session->current_state = "2";
				$Session->save();

			break;*/
			case "1":
				
				//$Session->g_parent = $request->message;
				/*if($request->message == "3")
				{
					$sendMessage = "1. Info\n2. Payment";
				}*/
				$Session->g_parent = 3;
				$sendMessage = "1. Info\n2. Payment";
				$output['session_operation'] = "continue";
				$output['session_msg'] = $sendMessage;
				
				$Session->current_state = "2";
				$Session->save();
				
			break;
			case "2":
				$Session->c_parent = $request->message;
				if($Session->g_parent == "3")
				{
					if($request->message == "1")
					{
						$info_Agent = Agent::Where('msisdn',$request->msisdn)->First();
						if($info_Agent)
							$sendMessage = $info_Agent->name."\n".$info_Agent->type." Agent\nAccount No: ".$info_Agent->accountno." Sterling Bank";
						else
							$sendMessage = "Sorry you are not registered for this service";
							
					}
					else
					{
						$info_Agent = Agent::Where('msisdn',$request->msisdn)->First();
						if($info_Agent)
						{
							$info_Payment = Payment::Where('userid',$info_Agent->id)->OrderBy('id','desc')->First();
							if($info_Payment)
							{
								$sendMessage = "Your Last Payment was N".((int)($info_Payment->totalPayable)*1)." on ".$info_Payment->paidon."\n\nKeep Abia moving, Pay your levies on time";
							}
							else
							{
								$sendMessage = "Your Last Payment was N0.\n\nKeep Abia moving, Pay your levies on time";
							}
						}
						else
						{
							$sendMessage = "Sorry you are not registered for this service";
						}
					}
					
					$output['session_operation'] = "continue";
					$output['session_msg'] = $sendMessage;
					
					$Session->delete();
				}
			break;
		}
        return $output;
    }
}
