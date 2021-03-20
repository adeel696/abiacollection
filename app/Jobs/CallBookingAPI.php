<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CallBookingAPI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	
	private $userSession;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($userSession)
    {
        $this->userSession = $userSession;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //Remove Seats
		$url = env('API_URL')."ActivateLockSeat/".$this->userSession->company_id."?Destinationid=".$this->userSession->destid."&SelectedSeats=".$this->userSession->no_of_seats."&TripID=".$this->userSession->tripid."&lockedby=".$this->userSession->orderid;
		$options = array(
		  'http'=>array(
			'method'=>"GET",
			'header'=>"Authorization: Bearer ".env('API_TOKEN')
		  )
		);
		$context = stream_context_create($options);
		$data = file_get_contents($url, false, $context);
		$decodeData = json_decode($data, true);

		\Log::info("Call booking lock seats url: ".$url);
		\Log::info("Call booking lock seats job: ".$data);
		
		$url = env('API_URL')."SaveBookingOrder/".$this->userSession->company_id;
		
		$postdata = http_build_query(
			array(
				'TripID' => $this->userSession->tripid,
				'SelectedSeats' => $this->userSession->no_of_seats,
				'MaxSeat' => $this->userSession->no_of_traveler,
				'DestinationID' => $this->userSession->destid,
				'OrderID' => $this->userSession->orderid,
				'Fullname' => $this->userSession->name,
				'Phone' => '0'.substr($this->userSession->booking_msisdn,-10),
				'Email' => 'admin@iyconsoft.com',
				'nextKin' => '',
				'nextKinPhone' => '',
				'KinSelectedSeats' => '',
				'Sex' => $this->userSession->gender,
			)
		);
		$options = array(
		  'http'=>array(
			'header'=> "Content-type: application/x-www-form-urlencoded\r\n". "Content-Length: " . strlen($postdata) . "\r\nAuthorization: Bearer ".env('API_TOKEN'),
			'method'=>"POST",
			'content' => $postdata
		  )
		);
		$context = stream_context_create($options);
		$data = file_get_contents($url, false, $context);
		
		$decodeData = json_decode($data, true);
		
		\Log::info("Call booking job: ".$data);
    }
}
