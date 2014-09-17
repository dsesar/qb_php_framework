<?php

class QBWCAuthClass extends QBClass{

    public $authenticateResult;

    public function __construct($ticket,$status,$waitRun,$minRun)
    {

        $this->authenticateResult = array($ticket,$status,$waitRun,$minRun);

    }

}

class QBWRequestClass extends QBClass{

    public $sendRequestXMLResult;

    public function __construct($xml)
    {
        $this->sendRequestXMLResult = $xml;

    }

}

class QBWResponseClass extends QBClass{

    public $receiveResponseXMLResult;

    public function __construct($complete)
    {

        $this->receiveResponseXMLResult = $complete;
    }

}




?>
