<?php
@session_start();
set_time_limit(60);
date_default_timezone_set("Asia/Bangkok");
header('Content-Type: application/json; charset=utf-8');

class Scb {
	private $endpoint='https://fasteasy.scbeasy.com';
	private $api_transactions 	= "/v2/deposits/casa/transactions";
	
	private $pin='';
	private $deviceId='';
	private $walletId=''; //แม่มณี
	public  $account_number = '';
	public $Auth = '';

	private $api_verification  = "/v2/transfer/verification";
	private $tilesVersion		= '75';
	private $useragent			= 'Android/13;FastEasy/3.73.0/7601';
	private $APIEndpointTranfers   		  = "/v2/transfer/verification";
	private $APIEndpointTranfersCF 		  = "/v3/transfer/confirmation";
    private $host = 'fasteasy.scbeasy.com:8443';

	public function setData($data){
		$this->pin = $data->pin;
		$this->deviceId = $data->deviceId;
		$this->walletId = $data->walletId;
		$this->account_number = $data->account_number;
	}
	
	public function Curl($method, $url, $data,$header)  {
		if ($url=='/v1/isprint/preAuth' or $url=='/v1/merchants/transactions' or $url=='/v1/merchants/request/qr' or $url == $this->api_verification) { $HEADER=0; }else{ $HEADER=1; }
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL =>$this->endpoint.$url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_HEADER=> $HEADER,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => $header,
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return  $response;
	}

	public function Curlwd($method, $url, $data,$header ,$setheder = 0)  {
		$setheder=0;
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL =>$this->endpoint.$url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_HEADER=> $setheder,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => $header,
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return  $response;
	}

	public function Curl1($method, $url, $data,$header)  {
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL =>$url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_HEADER=> $HEADER,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $method,
			CURLOPT_POSTFIELDS => $data,
			CURLOPT_HTTPHEADER => $header,
		));
		$response = curl_exec($curl);
		curl_close($curl);
		return  $response;
	}

	public function CurlEncode($url = 'http://localhost:3031/pin/encrypt' , $poststr = ""){
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $url ,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $poststr,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/x-www-form-urlencoded'
		),
		));

		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

	public function login()  {

		if($this->tilesVersion < 75){
			$data='{"isLoadGeneralConsent":"0","deviceId":"'.$this->deviceId.'","jailbreak":"0","tilesVersion":"'.$this->tilesVersion.'","userMode":"INDIVIDUAL"}';
		}
		else{
			$resp = json_decode(file_get_contents('http://localhost:8888/payload?deviceId='.$this->deviceId),true);
			$data='{"isLoadGeneralConsent":"0","deviceId":"'.$this->deviceId.'","jailbreak":"0","tag":"'.$resp["tag"].'","payload":"'.$resp["payload"].'","tilesVersion":"'.$this->tilesVersion.'","userMode":"INDIVIDUAL"}';
		}

		$header=array(
			'Accept-Language: th',
			'scb-channel: APP',
			'user-agent: '.$this->useragent,
			'Content-Type: application/json; charset=UTF-8',
			'Hos: fasteasy.scbeasy.com:8443',
			'Connection: close',
		);
		$res = $this->Curl("POST", '/v3/login/preloadandresumecheck',$data,$header);
		preg_match_all('/(?<=Api-Auth: ).+/', $res, $Auth);
		$Auth = trim($Auth[0][0]);
		if ($Auth=="") {  echo "Auth error login";  exit(); }
		$data='{"loginModuleId":"PseudoFE"}';
		$header=array( 'Api-Auth: '.$Auth,    'Content-Type: application/json',  );
		$res = $this->Curl("POST", '/v1/isprint/preAuth',$data, $header);
		$data = json_decode($res,true);

		$hashType=$data['e2ee']['pseudoOaepHashAlgo'];
		$Sid=$data['e2ee']['pseudoSid'];
		$ServerRandom=$data['e2ee']['pseudoRandom'];
		$pubKey=$data['e2ee']['pseudoPubKey'];

		$data="Sid=".$Sid."&ServerRandom=".$ServerRandom."&pubKey=".$pubKey."&pin=".$this->pin."&hashType=".$hashType;
		$header= array("Content-Type: application/x-www-form-urlencoded");
		$res = $this->CurlEncode('http://localhost:3031/pin/encrypt',$data);

		$data='{"deviceId":"'.$this->deviceId.'","pseudoPin":"'.$res.'","pseudoSid":"'.$Sid.'","tilesVersion":"'.$this->tilesVersion.'"}';
		$header=array(
			'Api-Auth: '.$Auth,
			'Accept-Language: th',
			'scb-channel: APP',
			'user-agent: '.$this->useragent,
			'Content-Type: application/json; charset=UTF-8',
			'Host: fasteasy.scbeasy.com:8443',
			'Connection: close',
		);
		$res = $this->Curl("POST", '/v1/fasteasy-login',$data, $header);
		preg_match_all('/(?<=Api-Auth:).+/', $res, $Auth_result);
		$Auth1=$Auth_result[0][0];
		if ($Auth1=="") { echo "Auth error #002";exit();}
		return $Auth1;
	}

	public function getBalance(){
		$Auth = trim($this->login());
		$header=array(
			"Accept-Language: th",
			'user-agent: '.$this->useragent,
			'scb-channel:  APP',
			'Host: '.$this->host,
			'Connection: close',
			'Api-Auth: '.$Auth,
			'Content-Type: application/json; charset=UTF-8',
		);

		$data='{"depositList":[{"accountNo":"'.$this->account_number.'"}],"latestTransactionFlag":"false","numberRecentTxn":2,"tilesVersion":"'.$this->tilesVersion.'"}';
		$res = $this->Curlwd("POST", '/v2/deposits/summary',$data, $header,0);
		$res = json_decode($res);
		return @$res;
		
	}

	public function withdraw($banktype,$toaccounterNumber,$amount = 1){
		if($amount >= 1 && strlen($toaccounterNumber) >= 9){

			$Auth = trim($this->login());
			$accountToBankCode = $this->get_bank_service($banktype);
			$transferType="ORFT";
			if ($accountToBankCode=='014') {
				$transferType="3RD";
			}

			$header=array('scb-channel:  APP','Api-Auth: '.$Auth,'Content-Type: application/json; charset=UTF-8');
			$data='{"accountFrom":"'.$this->account_number.'","accountFromType":"2","accountTo":"'.$toaccounterNumber.'","accountToBankCode":"'.$accountToBankCode.'","amount":"'.$amount.'","annotation":null,"transferType":"'.$transferType.'" , "tilesVersion":"'.$this->tilesVersion.'"}';

			$res = $this->Curlwd("POST", $this->APIEndpointTranfers,$data, $header,0);
			$res = json_decode($res);

			if($res->status->code == 1000){
				$get_confrim_data = $res->data;
				$header = array('scb-channel:  APP','Api-Auth: '.$Auth,'Content-Type: application/json; charset=UTF-8');
				$data = '{"accountFrom":"'.$this->account_number.'","accountFromName":"'.$get_confrim_data->accountFromName.'","accountFromType":"2","accountTo":"'.$get_confrim_data->accountTo.'","accountToBankCode":"'.$get_confrim_data->accountToBankCode.'","accountToName":"'.$get_confrim_data->accountToName.'","amount":"'.$amount.'","botFee":0.0,"channelFee":0.0,"fee":0.0,"feeType":"","pccTraceNo":"'.$get_confrim_data->pccTraceNo.'","scbFee":0.0,"sequence":"'.$get_confrim_data->sequence.'","terminalNo":"'.$get_confrim_data->terminalNo.'","transactionToken":"'.$get_confrim_data->transactionToken.'","transferType":"'.$get_confrim_data->transferType.'"}';

				$res = $this->Curlwd("POST", $this->APIEndpointTranfersCF,$data, $header,0);
				$res = json_decode($res);
				
				if($res->status->code == 1000){
					return $res;
				}
				else if($res->status->code == 8101 || $res->status->code == 8102){
					// โอนเกิน 50k
					$deleteGroup = $api->Getbulktransferprofiles();
					foreach($deleteGroup->data->groupList as $item){
						$api->bulktransferprofilesDelete($item->groupId);
					}

					$group = 'TMSCB';
                    $data1 = $this->bulktransferprofiles($group);
                    $facetec = $data1->data->groupId;
                    $data2 = $this->bulktransferprofilesrecipient($facetec,$get_confrim_data->accountTo,$amount,$get_confrim_data->accountToBankCode,$group);
                    $data3 = $this->bulktransferprofilesrecipientgroupId($facetec);

                    if($get_confrim_data->accountToBankCode != '014'){
                    	$checklsit = $data3->data->recipientList->otherList[(count($data3->data->recipientList->otherList)-1)]->recipientId;
                	}
                	else{
                		$checklsit = $data3->data->recipientList->scbList[(count($data3->data->recipientList->scbList)-1)]->recipientId;
                	}

                    $data4 = $this->transferbulkverification($this->account_number,$facetec,$amount,$checklsit, $get_confrim_data->accountToBankCode);
                    
                    $reterm = $this->transferbulkconfirmation($data4->data->transactionToken);
                    $xp = $this->bulktransferprofilesgroup($facetec);
                    $delete = $this->bulktransferprofilesDelete($facetec);
                    return $reterm;
				}
				else{
					return $res;
				}	
			}
			else{
				return $res;
			}
		}
		else{
			return ['error' => 'error'];
		}
		
	}

	public function loadLastToken(){
		$auth = trim($this->login());
		return $auth;
	}

    // face bypass
    //fun1
    public function bulktransferprofiles($groupName) {
        $Auth = $this->loadLastToken();
        $data = json_encode(array('groupName' => $groupName));
        $header=array('scb-channel: APP','Api-Auth: '.$Auth,'Content-Type: application/json; charset=UTF-8');
        $header=array(
            "Accept-Language: en",
            'user-agent: '.$this->useragent,
            'scb-channel:  APP',
            'Host: '.$this->host,
            'Connection: close',
            'Api-Auth: '.$Auth,
            'Content-Type: application/json; charset=UTF-8',
        );
        $res = $this->Curl("POST", "/v1/bulktransferprofiles/group",$data, $header,0);
        $res = json_decode($res);
        return $res;
    }

    public function Getbulktransferprofiles(){
	        $Auth = $this->loadLastToken();
	        $data = json_encode(array());
	        $header=array('scb-channel: APP','Api-Auth: '.$Auth,'Content-Type: application/json; charset=UTF-8');
	        $header=array(
	            "Accept-Language: en",
	            'user-agent: '.$this->useragent,
	            'scb-channel:  APP',
	            'Host: '.$this->host,
	            'Connection: close',
	            'Api-Auth: '.$Auth,
	            'Content-Type: application/json; charset=UTF-8',
	        );
	        $res = $this->Curl("GET", "/v1/bulktransferprofiles/group",$data, $header,0);
	        $res = json_decode($res);
	        return $res;
	}

    public function bulktransferprofilesDelete($groupId) {
        $Auth = $this->loadLastToken();
        $data = json_encode(array('groupId' => $groupId));
        $header=array('scb-channel:  APP','Api-Auth: '.$Auth,'Content-Type: application/json; charset=UTF-8');
        $header=array(
            "Accept-Language: en",
            'user-agent: '.$this->useragent,
            'scb-channel:  APP',
            'Host: '.$this->host,
            'Connection: close',
            'Api-Auth: '.$Auth,
            'Content-Type: application/json; charset=UTF-8',
        );
        $res = $this->Curl("DELETE", "/v1/bulktransferprofiles/group",$data, $header,0);
        $res = json_decode($res);
        return $res;
    }

    //fun4
    public function transferbulkverification($accountFrom,$groupId,$amount,$recipientId,$bankcode) {
        $Auth = $this->loadLastToken();

        if($bankcode != '014'){
	        $data = json_encode(
	            array('accountFrom' => $accountFrom , 'groupId' => $groupId,
	            'otherList' => array(array(
	                'amount' => $amount,
	                'recipientId' => $recipientId,
	                )),
	            'ownList' => array(),
	            'scbList' => array(),
	            )
	        );
    	}
    	else{
    		$data = json_encode(
	            array('accountFrom' => $accountFrom , 'groupId' => $groupId,
	            'otherList' => array(),
	            'ownList' => array(),
	            'scbList' => array(array(
	                'amount' => $amount,
	                'recipientId' => $recipientId,
	                )),
	            )
	        );
    	}

        $header=array('scb-channel:  APP','Api-Auth: '.$Auth,'Content-Type: application/json; charset=UTF-8');
        $header=array(
            "Accept-Language: en",
            'user-agent: '.$this->useragent,
            'scb-channel:  APP',
            'Host: '.$this->host,
            'Connection: close',
            'Api-Auth: '.$Auth,
            'Content-Type: application/json; charset=UTF-8',
        );
        $res = $this->Curl("POST", "/v1/transfer/bulk/verification",$data, $header,0);
        $res = json_decode($res);
        return $res;
    }
    //fun5
    public function  transferbulkconfirmation($transactionToken) {
        $Auth = $this->loadLastToken();
        $data = json_encode(array('transactionToken' => $transactionToken));
        $header=array('scb-channel:  APP','Api-Auth: '.$Auth,'Content-Type: application/json; charset=UTF-8');
        $header=array(
            "Accept-Language: en",
            'user-agent: '.$this->useragent,
            'scb-channel:  APP',
            'Host: '.$this->host,
            'Connection: close',
            'Api-Auth: '.$Auth,
            'Content-Type: application/json; charset=UTF-8',
        );
        $res = $this->Curl("POST", "/v1/transfer/bulk/confirmation",$data, $header,0);
        $res = json_decode($res);
        return $res;

    }
    //fun6
    public function  bulktransferprofilesgroup($groupId) {
        $Auth = $this->loadLastToken();
        $data = json_encode(array('groupId' => $groupId));
        $header=array('scb-channel:  APP','Api-Auth: '.$Auth,'Content-Type: application/json; charset=UTF-8');

        $header=array(
            "Accept-Language: en",
            'user-agent: '.$this->useragent,
            'scb-channel:  APP',
            'Host: '.$this->host,
            'Connection: close',
            'Api-Auth: '.$Auth,
            'Content-Type: application/json; charset=UTF-8',
        );
        $res = $this->Curl("POST", "/v1/bulktransferprofiles/group",$data, $header,0);
        $res = json_decode($res);
        return $res;

    }
    //fun2
    public function  bulktransferprofilesrecipient($groupId,$accountTo,$amount,$bankCode,$nickname ) {
        $Auth = $this->loadLastToken();
        $data = json_encode(array('groupId' => $groupId , 'recipientList' => array(array(
            'accountTo' => $accountTo,
            'amount' => $amount,
            'bankCode' => $bankCode,
            'nickname' => $nickname,
            'subFunction' => ($bankCode != '014') ? "OTHER" : "SCB",
        ))));
        $header=array('scb-channel:  APP','Api-Auth: '.$Auth,'Content-Type: application/json; charset=UTF-8');
        $header=array(
            "Accept-Language: en",
            'user-agent: '.$this->useragent,
            'scb-channel:  APP',
            'Host: '.$this->host,
            'Connection: close',
            'Api-Auth: '.$Auth,
            'Content-Type: application/json; charset=UTF-8',
        );
        $res = $this->Curl("POST", "/v1/bulktransferprofiles/group/recipient",$data, $header,0);
        $res = json_decode($res);
        return $res;
    }


    public function  bulktransferprofilesrecipientDelete($groupId,$recipientId) {
        $Auth = $this->loadLastToken();
        $data = json_encode(array('groupId' => $groupId , 'recipientId' => $recipientId));
        $header=array('scb-channel:  APP','Api-Auth: '.$Auth,'Content-Type: application/json; charset=UTF-8');
        $header=array(
            "Accept-Language: en",
            'user-agent: '.$this->useragent,
            'scb-channel:  APP',
            'Host: '.$this->host,
            'Connection: close',
            'Api-Auth: '.$Auth,
            'Content-Type: application/json; charset=UTF-8',
        );
        $res = $this->Curl("DELETE", "/v1/bulktransferprofiles/group/recipient",$data, $header,0);
        $res = json_decode($res);
        return $res;
    }

    //fun3
    public function  bulktransferprofilesrecipientgroupId($groupId) {
        $Auth = $this->loadLastToken();
        $data = json_encode(array());
        $header=array('scb-channel:  APP','Api-Auth: '.$Auth,'Content-Type: application/json; charset=UTF-8');
        $header=array(
            "Accept-Language: en",
            'user-agent: '.$this->useragent,
            'scb-channel:  APP',
            'Host: '.$this->host,
            'Connection: close',
            'Api-Auth: '.$Auth,
            'Content-Type: application/json; charset=UTF-8',
        );
        $res = $this->Curl("GET", "/v1/bulktransferprofiles/group/recipient?groupId=".$groupId,$data, $header,0);
        $res = json_decode($res);
        return $res;
    }

    // end facedetect

	public function transactionsmain($pagenumber = 1)  {

		$Auth = trim($this->login());
		$header=array(
			"Accept-Language: th",
			'user-agent: '.$this->useragent,
			'scb-channel:  APP',
			'Host: '.$this->host,
			'Connection: close',
			'Api-Auth: '.$Auth,
			'Content-Type: application/json; charset=UTF-8',
		);
 
		$startDate = date("Y-m-d", strtotime("-1 day"));
		$endDate = date('Y-m-d');

		$data='{"accountNo":"'.$this->account_number.'","endDate":"'.$endDate.'","pageNumber":"'.$pagenumber.'","pageSize":20,"productType":"2","startDate":"'.$startDate.'" }';

		$res = $this->Curlwd("POST", $this->api_transactions,$data, $header,0);
		$res = json_decode($res);


		if($res->status->code == 1000){
			return $res;
		}
		else{
		
			//1000 success , 1002 Token Expired
			if($res->status->code == 1002){
				$Auth = trim($this->login());
				
				if ($Auth == 'Auth error') { $Auth = trim($this->login()); }
				return null;
			}
		}
	}


	public function get_bank_service($bank_name) {
		$data_bank_id = [
			'bay' => '025',
			'baac' => '034',
			'bbl' => '002',
			'kbank' => '004',
			'ktb' => '006',
			'ttb' => '011',
			'tmb' => '011',
			'tisco' => '067',
			'tbnk' => '065',
			'uob' => '024',
			'sc' => '020',
			'gsb' => '030',
			'lhbank' => '073',
			'scb' => '014',
			'icbc' => '070',
			'cimb' => '022',
			'kk' => '069',
			'kkp' => '069',
		];

		return $data_bank_id[$bank_name];
	}

	public function getAccount($to,$banktype){
		$Auth = trim($this->login());


		$accountToBankCode = $this->get_bank_service($banktype);
		$amount=100;

		$transferType="ORFT";
		if ($accountToBankCode=='014') {
			$transferType="3RD";
		}

		$header=array('scb-channel:  APP','Api-Auth: '.$Auth,'Content-Type: application/json; charset=UTF-8');
		$data='{"depositList":[{"accountNo":"'.$this->account_number.'"}],"latestTransactionFlag":"false","numberRecentTxn":2,"tilesVersion":"'.$this->tilesVersion.'"}';
		$data='{"accountFrom":"'.$this->account_number.'","accountFromType":"2","accountTo":"'.$to.'","accountToBankCode":"'.$accountToBankCode.'","amount":"'.$amount.'","annotation":null,"transferType":"'.$transferType.'"}';
		$res = $this->Curlwd("POST", $this->api_verification,$data, $header,0);
		$res = json_decode($res);
		

		if($res->status->code == 1000){
			return $res->data;
		}
		else{
			$Auth = trim($this->login());
			
			if ($Auth == 'Auth error') { $Auth = trim($this->login()); }

			$header=array('scb-channel:  APP','Api-Auth: '.$Auth,'Content-Type: application/json; charset=UTF-8');
			$data='{"depositList":[{"accountNo":"'.$this->account_number.'"}],"latestTransactionFlag":"false","numberRecentTxn":2,"tilesVersion":"'.$this->tilesVersion.'"}';
			$data='{"accountFrom":"'.$this->account_number.'","accountFromType":"2","accountTo":"'.$to.'","accountToBankCode":"'.$accountToBankCode.'","amount":"'.$amount.'","annotation":null,"transferType":"'.$transferType.'"}';
			$res = $this->Curl("POST", $this->api_verification,$data, $header,0);
			$res = json_decode($res);
			return $res->data;
		}
	}
}

$action = isset($_GET['action']) ? $_GET['action'] : 'balance';
$post_data = (object) $_POST;

$api = new Scb();
$res = array('status' => 'fail', 'message' => 'ตั้ง device ไม่สำเร็จ');

if($action == 'setsession'){
	$pin = isset($post_data->pincode) ? $post_data->pincode : $_SESSION['pin'];
	$device = isset($post_data->deviceId) ? $post_data->deviceId : $_SESSION['device'];
	$accnumber = isset($post_data->accnumber) ? $post_data->accnumber : $_SESSION['accnumber'];

	$_SESSION['pin'] = $pin;
	$_SESSION['device'] = $device;
	$_SESSION['accnumber'] = $accnumber;

	$dataset = (object) array(
		"pin" => $_SESSION['pin'],
		"deviceId" => $_SESSION['device'],
		"walletId" => '',
		"account_number" => $_SESSION['accnumber']
	);

	$api->setData($dataset);

	$check = $api->login();
	if($check != ''){
		$res = array('status' => 'success', 'message' => 'ตั้ง device สำเร็จ' , 'res' => $check);
	}
	else{
		$res = array('status' => 'error', 'message' => 'ไม่สามารถเข้าระบบได้' );
	}

}
else if($action == 'delsession'){
	@session_destroy();
	$res = array('status' => 'success', 'message' => 'ลบค่าเรียบร้อยเเล้ว');
}
else{
	$dataset = (object) array(
		"pin" => $_SESSION['pin'],
		"deviceId" => $_SESSION['device'],
		"walletId" => '',
		"account_number" => $_SESSION['accnumber']
	);

	$api->setData($dataset);

	if($action == 'balance'){
		$res = $api->getBalance();
	}
	else if($action == 'transaction'){
		$res = $api->transactionsmain(1);
	}
	else if($action == 'withdraw'){
		$res = $api->withdraw($post_data->banktype,$post_data->accnumber,$post_data->amount);
	}

}

echo json_encode($res);

?> 