<?php
require_once("EasyCRUD.class.php");
class SMSTemplates extends Crud {
	protected $table = 'tbl_sms_template';
	protected $pk = 'sms_template_id';
	protected $fields = array('sms_template_id','template_type','template_name','template_body','template_status','tokens_used');
        function getSMSTemplateById($id){
		$DB =  new DB();
		$result = $DB->query("SELECT * FROM  ".$this->table." WHERE `sms_template_id` =".$id);
		return $result;
	 }
	 
	 function sendSMS($receipientno,$msgText){
		$msgText = urldecode($msgText);
		
		$username=SMS_USER_NAME;
		$encryp_password=sha1(trim(SMS_PASSWORD));
		$senderid=SMS_SENDERID;
		$deptSecureKey=SMS_SECURE_KEY;

		$finalmessage=$this->string_to_finalmessage(trim($msgText));
	 	$key=hash('sha512',trim($username).trim($senderid).trim($finalmessage).trim($deptSecureKey));
	 
		$data = array(
		"username" => trim($username),
		"password" => trim($encryp_password),
		"senderid" => trim($senderid),
		"content" => trim($finalmessage),
		"smsservicetype" =>"unicodemsg",
		"mobileno" =>trim($receipientno),
		"key" => trim($key)
		);

		//echo SMS_URL;exit;

		$this->post_to_url_unicode(SMS_URL,$data); //calling post_to_url_unicode to send single unicode sms
	 }

	 function post_to_url_unicode($url, $data) {
		$fields = '';
		foreach($data as $key => $value) {
		$fields .= $key . '=' . urlencode($value) . '&';
		}
		rtrim($fields, '&');
		
		$post = curl_init();
	   //curl_setopt($post, CURLOPT_SSLVERSION, 5); // uncomment for systems supporting TLSv1.1 only
		curl_setopt($post, CURLOPT_SSLVERSION, 6); // use for systems supporting TLSv1.2 or comment the line
		curl_setopt($post,CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($post, CURLOPT_URL, $url);	 
		curl_setopt($post, CURLOPT_POST, count($data));
		curl_setopt($post, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($post, CURLOPT_HTTPHEADER, array("Content-Type:application/x-www-form-urlencoded"));
		curl_setopt($post, CURLOPT_HTTPHEADER, array("Content-length:"
	   . strlen($fields) ));
		curl_setopt($post, CURLOPT_HTTPHEADER, array("User-Agent:Mozilla/4.0 (compatible; MSIE 5.0; Windows 98; DigExt)"));
		curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($post); //result from mobile seva server
		curl_close($post);
		}

	 public function ordutf8($string, &$offset){
		$code=ord(substr($string, $offset,1));
		if ($code >= 128)
		{ //otherwise 0xxxxxxx
		if ($code < 224) $bytesnumber = 2;//110xxxxx
		else if ($code < 240) $bytesnumber = 3; //1110xxxx
		else if ($code < 248) $bytesnumber = 4; //11110xxx
		$codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) -
	   ($bytesnumber > 3 ? 16 : 0);
		for ($i = 2; $i <= $bytesnumber; $i++) {
		$offset ++;
		$code2 = ord(substr($string, $offset, 1)) - 128;//10xxxxxx
		$codetemp = $codetemp*64 + $code2;
		}
		$code = $codetemp;
   
		}
		return $code;
		}

	 public function string_to_finalmessage($message){
		$finalmessage="";
		$sss = "";
		for($i=0;$i<mb_strlen($message,"UTF-8");$i++) {
		$sss=mb_substr($message,$i,1,"utf-8");
		$a=0;
		$abc="&#".$this->ordutf8($sss,$a).";";
		$finalmessage.=$abc;
		}
		return $finalmessage;
		}


 	function sendSMSOld($receipientno,$msgtxt) {
		if(strlen($receipientno) <= 10){
			$receipientno = '91'.$receipientno;
			}
                $ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_URL, SMS_URL."?unicode=1&uname=".SMS_USERNAME."&pass=".SMS_PASSWORD."&send=".SMS_SENDER."&dest=".$receipientno."&msg=".$msgtxt);
		$output = curl_exec($ch);
		if(curl_errno($ch)){
			echo 'Curl error: ' . curl_error($ch);
		}
		curl_close($ch);
		return $output;	
	}



	function sendSMSOldBackup($receipientno,$msgText) 
	{
		//echo urldecode($msgText);die;
		$msgText = urldecode($msgText);
		$url = "http://smspush.openhouseplatform.com/smsmessaging/1/outbound/tel%3A%2BKSLKAR/requests";
		//JSON object to be sent in the POST body.
		$rawdata="{\"outboundSMSMessageRequest\":{".
		"\"address\":[\"tel:+91".$receipientno."\"],".
		"\"senderAddress\":\"tel:KSLKAR\",".
		//"\"outboundSMSTextMessage\":{\"message\":\"ಓಪನ್ ಹೌಸ್ನಿಂದ ಪಿಎಚ್ಪಿ ಎಪಿಐ ಪಠ್ಯ ಸಂದೇಶ\"},".
		"\"outboundSMSTextMessage\":{\"message\":\"".$msgText."\"},".
		"\"clientCorrelator\":\"123456\",".
		"\"messageType\":\"4\",".
		"\"senderName\":\"KSLKAR\"}".
		"}";
		//Curl variable to store headers and JSON object.
		$ch = curl_init($url);
		//1 stands for posting.
		curl_setopt($ch, CURLOPT_POST, 1);
		//Replace the secure Key associated with the registered service from your account on the website 
		//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','key: 8ee0e150-adde-4b71-8719-a0e6412d1274'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','key: 19efcd0a-3344-4a2b-816a-4ac0535416a0'));
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $rawdata);
		$response = curl_exec($ch);
		curl_close($ch);
		
		//return $response;
	}
        
        public function getTemplatebyId($id){
		$DB =  new DB();
		$where = " WHERE a.sms_template_id='".$id."'";
		$productDetails = $DB->query("SELECT a.* FROM " . $this->table. " as a $where");
		return $productDetails[0];
	}
        
        function OTPSMS($otp,$mobileNo){
                $this->sms_template_id = 1;
		$smsMessage = $this->Find();
                if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
                $smsBody=$smsMessage['template_body_kannada'];    
                }else{
                $smsBody=$smsMessage['template_body'];    
                }
		$smsContent = str_replace("##OTP##",$otp, $smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		return $resp = $this->sendSMS($mobileNo,urlencode($smsContent));
	}
        
        function sendRegistrationSMS($caaf,$mobileNo,$name,$email,$password){
                $this->sms_template_id = 2;
		$smsMessage = $this->Find();
                if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
                $smsBody=$smsMessage['template_body_kannada'];    
                }else{
                $smsBody=$smsMessage['template_body'];    
                }
                
		$smsContent = str_replace("##CAAFNO##",$caaf, $smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
                $smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
                $smsContent = str_replace("##EMAIL##",$email, $smsContent);
                $smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
                unset($_SESSION['SMS_LANGUAGE']);
                return $resp;
	}
        
        function sendVtpApplicationSMS($caaf,$mobileNo,$name,$email,$password){
                $this->sms_template_id = 3;
		$smsMessage = $this->Find();
                if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
                $smsBody=$smsMessage['template_body_kannada'];    
                }else{
                $smsBody=$smsMessage['template_body'];    
                }
		$smsContent = str_replace("##CAAFNO##",$caaf,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
                $smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
                $smsContent = str_replace("##EMAIL##",$email, $smsContent);
                $smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
                unset($_SESSION['SMS_LANGUAGE']);
                return $resp;
	}
	function sendVendorApplicationSMS($caaf,$mobileNo,$name,$email,$password){
			$this->sms_template_id = 53;
	$smsMessage = $this->Find();
			if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
			$smsBody=$smsMessage['template_body_kannada'];    
			}else{
			$smsBody=$smsMessage['template_body'];    
			}
	$smsContent = str_replace("##CAAFNO##",$caaf,$smsBody);
	$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
			$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
			$smsContent = str_replace("##EMAIL##",$email, $smsContent);
			$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
	$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
			unset($_SESSION['SMS_LANGUAGE']);
			return $resp;
	}
        
    function sendGovtDeptTCRegistrationSMS($caaf,$mobileNo,$name,$email,$password){
                $this->sms_template_id = 5;
		$smsMessage = $this->Find();
                if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
                $smsBody=$smsMessage['template_body_kannada'];    
                }else{
                $smsBody=$smsMessage['template_body'];    
                }
		$smsContent = str_replace("##SERIALNUMBER##",$caaf,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
                $smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
                $smsContent = str_replace("##EMAIL##",$email, $smsContent);
                $smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
                unset($_SESSION['SMS_LANGUAGE']);
                return $resp;
	}
	function sendGovtDeptRegistrationSMS($caaf,$mobileNo,$name,$email,$password){
        $this->sms_template_id = 6;
		$smsMessage = $this->Find();
                
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##SERIALNUMBER##",$caaf,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
        
        function forgotPasswordToken($mobileNo,$token,$link){
			$this->sms_template_id = 4;
			$smsMessage = $this->Find();
			if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
			$smsBody=$smsMessage['template_body_kannada'];    
			}else{
			$smsBody=$smsMessage['template_body'];    
			}
			$smsContent = str_replace("##TOKEN##",$token, $smsBody);
			$smsContent = str_replace("##LINK##",$link, $smsContent);
			$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
			unset($_SESSION['SMS_LANGUAGE']);
			return $resp;
	}
		function sendLastLoginIpUser($mobileNo,$time,$ip,$role){
			$this->sms_template_id = 45;
			$smsMessage = $this->Find();
			if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
			$smsBody=$smsMessage['template_body_kannada'];    
			}else{
			$smsBody=$smsMessage['template_body'];    
			}
			$smsContent = str_replace("##TIME##",$time, $smsBody);
			$smsContent = str_replace("##IP##",$ip, $smsContent);
			$smsContent = str_replace("##USER_TYPE##",$role, $smsContent);
			$smsContent = str_replace("##NAME##",$_SESSION['NAME'], $smsContent);
			$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
			unset($_SESSION['SMS_LANGUAGE']);
			return $resp;
		}
        
        function sendCreatorRegistrationSMS($dept,$mobileNo,$name,$email,$password){
        $this->sms_template_id = 8;
		$smsMessage = $this->Find();
                
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##DEPARTMENT##",$dept,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##USERNAME##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
        
    function sendApproverRegistrationSMS($dept,$mobileNo,$name,$email,$password){
        $this->sms_template_id = 9;
		$smsMessage = $this->Find();
      	$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##DEPARTMENT##",$dept,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##USERNAME##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
        
        function sendStudentSelectedSMS($mobileNo,$name,$jobrole,$center,$tcPhone,$batchNo){
                $this->sms_template_id = 7;
		$smsMessage = $this->Find();
                $smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsBody);
		$smsContent = str_replace("##JOBROLE##",$jobrole, $smsContent);
		$smsContent = str_replace("##CENTERNAME##",$center, $smsContent);
                $smsContent = str_replace("##BATCHNUMBER##",$batchNo, $smsContent);
                $smsContent = str_replace("##PHONENUMBER##",$tcPhone, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
	
    function sendEmployeerRegistrationSMS($caaf,$mobileNo,$name,$email,$password){
		$this->sms_template_id = 4;
		$smsMessage = $this->Find();
		if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
			$smsBody=$smsMessage['template_body_kannada'];    
		}else{
			$smsBody=$smsMessage['template_body'];    
		}
		$smsContent = str_replace("##CAAFNO##",$caaf, $smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
				
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
                unset($_SESSION['SMS_LANGUAGE']);
                return $resp;
	}
		
	function sendJobSeekerRegistrationSMS($caaf,$mobileNo,$name,$email,$password){
        $this->sms_template_id = 5;
		$smsMessage = $this->Find();
                if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
                $smsBody=$smsMessage['template_body_kannada'];    
                }else{
                $smsBody=$smsMessage['template_body'];    
                }
                
		$smsContent = str_replace("##CAAFNO##",$caaf, $smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
                $smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
                $smsContent = str_replace("##EMAIL##",$email, $smsContent);
                $smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
                unset($_SESSION['SMS_LANGUAGE']);
                return $resp;
	}   
        
        function sendECRegistrationSMS($caaf,$mobileNo,$name,$email,$password){
        $this->sms_template_id = 10;
		$smsMessage = $this->Find();
                
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##SERIALNUMBER##",$caaf,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
	
	function sendCAAFSupervisorRegistrationSMS($caaf,$mobileNo,$name,$email,$password,$type=''){
        $this->sms_template_id = 19;
		$smsMessage = $this->Find();
                
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##SERIALNUMBER##",$caaf,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		$smsContent = str_replace("##TYPE##",$type, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
	
	function sendTPAgencyRegistrationSMS($caaf,$mobileNo,$name,$email,$password,$districtNames){
        $this->sms_template_id = 11;
		$smsMessage = $this->Find();
                
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##SERIALNUMBER##",$caaf,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##DISTRICTS##",$districtNames, $smsContent);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
	function notifyUpdateTPSMS($caaf,$mobileNo,$name){
        $this->sms_template_id = 12;
		$smsMessage = $this->Find();
                
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##SERIALNUMBER##",$caaf,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
        function sendCaafLoginCredentials($caaf,$mobileNo,$name,$email,$password){
                $this->sms_template_id = 13;
		$smsMessage = $this->Find();
                if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
                $smsBody=$smsMessage['template_body_kannada'];    
                }else{
                $smsBody=$smsMessage['template_body'];    
                }
		$smsContent = str_replace("##SERIALNUMBER##",$caaf,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
                $smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
                $smsContent = str_replace("##EMAIL##",$email, $smsContent);
                $smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
                unset($_SESSION['SMS_LANGUAGE']);
                return $resp;
	}
	
	function sendTPAccreditationSMS($tpName,$tpSN,$tpmemberMobile,$tpmemberName,$accreditationStatus)
	{
		//$smsContent="Dear ".$tpmemberName.", Training Provider ".$tpName." with serial number ".$tpSN." has been ".$accreditationStatus;
		$this->sms_template_id = 14;
		$smsMessage = $this->Find();
		
		$smsBody=$smsMessage['template_body'];  
		
		$smsContent = str_replace("##TPMEMBERNAME##",$tpmemberName,$smsBody);
		$smsContent = str_replace("##TPNAME##",$tpName, $smsContent);
		$smsContent = str_replace("##TPSNO##",$tpSN, $smsContent);
		$smsContent = str_replace("##STATUS##",$accreditationStatus, $smsContent);
		
		$resp = $this->sendSMS($tpmemberMobile,urlencode($smsContent));
	}
	function sendTPAccreditatingPersonnelSMS($serialNumber,$mobileNo,$name,$email,$password,$role,$districtNames)
	{
		$this->sms_template_id = 15;
		$smsMessage = $this->Find();
                
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##SERIALNUMBER##",$serialNumber,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##PERSONROLE##",$role, $smsContent);
		$smsContent = str_replace("##DISTRICT##",$districtNames, $smsContent);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
	function sendTCAgencyRegistrationSMS($creatorSerialNumber,$mobileNo,$name,$email,$password,$agencyName,$districtNames)
	{
		$this->sms_template_id = 16;
		$smsMessage = $this->Find();
                
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##SERIALNUMBER##",$creatorSerialNumber,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##AGENCYTYPE##",$agencyName, $smsContent);
		$smsContent = str_replace("##DISTRICT##",$districtNames, $smsContent);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
	function sendTCAccreditatingPersonnelSMS($serialNumber,$mobileNo,$name,$email,$password,$role,$agencyName,$districtNames)
	{
		$this->sms_template_id = 17;
		$smsMessage = $this->Find();
                
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##SERIALNUMBER##",$serialNumber,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##PERSONROLE##",$role, $smsContent);
		$smsContent = str_replace("##AGENCYTYPE##",$agencyName, $smsContent);
		$smsContent = str_replace("##DISTRICT##",$districtNames, $smsContent);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
        
        function sendCounsellorRegistrationSMS($type,$SerialNumber,$mobileNo,$name,$email,$password){
        $this->sms_template_id = 25;
		$smsMessage = $this->Find();
                
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##SERIALNUMBER##",$SerialNumber,$smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
                $smsContent = str_replace("##TYPE##",$type, $smsContent);
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
	
	/* function sendTCJobroleStatusChangeSMS()
	{
		
	} */
	
	function sendEmployerCredentialsSMS($eaf,$mobileNo,$name,$email,$password){
		$this->sms_template_id = 21;
		$smsMessage = $this->Find();
		if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
			$smsBody=$smsMessage['template_body_kannada'];    
		}else{
			$smsBody=$smsMessage['template_body'];    
		}
		$smsContent = str_replace("##EAFNO##",$eaf, $smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
				
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
                unset($_SESSION['SMS_LANGUAGE']);
                return $resp;
	}
	function sendJobOpeningSMS($mobileNo,$name,$email,$message){
		$this->sms_template_id = 20;
		$smsMessage = $this->Find();
		
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##NAME##",$name, $smsBody);
		$smsContent = str_replace("##NAME##",$name, $smsContent);
		$smsContent = str_replace("##JOBOPENINIGDETAILS##",$message, $smsContent);
		
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
		
	}
	function sendJobApplicationSMS($mobileNo,$name,$email,$message){
		$this->sms_template_id = 22;
		$smsMessage = $this->Find();
		
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##NAME##",$name, $smsBody);
		$smsContent = str_replace("##NAME##",$name, $smsContent);
		$smsContent = str_replace("##JOBAPPLICATIONDETAILS##",$message, $smsContent);
		
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
	function sendJobApplicationStatusSMS($mobileNo,$name,$email,$message){
		$this->sms_template_id = 23;
		$smsMessage = $this->Find();
		
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##NAME##",$name, $smsBody);
		$smsContent = str_replace("##NAME##",$name, $smsContent);
		$smsContent = str_replace("##JOBAPPLICATIONSTATUSDETAILS##",$message, $smsContent);
		
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
	/*JobFair SPOC SMS Notification */
	function sendJobFairCredentialsSMS($jof,$mobileNo,$name,$email,$password){
		$this->sms_template_id = 24;
		$smsMessage = $this->Find();
		if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
			$smsBody=$smsMessage['template_body_kannada'];    
		}else{
			$smsBody=$smsMessage['template_body'];    
		}
		$smsContent = str_replace("##NAME##",$name, $smsBody);
		$smsContent = str_replace("##NAME##",$name, $smsContent);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
				
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
                unset($_SESSION['SMS_LANGUAGE']);
                return $resp;
	}
	/*JobFair SPOC SMS Notification */
	/*YES Staff SMS Notification */
	function sendYesStaffCredentialsSMS($mobileNo,$name,$email,$password){
		$this->sms_template_id = 44;
		$smsMessage = $this->Find();
		if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
			$smsBody=$smsMessage['template_body_kannada'];    
		}else{
			$smsBody=$smsMessage['template_body'];    
		}
		$smsContent = str_replace("##NAME##",$name, $smsBody);
		$smsContent = str_replace("##NAME##",$name, $smsContent);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
				
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
                unset($_SESSION['SMS_LANGUAGE']);
                return $resp;
	}
	/*YES Staff SMS Notification */
	/**JobFair Attendee selection*/
	function sendJobAttendeeSelectionSMS($mobileNo,$name,$empname,$job,$spocname,$spocphone){
		$this->sms_template_id = 30;
		$smsMessage = $this->Find();
		if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
			$smsBody=$smsMessage['template_body_kannada'];    
		}else{
			$smsBody=$smsMessage['template_body'];    
		}
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsBody);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##EMPLOYERNAME##",$empname, $smsContent);
		$smsContent = str_replace("##JOB##",$job, $smsContent);
		$smsContent = str_replace("##SPOCFIRSTNAME##",$spocname, $smsContent);
		$smsContent = str_replace("##SPOCPHONENUMBER##",$spocphone, $smsContent);		
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
	/**JobFair Attendee Rejection*/
	function sendJobAttendeeRejectionSMS($mobileNo,$name,$empname,$job){
		$this->sms_template_id = 31;
		$smsMessage = $this->Find();
		
		if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
			$smsBody=$smsMessage['template_body_kannada'];    
		}else{
			$smsBody=$smsMessage['template_body'];    
		}  
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsBody);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##EMPLOYERNAME##",$empname, $smsContent);
		$smsContent = str_replace("##JOB##",$job, $smsContent);		
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
	/** sendJobFairAttendeeSMS */
	function sendJobFairAttendeeSMS($mobileNo,$name,$jobfair){
		$this->sms_template_id = 29;
		$smsMessage = $this->Find();
		
		if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
			$smsBody=$smsMessage['template_body_kannada'];    
		}else{
			$smsBody=$smsMessage['template_body'];    
		}  
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsBody);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##JOBFAIR##",$jobfair, $smsContent);		
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}
	/** Send SMS to TP CEO for Accreditation Status **/
	function sendAccreditationStatusSMSToTP($tpCEO,$tpCEOMobile,$tcNumber,$tcName,$accreditationStatus,$smsTemplateId)
	{
		//$tpCEOMobile = 7097774779;
		$this->sms_template_id = $smsTemplateId;
		$smsMessage = $this->Find();
		$smsBody=$smsMessage['template_body']; 
		$smsContent = str_replace("##TP_CEO##",$tpCEO, $smsBody);
		$smsContent = str_replace("##TC_NUMBER##",$tcNumber, $smsContent);
		$smsContent = str_replace("##TC_NAME##",$tcName, $smsContent);		
		$smsContent = str_replace("##STATUS##",$accreditationStatus, $smsContent);
		
		
		$resp = $this->sendSMS($tpCEOMobile,urlencode($smsContent));
		return $resp;
	}
	
	function sendTrainerCredentialsSMS($eaf,$mobileNo,$name,$email,$password){
		$this->sms_template_id = 37;
		$smsMessage = $this->Find();
		if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
			$smsBody=$smsMessage['template_body_kannada'];    
		}else{
			$smsBody=$smsMessage['template_body'];    
		}
		$smsContent = str_replace("##SERIALNUMBER##",$eaf, $smsBody);
		$smsContent = str_replace("##SITEURL##",CONFIG_SERVER_ROOT, $smsContent);
		$smsContent = str_replace("##FIRSTNAME##",$name, $smsContent);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
				
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
                unset($_SESSION['SMS_LANGUAGE']);
                return $resp;
	}
	function sendEmailInspectorCredentialsSMS($mobileNo,$name,$email,$password){
		$this->sms_template_id = 36;
		$smsMessage = $this->Find();
		if(isset($_SESSION['SMS_LANGUAGE']) && $_SESSION['SMS_LANGUAGE']=='kannada'){
			$smsBody=$smsMessage['template_body_kannada'];    
		}else{
			$smsBody=$smsMessage['template_body'];    
		}
		$smsContent = str_replace("##NAME##",$name, $smsBody);
		$smsContent = str_replace("##EMAIL##",$email, $smsContent);
		$smsContent = str_replace("##PASSWORD##",$password, $smsContent);
				
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
                unset($_SESSION['SMS_LANGUAGE']);
                return $resp;
	}
	function sendBatchCommencementSMS($accreditatorType,$mobile,$name,$centerName,$centerNumber,$hocMobile,$batchSerialNumber)
	{
	     if($accreditatorType=="tlecVerifier")
	     {
		$this->sms_template_id = 40;
	     }
	     else
	     {
		$this->sms_template_id = 41;    
	     }
	     $smsMessage = $this->Find();
	     $smsBody=$smsMessage['template_body']; 

	     $batchCommenceDate = date('d-m-Y');

	     $smsContent = str_replace("##NAME##",$name, $smsBody);
	     $smsContent = str_replace("##BATCHNUMBER##",$batchSerialNumber, $smsContent);
	     $smsContent = str_replace("##BATCHCOMMENCEMENTDATE##",$batchCommenceDate, $smsContent);
	     $smsContent = str_replace("##CENTERNAME##",$centerName, $smsContent);
	     $smsContent = str_replace("##CAAFNUMBER##",$centerNumber, $smsContent);
	     $smsContent = str_replace("##HOCMOBILE##",$hocMobile, $smsContent);
				
	     $resp = $this->sendSMS($mobile,urlencode($smsContent));
	     
	     return $resp;
	}

	function sendBatchApprovalSMS($type,$mobile,$name,$centerName,$centerNumber,$centerAddress,$batchSerialNumber)
	{
		if($accreditatorType=="TPSPOC")
		{
		$this->sms_template_id = 38;
		}
		else
		{
		$this->sms_template_id = 39;    
		}
		$smsMessage = $this->Find();
		$smsBody=$smsMessage['template_body']; 

		$smsContent = str_replace("##NAME##",$name, $smsBody);
		$smsContent = str_replace("##BATCHNUMBER##",$batchSerialNumber, $smsContent);
		$smsContent = str_replace("##CENTERNAME##",$centerName, $smsContent);
		$smsContent = str_replace("##CAAFNUMBER##",$centerNumber, $smsContent);
		$smsContent = str_replace("##CENTERADDRESS##",$centerAddress, $smsContent);
				   
		$resp = $this->sendSMS($mobile,urlencode($smsContent));
	}

	/** Event Notification sms */
	function sendEventNotifySMS($mobileNo,$eventname,$start,$end,$by,$location){
		$this->sms_template_id = 46;
		$smsMessage = $this->Find();
		
		$smsBody=$smsMessage['template_body'];  
		$smsContent = str_replace("##EVENT##",$eventname, $smsBody);
		$smsContent = str_replace("##START##",$start, $smsContent);
		$smsContent = str_replace("##END##",$end, $smsContent);
		$smsContent = str_replace("##BY##",$by, $smsContent);
		$smsContent = str_replace("##LOCATION##",$location, $smsContent);
		
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}


	function sendGenericSMSTemplate($templateId,$mobileNo,$tokenArray)
	{
		$this->sms_template_id = $templateId;
		$smsMessage = $this->Find();
                
		$smsBody=$smsMessage['template_body'];
		$smsContent = str_replace("##TEMPLATE_NAME##", '', $smsBody);

		foreach($tokenArray as $key=>$value){			
			$smsContent = str_replace("$key","$value",$smsContent);
		}		
	
		$resp = $this->sendSMS($mobileNo,urlencode($smsContent));
		return $resp;
	}


	/** Event sms */

}
?>