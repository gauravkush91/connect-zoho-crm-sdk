<?php
use zcrmsdk\crm\crud\ZCRMAttachment;
use zcrmsdk\crm\crud\ZCRMCustomView;
use zcrmsdk\crm\crud\ZCRMCustomViewCategory;
use zcrmsdk\crm\crud\ZCRMCustomViewCriteria;
use zcrmsdk\crm\crud\ZCRMEventParticipant;
use zcrmsdk\crm\crud\ZCRMField;
use zcrmsdk\crm\crud\ZCRMInventoryLineItem;
use zcrmsdk\crm\crud\ZCRMJunctionRecord;
use zcrmsdk\crm\crud\ZCRMLayout;
use zcrmsdk\crm\crud\ZCRMLeadConvertMapping;
use zcrmsdk\crm\crud\ZCRMLeadConvertMappingField;
use zcrmsdk\crm\crud\ZCRMLookupField;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\crud\ZCRMModuleRelatedList;
use zcrmsdk\crm\crud\ZCRMModuleRelation;
use zcrmsdk\crm\crud\ZCRMNote;
use zcrmsdk\crm\crud\ZCRMOrgTax;
use zcrmsdk\crm\crud\ZCRMPermission;
use zcrmsdk\crm\crud\ZCRMPickListValue;
use zcrmsdk\crm\crud\ZCRMPriceBookPricing;
use zcrmsdk\crm\crud\ZCRMProfileCategory;
use zcrmsdk\crm\crud\ZCRMTrashRecord;
use zcrmsdk\crm\crud\ZCRMTax;
use zcrmsdk\crm\crud\ZCRMTag;
use zcrmsdk\crm\crud\ZCRMSection;
use zcrmsdk\crm\crud\ZCRMRelatedListProperties;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\crud\ZCRMProfileSection;
use zcrmsdk\crm\exception\ZCRMException;
use zcrmsdk\crm\setup\org\ZCRMOrganization;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\crm\setup\users\ZCRMProfile;
use zcrmsdk\crm\setup\users\ZCRMRole;
use zcrmsdk\crm\setup\users\ZCRMUser;
use zcrmsdk\crm\setup\users\ZCRMUserCustomizeInfo;
use zcrmsdk\crm\setup\users\ZCRMUserTheme;
use zcrmsdk\oauth\ZohoOAuth;
use zcrmsdk\oauth\ZohoOAuthClient;

class ZCZohoHelper{
	
	private $successLog = ZC_PLUGINPATH ."/logs/success.log";
	private $errorLog 	= ZC_PLUGINPATH ."/logs/error.log";
	private $unknownLog = ZC_PLUGINPATH ."/logs/unknown.log";
	private $deletedLog = ZC_PLUGINPATH ."/logs/deleted.log";

	public function __construct($configuration){
		try {
			//ZCRMRestClient::setCurrentUserEmailId($zohoEmail);
			ZCRMRestClient::initialize($configuration);
		} catch (Exception $e) {
			$strError = "[__construct] ERROR: ".$e->getMessage();
			$this->addLog($this->errorLog,$strError);
		}
	}

	public function authConnect($code){
		$returnResponse = array();
		$returnResponse["status"] = "error";
		try{
			$oAuthClient  = ZohoOAuth::getClientInstance();
			$refreshToken = "";
			if(!empty($code)){
				$grantToken 			= $code;
				$oAuthTokensAccessToken = $oAuthClient->generateAccessToken($grantToken);
				$refreshToken 			= $oAuthTokensAccessToken->getRefreshToken();
				if(!empty($refreshToken)){
					$refreshToken 	= $refreshToken;
					$userIdentifier = $zconnect_email;
					$oAuthTokens 	= $oAuthClient->generateAccessTokenFromRefreshToken($refreshToken,$userIdentifier);
					$returnResponse["status"]	= "success";
					$returnResponse["response"]	= "connected";	
				}else{
					$returnResponse["status"]	= "error";
					$returnResponse["response"]	= $refreshToken;	
				}
				$returnResponse["code"]		= 200;
			}
		}catch (Exception $e) {
			$strError = "[authConnect] ($code) ERROR: ".$e->getMessage();
			$this->addLog($this->errorLog,$strError);
			$returnResponse["code"]		= $e->getCode();
			$returnResponse["status"]	= "error";
			$returnResponse["response"]	= $strError;
		}
		return $returnResponse;
	}

	public function getAllActiveUsers(){
		$returnResponse = [];
		$allUsersData   = [];
		try { 
			$bulkAPIResponse = ZCRMOrganization::getInstance()->getAllUsers();
			$users           = $bulkAPIResponse->getData();
			//print_r($users);
			foreach($users as $userInstance) {
				$userStatus = '';
            	$userId     = $userInstance->getId();
            	$userName   = $userInstance->getFullName();
            	$userEmail  = $userInstance->getEmail();
            	$userStatus = $userInstance->getStatus();
            	if($userStatus=="active"){
            		$allUsersData[$userEmail] = $userId;	
            	}
            	
        	}
        	$returnResponse["code"]		= 200;
			$returnResponse["status"]	= "success";
			$returnResponse["response"]	= "all users info";
			$returnResponse["data"]		= $allUsersData;
		}catch (Exception $e) {
			$strError = "[getAllUsers] ERROR: ".$e->getMessage();
			$this->addLog($this->errorLog,$strError);
			$returnResponse["code"]		= $e->getCode();
			$returnResponse["status"]	= "error";
			$returnResponse["response"]	= $strError;
			$returnResponse["data"]		= $allUsersData;
		}
		return $returnResponse;
	}

	public function getRecordById($module,$recordID){
		$returnResponse = array();
		try {
			$moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module);
			$response  = $moduleIns->getRecord($recordID);
			$record    = $response->getData();
			$returnResponse["status"]	= "success";
			$returnResponse["response"]	= $record;
		} catch (Exception $e) {
			$strError = "[getRecordById] ($module) ERROR: ".$e->getMessage();
			$this->addLog($this->errorLog,$strError);
			$returnResponse["code"]		= $e->getCode();
			$returnResponse["status"]	= "error";
			$returnResponse["response"]	= $strError;
			
		}
		return $returnResponse;
	}

	public function searchModuleByEmail($module,$email){
		$returnResponse = array();
		try {
			$zcrmModule      = ZCRMModule::getInstance($module); 
			$bulkAPIResponse = $zcrmModule->searchRecordsByCriteria("(Email:equals:".$email.")");
			$map             = $bulkAPIResponse->getData(); // $records - array of ZCRMRecord
			$recordFound     = array();
			foreach ($map as $key=>$value){
				if($value instanceof ZCRMRecord){
					$recordFound[] = $value;
			      //return $value->getEntityId();
				}
			}
			$returnResponse["status"]	= "success";
			$returnResponse["response"]	= $recordFound;
		} catch (Exception $e) {
			$strError = "[searchModuleByEmail] ($module) ERROR: ".$e->getMessage();
			if($e->getMessage() =="No Content"){
				$returnResponse["status"]	= "success";
				$returnResponse["response"]	= "No Content";
			}else{
				$this->addLog($this->errorLog,$strError);
				$returnResponse["code"]		= $e->getCode();
				$returnResponse["status"]	= "error";
				$returnResponse["response"]	= $strError;	
			}
		}
		return $returnResponse;
	}

	public function searchAccountByName($accountName){
		$returnResponse = array();
		try {
			$zcrmModule      = ZCRMModule::getInstance("Accounts"); 
			$bulkAPIResponse = $zcrmModule->searchRecordsByCriteria("(Account_Name:equals:".$accountName.")");
			$map             = $bulkAPIResponse->getData(); // $records - array of ZCRMRecord
			$recordFound     = array();
			foreach ($map as $key=>$value){
				if($value instanceof ZCRMRecord){
					$recordFound[] = $value;
			      //return $value->getEntityId();
				}
			}
			$returnResponse["status"]	= "success";
			$returnResponse["response"]	= $recordFound;
		} catch (Exception $e) {
			$strError = "[searchAccountByName] ($module) ERROR: ".$e->getMessage();
			if($e->getMessage() =="No Content"){
				$returnResponse["status"]	= "success";
				$returnResponse["response"]	= "No Content";
			}else{
				$this->addLog($this->errorLog,$strError);
				$returnResponse["status"]	= "error";
				$returnResponse["response"]	= $strError;	
			}
			$returnResponse["code"]	= $e->getCode();
		}
		return $returnResponse;
	}

	public function searchAccountByAccountNo($accountNo){
		$returnResponse = array();
		try {
			$zcrmModule      = ZCRMModule::getInstance("Accounts"); 
			$bulkAPIResponse = $zcrmModule->searchRecordsByCriteria("(GM_Account_No:equals:".$accountNo.")");
			$map             = $bulkAPIResponse->getData(); // $records - array of ZCRMRecord
			$recordFound     = array();
			foreach ($map as $key=>$value){
				if($value instanceof ZCRMRecord){
					$recordFound[] = $value;
			      //return $value->getEntityId();
				}
			}
			$returnResponse["status"]	= "success";
			$returnResponse["response"]	= $recordFound;
		} catch (Exception $e) {
			$strError = "[searchAccountByAccountNo] ($module) ERROR: ".$e->getMessage();
			if($e->getMessage() =="No Content"){
				$returnResponse["status"]	= "success";
				$returnResponse["response"]	= "No Content";
			}else{
				$this->addLog($this->errorLog,$strError);
				$returnResponse["status"]	= "error";
				$returnResponse["response"]	= $strError;	
			}
			$returnResponse["code"]	= $e->getCode();
		}
		return $returnResponse;
	}

	public function insertRecord($module,$data,$setProperties=array(),$isWftrigger=false){
		$returnResponse = array();
		try{
			$moduleData = ZCRMRecord::getInstance($module, null);
			foreach ($data as $key => $value) {
				$moduleData->setFieldValue($key, $value);
			}

			if(!empty($setProperties)){
				if(array_key_exists("Created_Time",$setProperties)){
					$moduleData->setCreatedTime($setProperties['Created_Time']);
				}
				if(array_key_exists("Created_By",$setProperties)){
					$moduleData->setCreatedBy(ZCRMUser::getInstance($setProperties['Created_By'][0], $setProperties['Created_By'][1]));
				}
				if(array_key_exists("Modified_By",$setProperties)){
					$moduleData->setModifiedBy(ZCRMUser::getInstance($setProperties['Created_By'][0], $setProperties['Created_By'][1]));
				}
			}
  			//print_r($moduleData);
			if($isWftrigger==true){
				$wfTrigger 		= array("workflow");
				$createResponse = $moduleData->create($wfTrigger);
			}else{
				$createResponse = $moduleData->create();
			}
			
			$createResponseJson = $createResponse->getResponseJSON();
			$insertedId 		= $createResponseJson["data"][0]["details"]["id"];
			$strSuccess         = "[insertRecord] New record ($module) : ".$insertedId." Created Succefully";
			$this->addLog($this->successLog,$strSuccess);
			$returnResponse["status"]	= "success";
			$returnResponse["response"]	= $insertedId;

		}catch (Exception $e) {
			$strError = "[insertRecord] ERROR: ".$e->getMessage()." Data: ".json_encode($data)." "." ExceptionDetails ".json_encode($e->getExceptionDetails());
			$this->addLog($this->errorLog,$strError);
			$returnResponse["status"]	= "error";
			$returnResponse["response"]	= $strError;
			$returnResponse["code"]		= $e->getCode();
		}
		return $returnResponse;
	}

	public function updateRecord($module,$recordID,$data,$setProperties=array(),$isWftrigger=false){
		$returnResponse = array();
		try{
			$moduleData = ZCRMRecord::getInstance($module,$recordID);
			foreach ($data as $key => $value) {
				$moduleData->setFieldValue($key, $value);
			}

			if(!empty($setProperties)){
				if(array_key_exists("Created_Time",$setProperties)){
					$moduleData->setCreatedTime($setProperties['Created_Time']);
				}
				if(array_key_exists("Created_By",$setProperties)){
					$moduleData->setCreatedBy($setProperties['Created_By'][0]);
				}
				if(array_key_exists("Modified_By",$setProperties)){
					$moduleData->setModifiedBy($setProperties['Modified_By'][0]);
				}
			}

			if($isWftrigger==true){
				$wfTrigger 		= array("workflow");
				$updateResponse = $moduleData->update($wfTrigger);
			}else{
				$updateResponse = $moduleData->update();
			}

			$updateResponseJson = $updateResponse->getResponseJSON();
			$updateId 	= $updateResponseJson["data"][0]["details"]["id"];
			$strSuccess = "[updateRecord] Record ($module) : ".$updateId." Updated Succefully";
			$this->addLog($this->successLog,$strSuccess);
			$returnResponse["status"]	= "success";
			$returnResponse["response"]	= $updateId;
		}catch (Exception $e) {
			$strError = "[updateRecord] Record ($module - $moduleId)  ERROR: ".$e->getMessage()." Data: ".json_encode($data)." ExceptionDetails ".json_encode($e->getExceptionDetails());
			$this->addLog($this->errorLog,$strError);
			$returnResponse["status"]	= "error";
			$returnResponse["response"]	= $strError;
			$returnResponse["code"]		= $e->getCode();
		}
		return $returnResponse;
	}

	public function deleteRecord($module,$recordID,$isWftrigger=false){
		$returnResponse = array();
		try{
			$record = ZCRMRestClient::getInstance()->getRecordInstance($module,$recordID);
			$responseIns = 	$record->delete();
			$status 	 =	$responseIns->getStatus();
			$message 	 =	$responseIns->getMessage();
			$details 	 =	json_encode($responseIns->getDetails());
			if($status=="success"){
				$strSuccess  = "[deleteRecord record ($module) $ : ".$recordID." deleted Succefully";
				$this->addLog($this->successLog,$strSuccess);
				$returnResponse["status"]	= "success";
				$returnResponse["response"]	= $recordID;
			}else{
				$strError  = "[deleteRecord] Error ($module) $ : ".$recordID." deleting records ".$details;
				$this->addLog($this->errorLog,$strError);
				$returnResponse["status"]	= "error";
				$returnResponse["response"]	= $strError;
			}
		}catch (Exception $e) {
			$strError = "[deleteRecord] ERROR: ".$e->getMessage()." Data: $recordID "." ExceptionDetails ".json_encode($e->getExceptionDetails());
			$this->addLog($this->errorLog,$strError);
			$returnResponse["status"]	= "error";
			$returnResponse["response"]	= $strError;
			$returnResponse["code"]		= $e->getCode();
		}
		return $returnResponse;
	}

	public function createNotesInRecords($module,$recordID,$data){
		$returnResponse = array();
		try{
			$record = ZCRMRecord::getInstance($module,$recordID);
			$noteIns = ZCRMNote::getInstance($record);
			if(empty($data['title'])){
				$data['title'] = '.';
			}
			$noteIns->setTitle($data['title']);
			$noteIns->setContent($data['content']);

			if(array_key_exists("owner",$data) && !empty($data['owner'])){
				$noteIns->setOwner($data['owner']);
			}

			if(array_key_exists("created",$data) && !empty($data['created'])){
				$noteIns->setCreatedTime($data['created']);
			}

			if(array_key_exists("modified",$data) && !empty($data['modified'])){
				$noteIns->setCreatedTime($data['modified']);
			}

			if(array_key_exists("createdby",$data) && !empty($data['createdby'])){
				$noteIns->setCreatedBy(ZCRMUser::getInstance($data['createdby'][0], $data['createdby'][1]));
			}
			
			if(array_key_exists("modifiedby",$data) && !empty($data['modifiedby'])){
				$noteIns->setCreatedBy(ZCRMUser::getInstance($data['modifiedby'][0], $data['modifiedby'][1]));
			}	

			$responseIns 	= $record->addNote($noteIns);
			$zcrmNote    	= $responseIns->getData();
			$noteCrmId      = $zcrmNote->getId();
			$strSuccess 	= "[createNotesInRecords] New Note : ".$noteCrmId." Created Succefully";
			$this->addLog($this->successLog,$strSuccess);
			$returnResponse["status"]	= "success";
			$returnResponse["response"]	= $noteCrmId;
		}catch (Exception $e) {
			$strError = "[createNotesInRecords] ERROR: ".$e->getMessage()." Data: ".json_encode($data);
			$this->addLog($this->errorLog,$strError);
			$returnResponse["status"]	= "error";
			$returnResponse["response"]	= $strError;
			$returnResponse["code"]		= $e->getCode();
		}
		return $returnResponse;
	}

	public function createCallInRecords($module,$data){
		$returnResponse = array();
		try{
			$moduleData = ZCRMRecord::getInstance($module, null);
			foreach ($data as $key => $value) {
				$moduleData->setFieldValue($key, $value);
			}
			$createResponse = $moduleData->create();
			$createResponseJson = $createResponse->getResponseJSON();
			$insertedId 		= $createResponseJson["data"][0]["details"]["id"];
			$strSuccess         = "[createCallInRecords] New record ($module) : ".$insertedId." Created Succefully";
			$this->addLog($this->successLog,$strSuccess);
			$returnResponse["status"]	= "success";
			$returnResponse["response"]	= $insertedId;

		}catch (Exception $e) {
			$strError = "[createCallInRecords] ERROR: ".$e->getMessage()." Data: ".json_encode($data)." "." ExceptionDetails ".json_encode($e->getExceptionDetails());
			$this->addLog($this->errorLog,$strError);
			$returnResponse["status"]	= "error";
			$returnResponse["response"]	= $strError;
			$returnResponse["code"]		= $e->getCode();
		}
		return $returnResponse;
	}

	public function insertMultipleRecord($module,$data,$uniqueKey=null,$chunkNumber=99){
		$returnResponse 	=[];
		$multipleData 		=[];
		$allSucessresponce 	=[];
		$element_count 	=  count($data);
		foreach ($data as $chunkKey => $chunkValue) {
			$insertData = $chunkValue;
			$moduleData = ZCRMRecord::getInstance($module, null);
			foreach ($insertData as $key => $value) {
				$moduleData->setFieldValue($key, $value);
			}
			$multipleData[] = $moduleData;
		}

		if(!empty($multipleData)) {
			$counter = 1;
			$countdataElement 	= count($multipleData);
			$chunks 			= array_chunk($multipleData,$chunkNumber);
			$responseChunk 		= array();
			$chunkCounter 		= 1;
			foreach($chunks as $chunk){
				$responseChunkRecords = array();
				try {
					$zcrmModuleIns   = ZCRMModule::getInstance($module);
					$bulkAPIResponse = $zcrmModuleIns->createRecords($chunk);
					$entityResponses = $bulkAPIResponse->getEntityResponses();
		    		//echo "Chunk count "."<br/>".count($bulkAPIResponse)."<br/>".count($entityResponses);
					foreach($entityResponses as $entitykey => $entityResponse) {
						$strSuccess =	"";
						$strError 	=	"";
						$createdResponseJson = $entityResponse->getResponseJSON();
						if($entityResponse->getStatus() == "success") {
							$createdId     			= $createdResponseJson["details"]["id"];
							$strSuccess 			= "[insertMultipleRecord] #$counter | $module created: ".$createdId;
							$this->addLog($this->successLog,$strSuccess);
							$dataReturnChunk 	=[];
							$uniqueValue 		= "";
							if(!empty($uniqueKey)){
								$uniqueValue = $entityResponse->getData()->getFieldValue($uniqueKey);
							}
							$dataReturnChunk["crmid"]  = $createdId;
							$dataReturnChunk["module"] = $module;
							$allSucessresponce[$uniqueValue] = $dataReturnChunk;

							$responseChunkRecords[] = $strSuccess;
						} else {
							$strError ="[insertMultipleRecord] #$counter | Error created $module : ".json_encode($createdResponseJson).' Status: '.$entityResponse->getStatus()." entityResponse ".json_encode($entityResponse)." entitykey: $entitykey Data:".json_encode($data[$counter-1]);
							$this->addLog($this->errorLog,$strError);
							$responseChunkRecords[] = $strError;
						}
						$counter++;
					}
					$responseChunk["response_".$chunkCounter] = json_encode($responseChunkRecords);
				}catch(Exception $err) {
					$strErrorCatch ="[insertMultipleRecord] Error creating Multiple $module record: Code- ".$err->getCode()." Message- ".$err->getMessage()." getExceptionDetails ".json_encode($err->getExceptionDetails());
					$this->addLog($this->errorLog,$strErrorCatch);
					$responseChunkRecords[] = $strErrorCatch;
					$responseChunk["response_".$chunkCounter] = json_encode($responseChunkRecords);
				}
				$chunkCounter++;
			}
		}else{
			$returnResponse["status"]	= "error";
			$returnResponse["response"]	= "multipleData is blank";
		}
		$returnResponse["status"]	= "success";
		$returnResponse["response"]	= json_encode($responseChunk);
		$returnResponse["data"]		= $allSucessresponce;
		return $returnResponse;
	}

	public function updateMultipleRecord($module,$recordID,$data,$uniqueKey=null,$chunkNumber=99){
		$returnResponse 	=[];
		$multipleData 		=[];
		$allSucessresponce 	=[];
		$element_count 		=count($data);
		foreach ($data as $chunkKey => $chunkValue) {
			//$updateData = $chunkValue;
			$moduleData = ZCRMRecord::getInstance($module,$recordID);
			foreach ($chunkValue as $key => $value) {
				$moduleData->setFieldValue($key, $value);
			}
			$multipleData[] = $moduleData;
		}
		if(!empty($multipleData)) {
			$counter = 1;
			$countdataElement 	= count($multipleData);
			$chunks 			= array_chunk($multipleData,$chunkNumber);
			$responseChunk 		= array();
			$chunkCounter 		= 1;
			foreach($chunks as $chunk){
				$responseChunkRecords = array();
				try {
					$zcrmModuleIns   = ZCRMModule::getInstance($module);
					$bulkAPIResponse = $zcrmModuleIns->updateRecords($chunk);
					$entityResponses = $bulkAPIResponse->getEntityResponses();

					foreach($entityResponses as $entitykey => $entityResponse) {
						$strSuccess =	"";
						$strError 	=	"";
						$createdResponseJson = $entityResponse->getResponseJSON();
						if($entityResponse->getStatus() == "success") {
							$createdId    = $createdResponseJson["details"]["id"];
							$strSuccess   = "[updateMultipleRecord] #$counter | $module updated: ".$createdId;
							$this->addLog($this->successLog,$strSuccess);
							$dataReturnChunk 	=[];
							$uniqueValue 		= "";
							if(!empty($uniqueKey)){
								$uniqueValue = $entityResponse->getData()->getFieldValue($uniqueKey);
							}
							$dataReturnChunk["crmid"]  = $createdId;
							$dataReturnChunk["module"] = $module;
							$allSucessresponce[$uniqueValue] = $dataReturnChunk;

							$responseChunkRecords[] = $strSuccess;
						} else {
							$strError ="[updateMultipleRecord] #$counter | Error updated $module : ".json_encode($createdResponseJson).' Status: '.$entityResponse->getStatus()." entityResponse ".json_encode($entityResponse)." entitykey: $entitykey Data:".json_encode($data[$counter-1]);
							$this->addLog($this->errorLog,$strError);
							$responseChunkRecords[] = $strError;
						}
						$counter++;
					}
					$responseChunk["response_".$chunkCounter] = json_encode($responseChunkRecords);
				}catch(Exception $err) {
					$strErrorCatch ="[updateMultipleRecord] Error updating Multiple $module record: Code- ".$err->getCode()." Message- ".$err->getMessage()." getExceptionDetails ".json_encode($err->getExceptionDetails());
					$this->addLog($this->errorLog,$strErrorCatch);
					$responseChunkRecords[] = $strErrorCatch;
					$responseChunk["response_".$chunkCounter] = json_encode($responseChunkRecords);
				}

				$chunkCounter++;
			}
		}else{
			$returnResponse["status"]	= "error";
			$returnResponse["response"]	= "update multipleData is blank";
		}
		$returnResponse["status"]	= "success";
		$returnResponse["response"]	= json_encode($responseChunk);
		$returnResponse["data"]		= $allSucessresponce;
		return $returnResponse;
	}

	public function insertMultipleNotesToRecord($module,$recordID,$data,$chunkNumber=99){
		$returnResponse 	=[];
		$multipleData 		=[];
		//$allSucessresponce =[];
		$element_count 		=count($data);
		$moduleData 		=ZCRMRecord::getInstance($module,$recordID);
		foreach ($data as $chunkKey => $chunkValue) {
			$insertData = $chunkValue;
			$noteIns 	= ZCRMNote::getInstance($moduleData);

			if(empty($insertData['title'])){
				$insertData['title'] = '.';
			}
			$noteIns->setTitle($insertData['title']);
			$noteIns->setContent($insertData['content']);
			if(array_key_exists("owner",$insertData) && !empty($insertData['owner'])){
				$noteIns->setOwner($insertData['owner']);
			}
			$multipleData[] = $noteIns;
		}
		
		if(!empty($multipleData)) {
			$counter = 1;
			$countdataElement 	= count($multipleData);
			$chunks 			= array_chunk($multipleData,$chunkNumber);
			$responseChunk 		= array();
			$chunkCounter 		= 1;
			foreach($chunks as $chunk){
		    	//echo "insertMultipleNotesToRecord chunk inside loop<br/>";
				$responseChunkRecords = array();
				try {
		    		//$zcrmModuleIns   = ZCRMRecord::getInstance($module, $moduleId);
					$bulkAPIResponse = $moduleData->addNotes($chunk);
					$entityResponses = $bulkAPIResponse->getEntityResponses();
		    		//echo "Chunk count "."<br/>".count($bulkAPIResponse)."<br/>".count($entityResponses);
					foreach($entityResponses as $entityResponse) {
						$strSuccess =	"";
						$strError 	=	"";
						$createdResponseJson 	= $entityResponse->getResponseJSON();
						if($entityResponse->getStatus() == "success") {
							$createdId  = $createdResponseJson["details"]["id"];
							$strSuccess = "[insertMultipleNotesToRecord] #$counter | $module($moduleId) - Notes Created: Notes ID".$createdId;
							$this->addLog($this->successLog,$strSuccess);
							$responseChunkRecords[] = $strSuccess;
						} else {
							$strError ="[insertMultipleNotesToRecord] #$counter | Error created $module($moduleId) : ".json_encode($createdResponseJson).' Status: '.$entityResponse->getStatus()." Data: ".json_encode($data[$counter-1]);
							$this->addLog($this->errorLog,$strError);
							$responseChunkRecords[] = $strError;
						}
						$counter++;
					}
					$responseChunk["response_".$chunkCounter]	= json_encode($responseChunkRecords);
				}catch(Exception $err) {
					$strErrorCatch ="[insertMultipleNotesToRecord] Error creating Multiple $module record ID $moduleId: Code- ".$err->getCode()." Message- ".$err->getMessage()." getExceptionDetails ".json_encode($err->getExceptionDetails());
					$this->addLog($this->errorLog,$strErrorCatch);
					$responseChunkRecords[] = $strErrorCatch;
					$responseChunk["response_".$chunkCounter]	= json_encode($responseChunkRecords);
				}
				$chunkCounter++;
			}
		}else{
			$returnResponse["status"]	= "error";
			$returnResponse["response"]	= "multipleData is blank";
		}
		$returnResponse["status"]	= "success";
		$returnResponse["response"]	= json_encode($responseChunk);
		//$returnResponse["data"]		= $allSucessresponce;
		return $returnResponse;

	}
	
	public function deleteMultipleRecord($module,$multipleData,$chunkNumber=99){
		$returnResponse = array();
		$element_count 	=  count($multipleData);
		//echo "multipleData<br/>";
		//print_r($multipleData);
		if(!empty($multipleData)) {
			$counter = 1;
			$countdataElement 	= count($multipleData);
			$chunks 			= array_chunk($multipleData,$chunkNumber);
			$responseChunk 		= array();
			$chunkCounter 		= 1;
		    //echo "Chunk<br/>";
		    //print_r($chunks);
			foreach($chunks as $chunk){
				$responseChunkRecords = array();
				try {
					$moduleIns = ZCRMRestClient::getInstance()->getModuleInstance($module);
					$responseIn = $moduleIns->deleteRecords($chunk);

					foreach ($responseIn->getEntityResponses() as $responseIns) {
						$strSuccess = "";
						$strError 	= "";
						if($responseIns->getStatus() == "success") {
							$strSuccess ="[deleteMultipleRecord] Success #$counter | $module record deleted: ".$responseIns->getMessage()."  --- ".json_encode($responseIns->getDetails());
							$this->addLog($this->deletedLog,$strSuccess);
							$responseChunkRecords[] = $strSuccess;
						}else {
							$strError ="[deleteMultipleRecord] Error #$counter | $module record deleted: ".$responseIns->getMessage()."  --- ".json_encode($responseIns->getDetails());
							$this->addLog($this->deletedLog,$strError);
							$responseChunkRecords[] = $strError;
						}
						$counter++;
					}
					$responseChunk["response_".$chunkCounter]	= json_encode($responseChunkRecords);
				}catch(Exception $err) {
					$strErrorCatch ="[deleteMultipleRecord] Error deleteting Multiple $module record: Code- ".$err->getCode()." Message- ".$err->getMessage();
					$this->addLog($this->errorLog,$strErrorCatch);
					$responseChunkRecords[] = $strErrorCatch;
					$responseChunk["response_".$chunkCounter]	= json_encode($responseChunkRecords);
				}
				$chunkCounter++;
			}
		}else{
			$returnResponse["status"]	= "error";
			$returnResponse["response"]	= "multipleData ID is blank";
		}
		$returnResponse["status"]	= "success";
		$returnResponse["response"]	= json_encode($responseChunk);
		return $returnResponse;
	}

	public function getRelatedRecords($module,$recordID,$relatedRecordAPIName,$pageNo=1){
		$returnResponse = array();
		try{
			$perPage 		= 200;
			$paraMap 		= array("page"=>$pageNo,"per_page"=>$perPage);
			$record   = ZCRMRestClient::getInstance()->getRecordInstance($module,$recordID); 
			$dataInfo = $record->getRelatedListRecords($relatedRecordAPIName,$paraMap);
			$data     = $dataInfo->getData();
			$recordIDArray = array();
			foreach ($data as $sakey => $sarecord) {
				$recordID = $sarecord->getEntityId();
				$recordIDArray[] = $recordID;
			}
			$returnResponse["status"]	= "success";
			$returnResponse["response"]	= $recordIDArray;
		}catch (Exception $e) {
			$error_msg 		 = $e->getMessage();
			if($error_msg =='No Content'){
				$returnResponse["status"]	= "error";
				$returnResponse["response"]	= "No Content";
			}else{
				$returnResponse["status"]	= "error";
				$returnResponse["response"]	= $error_msg;
				$strError = "[getRelatedRecords] Record ($module - $recordID)  ERROR: ".$e->getMessage()." ExceptionDetails ".json_encode($e->getExceptionDetails());
				$this->addLog($this->errorLog,$strError);
			}
		}
		return $returnResponse;
	}

	/* create log */
	public function addLog($path,$log){
		$myfile     = fopen($path, "a+") or die("Unable to open file!");
		$today_date = date("Y-m-d H:i:s");
		$final_log  = "[$today_date] $log\n";
		fwrite($myfile, $final_log);
		fclose($myfile);
	}

	public function addDataToFile($path,$data){
		$file = fopen($path, "a+") or die("Unable to open file!");
		if(!empty($data)){
			fwrite($file, $data);
		}
		fclose($file);
	}

}