<?php

class QBServerClass extends QBClass{

    public function __construct()
    {

    }

    function serverVersion($obj){

    }


    function clientVersion($obj){

    }




//    --------------- Authenticate ---------------

    function authenticate($obj)
    {

        // Create ticket and return ticket data
        $ticketData = $this->createTicket($obj);

        // Set ticketID and companyID
        $ticketID = $ticketData["ticketID"];
        $companyID = $ticketData["companyID"];

        // if valid ticket
        if($ticketID!=0){

            // Get Queue Data
            $queueData = $this->getActiveQueue($companyID);

            // Set queueID
            $queueID = $queueData["queueID"];

            // If data in queue for company
            if($queueID!=0){

                // Update Queue TicketID and StatusID
                $statusID = '2';
                $this->updateQueueTicketStatus($queueID,$ticketID,$statusID);

                $status = '';
                $waitRun = '';
                $minRun = '';

            }else{ // if no data in queue for company

                $ticketID = '';
                $status = 'none';
                $waitRun = '';
                $minRun = '';

            }

        }else{ // If ticketID = 0

            $ticketID = '';
            $status = 'nvu';
            $waitRun = '';
            $minRun = '';
        }

        // Set Auth Return
        $auth = new QBWCAuthClass($ticketID,$status,$waitRun,$minRun);

        return $auth;

    }

//    --------------- Send Request ---------------

    function sendRequestXML($obj)
    {

        // Insert Ticket Details
        $this->createTicketDetail($obj);

        // Set ticket data
        $ticketData = $this->getTicket($obj);

        // Set ticket variables
        $companyID =  $ticketData["companyID"];
        $ticketID = $ticketData["ticketID"];

        // Set queue data
        $queueData = $this->getActiveQueue($companyID);

        // Get class
        $c = $queueData["class"];

        // Get queue variables
        $req = $queueData["request"];
        $queueID = $queueData["queueID"];
        $isCon = $queueData["isCon"];
        $refID = $queueData["refID"];
        $maxRecords = $queueData["maxRecords"];
        $getAll = $queueData["getAll"];
        $modDate = $this->getLastModXML($queueData["modDate"]);

        // Init class
        $class = new $c();

        // Call request
        $reqCall = $class->$req($queueID,$isCon,$refID,$maxRecords,$getAll,$modDate);

        // Set request
        $request = new QBWRequestClass($reqCall);

        // Update queue statusID
        $this->updateQueue($companyID,$ticketID,$queueID,'3');

        return $request;

    }

//    --------------- Receive Response ---------------

    function receiveResponseXML($obj)
    {

        // Set response data
        $responseData = $obj->response;

        // Convert to Array
        $xmlData = new SimpleXMLElement($responseData);

        // Set Header Data
        $headerData = $xmlData->children()->children()->attributes();

        // Set Header Variables
        $iRemaining = $headerData["iteratorRemainingCount"];
        $iID= $headerData["iteratorID"];
        $queueID = $headerData["requestID"];

        // Get ticket data
        $ticketData = $this->getTicket($obj);

        // Set ticket variables
        $companyID =  $ticketData["companyID"];
        $ticketID = $ticketData["ticketID"];

        // Get queue data
        $queueData = $this->getQueueByID($queueID);

        // Get Class
        $c = $queueData["class"];

        // Set Response Action
        $res = $queueData["response"];

        // Init Response Class
        $class = new $c();

        // Call Response Action
        //$class->$res($queueID,$companyID,$ticketID,$responseData);


        $this->logFile('customersss',$responseData);

        // Update queue statusID & process iterators
        $this->updateQueue($companyID,$ticketID,$queueID,'4',$iRemaining,$iID);

        if($iRemaining>0){

            // Set response
            $response = new QBWResponseClass('50');

        }else{

            // Check for more data in queue
            $queueDataMore = $this->getActiveQueue($companyID);
            $queueIDMore = $queueDataMore["queueID"];

            if($queueIDMore==0){

                // Set response
                $response = new QBWResponseClass('100');

            }else{

                // Set response
                $response = new QBWResponseClass('50');

                // Update queue with ticketID and change status
                $this->updateQueueTicketStatus($queueIDMore,$ticketID,'2');
            }

        }

        return $response;

    }

//    --------------- Close Connection ---------------

    function closeConnection($obj)
    {

        $close = new QBWRequestClass($obj);

        return $close;

    }

//    --------------- Get Last Error ---------------

    function getLastError($obj){

        $this->logFile("err",$obj);

    }

}



