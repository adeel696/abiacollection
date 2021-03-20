<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Repositories\UssdRepository;
use DB;

class FinalBookNewMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	
	private $userSession;
	private $session_from;
	private $session_msisdn;
	private $gateway;
	private $ussdRps;
	private $info_Date;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userSession, $session_from,$session_msisdn, $gateway, $info_Date)
    {
        $this->userSession = $userSession;
		$this->session_from = $session_from;
		$this->session_msisdn = $session_msisdn;
		$this->gateway = $gateway;
		$this->info_Date = $info_Date;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		$this->ussdRps = new UssdRepository();
		$perPage = 4;
        if($this->userSession->company_ussd_type == "1")
		{
			$info_Schedules = $this->ussdRps->GetCompanySchedule($this->userSession->company_id,$this->info_Date ,$this->userSession->from_terminal_id, $this->userSession->to_terminal_id,$this->userSession->to_city_id,$this->userSession->page_no, $perPage);
		}
		else
		{
			$info_Schedules = $this->ussdRps->GetCompanyScheduleAPI($this->userSession->company_id,$this->info_Date ,$this->userSession->from_terminal_id, $this->userSession->to_terminal_id,$this->userSession->to_city_id,$this->userSession->page_no, $perPage);
		}
		
		$menuSchedule = "";
		$menuScheduleCount=0;
		foreach($info_Schedules as $info_Schedule)
		{
			$menuScheduleCount++;
			$menuSchedule .= $info_Schedule['rownum'].", ".$info_Schedule['dept_time'].", ".$info_Schedule['bus'].", N".$info_Schedule['fare'].", Avail".chr(58)." ".$info_Schedule['AvailableSeatCounts']." Seats\n";
		}
		
		if($menuSchedule!="")
		{
			$info_Company = $this->userSession->Company()->First();
			$company_Ussd_Code = $info_Company->Company_ussd()->First()->code;
			$output['session_msg'] = $info_Company->name."\n".$this->userSession->from_terminal_name." to ".$this->userSession->to_terminal_name."\n".date_format(date_create($this->info_Date),"D d M Y")."\nSchedules".chr(58)." ".$info_Schedules[0]['terminal_name']."\nBus Found (".$menuScheduleCount.")\n".$menuSchedule."\n\n* To book a bus, dial\n*".$company_Ussd_Code."*BusNo#\ne.g.For Bus 1, dial *".$company_Ussd_Code."*1#";
		}
		
		$this->userSession->schedule_date = $this->info_Date;
		
		$this->userSession->per_page=$perPage;
		$this->userSession->user_level=10;
		//$this->userSession->keyword=$this->session_msg;
		//$this->userSession->c_parent_id=$this->session_msg;
		$this->userSession->is_child=1;
		$this->userSession->save();
		\Log::info("Call FinalBookNewMessage menuSchedule: ".$menuSchedule);
		if($menuSchedule=="")
		{
			$action = "Schedule & Fare";
			if($this->userSession->ussd_code=="*".$this->session_from)
			{
				$action = "Transporters";
			}
			$this->ussdRps->updateCustomerLog('0', "update customer_logs set is_complete=0, action='$action', from_city_id='".$this->userSession->from_city_id."', from_city_name='".$this->userSession->from_city_name."', to_city_id='".$this->userSession->to_city_id."', to_city_name='".$this->userSession->to_city_name."' where id=".$this->userSession->session_id);
			
			$output['session_operation'] = "endcontinue";
			$output['session_msg'] = "Sorry no schedule available for this date";
			
			$smsMessage = $output['session_msg'];
			\Log::info("Call FinalBookNewMessage Send SMS None: ".$this->session_msisdn." Gateway:".$this->gateway);
			$this->ussdRps->sendSMS($this->session_from,$this->session_msisdn, $smsMessage, $this->gateway);
		}
		else
		{
			$smsMessage = $output['session_msg'];
			$Charge = $this->ussdRps->ChargeUser($this->session_msisdn, "*".$this->session_from);
			if($Charge != 0)
			{

				$this->ussdRps->sendSMS($this->session_from,$this->session_msisdn, $smsMessage, $this->gateway);
				\Log::info("Call FinalBookNewMessage Send SMS: ".$this->session_msisdn." Gateway:".$this->gateway);
				//$output['session_msg'] = "Thanks, your request has being received. You will receive SMS response shortly";
				$output['session_msg'] = "Thanks for choosing ".$this->userSession->Company()->First()->name.". You will receive SMS with Buses available shortly.";
										
				//Store data for booking next
				$ResultIns = DB::insert("INSERT INTO ussd_menu_schedule_search (msisdn, ussd_code, from_city_id, from_city_name, from_terminal_id, from_terminal_name, to_terminal_id, to_terminal_name, to_city_id, to_city_name, schedule_date, company_id, company_name, schedule_id, fare_id, record_on) select msisdn, ussd_code, from_city_id, from_city_name, from_terminal_id, from_terminal_name, to_terminal_id, to_terminal_name, to_city_id, to_city_name, schedule_date, company_id, company_name, schedule_id, fare_id, record_on from ussd_menu_session where msisdn = '".$this->session_msisdn."'");
			}
			else
			{
				$output = $this->ussdRps->emptyBalanceMessage($this->session_msisdn);
			}
			
		}
		\Log::info("Call FinalBookNewMessage job: ".$this->session_msisdn." Gateway:".$this->gateway);
    }
}
