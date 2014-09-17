<?php

class QBClass extends CoreClass{


    public function __construct()
    {


    }

    // :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // :::::::::::::::::::::::::::::::::::::::::::::::: Ticket Functions ::::::::::::::::::::::::::::::::::::::::::::::::
    // :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

    public function createTicket($obj){

        $dbh = $this->coreDB();

        $auth_q = $dbh->prepare("SELECT *
                                        FROM QB_Users
                                        WHERE qbUsername = :username AND qbPassword = :password AND statusID = '1'");

        $auth_q->bindParam("username",$obj->strUserName);
        $auth_q->bindParam("password",$obj->strPassword);

        $auth_q->execute();
        $auth_d = $auth_q->fetchAll(PDO::FETCH_ASSOC);


        if(!empty($auth_d)){

            $userID = $auth_d["0"]["qbUserID"];
            $companyID = $auth_d["0"]["companyID"];

            $q = $dbh->prepare("INSERT INTO QB_Tickets(qbUserID,companyID,statusID,createdOn)
                                        VALUES(:userID,:companyID,'1',CURRENT_TIMESTAMP)");

            $q->bindParam("userID",$userID);
            $q->bindParam("companyID",$companyID);

            $q->execute();

            $id = $dbh->lastInsertId();

        }else{

            $id = '0';
            $companyID = '0';

        }

        $data = array("ticketID"=>$id,"companyID"=>$companyID);

        $dbh = null;

        return $data;

    }


    public function getTicket($obj){

        $dbh = $this->coreDB();

        // Get ticketID
        $ticketID = $obj->ticket;

        $q = $dbh->prepare("SELECT *
                                    FROM QB_Tickets
                                    WHERE qbTicketID = :ticketID");

        $q->bindParam("ticketID",$ticketID);
        $q->execute();

        $d = $q->fetchAll(PDO::FETCH_ASSOC);

        if(!empty($d)){

            $companyID = $d["0"]["companyID"];

        }else{

            $companyID = '0';

        }

        $data = array('companyID'=>$companyID,'ticketID'=>$ticketID);

        $dbh = null;

        return $data;

    }


    public function createTicketDetail($obj){

        $dbh = $this->coreDB();

        $ticketID = $obj->ticket;
        $companyFile =  $obj->strCompanyFileName;
        $qbXMLCountry = $obj->qbXMLCountry;
        $qbXMLMajorVersion = $obj->qbXMLMajorVers;
        $qbXMLMinorVersion = $obj->qbXMLMinorVers;

        $responseData = $obj->strHCPResponse;

        // If con do not update ticket details again
        if(!empty($responseData)){


            $xmlData = new SimpleXMLElement($responseData);

            $hostData = $xmlData->xpath('//HostRet');
            $companyData = $xmlData->xpath('//CompanyRet');

            $q = $dbh->prepare("INSERT INTO QB_Ticket_Details(qbTicketID,
                                                                  qbProductName,
                                                                  qbMajorVersion,
                                                                  qbMinorVersion,
                                                                  qbCompanyName,
                                                                  qbCompanyFile,
                                                                  qbXMLCountry,
                                                                  qbXMLMajorVersion,
                                                                  qbXMLMinorVersion,
                                                                  createdOn)

                                          VALUES(:ticketID,
                                                  :productName,
                                                  :majorVersion,
                                                  :minorVersion,
                                                  :companyName,
                                                  :companyFile,
                                                  :qbXMLCountry,
                                                  :qbXMLMajorVersion,
                                                  :qbXMLMinorVersion,
                                                  current_timestamp)");

            $q->bindParam("ticketID",$ticketID);
            $q->bindParam("productName",$hostData[0]->ProductName);
            $q->bindParam("majorVersion",$hostData[0]->MajorVersion);
            $q->bindParam("minorVersion",$hostData[0]->MinorVersion);
            $q->bindParam("companyName",$companyData[0]->CompanyName);
            $q->bindParam("companyFile",$companyFile);
            $q->bindParam("qbXMLCountry",$qbXMLCountry);
            $q->bindParam("qbXMLMajorVersion",$qbXMLMajorVersion);
            $q->bindParam("qbXMLMinorVersion",$qbXMLMinorVersion);

            $q->execute();

            $dbh = null;

        }

    }


    // :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    // :::::::::::::::::::::::::::::::::::::::::::::::: Queue Functions ::::::::::::::::::::::::::::::::::::::::::::::::
    // :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::


    // ---------------------------------------- Get Queue ----------------------------------------

    public function getActiveQueue($companyID){

        $dbh = $this->coreDB();

        $q = "SELECT *
                FROM QB_Queue as q
                JOIN QB_Actions as a on q.qbActionID = a.qbActionID
                WHERE companyID = :companyID AND (statusID = '1' OR statusID = '2')
                ORDER BY priorityID";

        $query = $dbh->prepare($q);

        $query->bindParam("companyID",$companyID);

        $query->execute();
        $d = $query->fetchAll(PDO::FETCH_ASSOC);

        if(!empty($d)){

            $queueID = $d["0"]["qbQueueID"];
            $action = $d["0"]["qbAction"];
            $class = $d["0"]["qbClass"];
            $request = $d["0"]["qbRequest"];
            $response = $d["0"]["qbResponse"];
            $refID = $d["0"]["refID"];
            $isCon = $d["0"]["isCon"];
            $maxRecords = $d["0"]["maxRecords"];
            $getAll = $d["0"]["getAll"];
            $modDate = $d["0"]["modDate"];


        }else{

            $queueID = '0';
            $action = '';
            $class = '';
            $request = '';
            $response = '';
            $refID = '';
            $isCon = '';
            $maxRecords = '';
            $getAll = '';
            $modDate = '';

        }

        $data = array("queueID"=>$queueID,
            "companyID"=>$companyID,
            "action"=>$action,
            "class"=>$class,
            "request"=>$request,
            "response"=>$response,
            "refID"=>$refID,
            "isCon"=>$isCon,
            "maxRecords"=>$maxRecords,
            "getAll"=>$getAll,
            "modDate"=>$modDate);

        $dbh = null;

        return $data;

    }

    // ---------------------------------------- Get Queue ----------------------------------------

    public function getQueueByID($queueID){

        $dbh = $this->coreDB();

        $q = "SELECT *
                FROM QB_Queue as q
                JOIN QB_Actions as a on q.qbActionID = a.qbActionID
                WHERE qbQueueID = :queueID
                ORDER BY priorityID";

        $query = $dbh->prepare($q);

        $query->bindParam("queueID",$queueID);

        $query->execute();
        $d = $query->fetchAll(PDO::FETCH_ASSOC);

        if(!empty($d)){

            $queueID = $d["0"]["qbQueueID"];
            $companyID = $d["0"]["companyID"];
            $action = $d["0"]["qbAction"];
            $actionID = $d["0"]["qbActionID"];
            $class = $d["0"]["qbClass"];
            $request = $d["0"]["qbRequest"];
            $response = $d["0"]["qbResponse"];
            $refID = $d["0"]["refID"];
            $isCon = $d["0"]["isCon"];
            $maxRecords = $d["0"]["maxRecords"];
            $getAll = $d["0"]["getAll"];
            $modDate = $d["0"]["modDate"];


        }else{

            $queueID = '0';
            $companyID = '';
            $action = '';
            $actionID = '';
            $class = '';
            $request = '';
            $response = '';
            $refID = '';
            $isCon = '';
            $maxRecords = '';
            $getAll = '';
            $modDate = '';

        }

        $data = array("queueID"=>$queueID,
            "companyID"=>$companyID,
            "action"=>$action,
            "actionID"=>$actionID,
            "class"=>$class,
            "request"=>$request,
            "response"=>$response,
            "refID"=>$refID,
            "isCon"=>$isCon,
            "maxRecords"=>$maxRecords,
            "getAll"=>$getAll,
            "modDate"=>$modDate);

        $dbh = null;

        return $data;

    }



    // ---------------------------------------- Update Queue TicketID and Status ----------------------------------------

    public function updateQueueTicketStatus($queueID,$ticketID,$statusID){

        $dbh = $this->coreDB();

        $q = $dbh->prepare("UPDATE QB_Queue
                                    SET
                                      qbTicketID = :ticketID,
                                      statusID = :statusID,
                                      modifiedOn = CURRENT_TIMESTAMP
                                    WHERE qbQueueID = :queueID");

        $q->bindParam("ticketID",$ticketID);
        $q->bindParam("statusID",$statusID);
        $q->bindParam("queueID",$queueID);

        $q->execute();

        $dbh = null;

        return;

    }

    // ---------------------------------------- Update Queue Status & Process Iterators ----------------------------------------

    public function updateQueue($companyID,$ticketID,$queueID,$statusID,$iRemaining=0,$iID=0){

        $dbh = $this->coreDB();

        // Re-queue iterators
        if($iRemaining>0){

            // Get existing queue data
            $queueData = $this->getQueueByID($queueID);

            // Set queue variables
            $actionID = $queueData["actionID"];
            $getAll = $queueData["getAll"];
            $modDate = $queueData["modDate"];
            $maxRecords = $queueData["maxRecords"];

            // Queue up more
            $this->addQueue($companyID,$ticketID,$actionID,$maxRecords,'1','0',$iID,$getAll,$modDate,'1');

        }

        // Update queue status
        $q = $dbh->prepare("UPDATE QB_Queue
                                    SET
                                      statusID = :statusID,
                                      modifiedOn = CURRENT_TIMESTAMP
                                    WHERE qbTicketID = :ticketID AND qbQueueID = :queueID");

        $q->bindParam("statusID",$statusID);
        $q->bindParam("ticketID",$ticketID);
        $q->bindParam("queueID",$queueID);

        $q->execute();

        $dbh = null;

        return;
    }


    // ---------------------------------------- Get Action ID ----------------------------------------

    public  function getActionID($action){

        $dbh = $this->coreDB();

        $q = $dbh->prepare("SELECT qbActionID FROM QB_Actions WHERE qbAction = :qbAction");

        $q->bindParam("qbAction",$action);

        $q->execute();

        $d = $q->fetchColumn();

        $dbh = null;

        return $d;

    }

    // ---------------------------------------- Get Action By TicketID ----------------------------------------

    public  function getActionByTicketID($ticketID){

        $dbh = $this->coreDB();

        $q = $dbh->prepare("SELECT qbActionID FROM QB_Actions WHERE qbAction = :qbAction");

        $q->bindParam("qbAction",$action);

        $q->execute();

        $d = $q->fetchColumn();

        $dbh = null;

        return $d;

    }

    // ---------------------------------------- Add to queue ----------------------------------------

    public  function addQueue($companyID,$ticketID,$actionID,$maxRecords,$isCon,$priorityID,$refID=0,$getAll,$modDate,$statusID=1){

        $dbh = $this->coreDB();

        $q = $dbh->prepare("INSERT INTO QB_Queue(companyID,
                                                  qbActionID,
                                                  qbTicketID,
                                                  refID,
                                                  isCon,
                                                  statusID,
                                                  priorityID,
                                                  maxRecords,
                                                  getAll,
                                                  modDate,
                                                  createdOn,
                                                  modifiedOn)

                                    VALUES(:companyID,
                                            :actionID,
                                            :ticketID,
                                            :refID,
                                            :isCon,
                                            :statusID,
                                            :priorityID,
                                            :maxRecords,
                                            :getAll,
                                            :modDate,
                                            CURRENT_TIMESTAMP,
                                            CURRENT_TIMESTAMP)");

        $q->bindParam("companyID",$companyID);
        $q->bindParam("ticketID",$ticketID);
        $q->bindParam("actionID",$actionID);
        $q->bindParam("refID",$refID);
        $q->bindParam("isCon",$isCon);
        $q->bindParam("statusID",$statusID);
        $q->bindParam("priorityID",$priorityID);
        $q->bindParam("maxRecords",$maxRecords);
        $q->bindParam("getAll",$getAll);
        $q->bindParam("modDate",$modDate);

        $q->execute();

        $dbh = null;

        return;

    }




    public function updateLastMod($queueID){

        $dbh = $this->coreDB();

        $queueData = $this->getQueueByID($queueID);
        $companyID = $queueData["companyID"];
        $actionID = $queueData["actionID"];

        $q = $dbh->prepare("INSERT INTO LastMod(companyID,qbActionID,lastMod)
                                VALUES(:companyID,:actionID, NOW())
                                ON DUPLICATE KEY UPDATE
                                LastMod = NOW()");

        $q->bindParam("companyID",$companyID);
        $q->bindParam("actionID",$actionID);

        $q->execute();

        $err = $q->errorInfo();


    }


    public function getLastModXML($mod){

        date_default_timezone_set("America/New_York");
        $isDST = date("I");

        $m1 = new DateTime($mod);

        $m1->sub(new DateInterval('PT15M'));

        if($isDST){
            $m1->add(new DateInterval('PT1H'));
        };

        $mod_d1 = $m1->format('Y-m-d');
        $mod_t1 = $m1->format('H:i:s');
        $mod1 = $mod_d1 . 'T' . $mod_t1 .'-00:00';

        return $mod1;


    }


    public function convertDateTimeUTC($dt){

        date_default_timezone_set("America/New_York");
        $isDST = date("I");

        $utc = new DateTimeZone("UTC");

        $dtUTC = new DateTime($dt);
        $dtUTC->setTimezone($utc);

        if($isDST){
            $dtUTC->sub(new DateInterval('PT1H'));
        }

        return $dtUTC;

    }


    public function convertTF($val){

        if($val=='true'){
            $bit = '1';
        }else{
            $bit = '0';
        }

        return $bit;
    }


    // ---------------------------------------- Create Log File ----------------------------------------

    public function logFile($fileName,$logData,$type='a'){

        $log = fopen('/logs/'.$fileName.'.log', ''.$type.'+');
        fwrite($log, print_r($logData, TRUE));
        fclose($log);

    }

}


?>