<?php


class QBCustomerClass extends QBClass{

    public function customer_query_request($queueID,$isCon,$refID,$maxRecords,$getAll=1,$modDate=''){

        $xml = '<?xml version="1.0" encoding="utf-8"?>';
            $xml .= '<?qbxml version="13.0"?>';
                $xml .= '<QBXML>';
                    $xml .= '<QBXMLMsgsRq onError="stopOnError">';

                        if($isCon==1){
                            $xml .= '<CustomerQueryRq requestID="'.$queueID.'" iterator="Continue" iteratorID="'.$refID.'">';
                        }else{
                            $xml .= '<CustomerQueryRq requestID="'.$queueID.'" iterator="Start">';
                        }

                        $xml .= '<MaxReturned>'.$maxRecords.'</MaxReturned>';

                        if($getAll!=1){
                            $xml .= '<FromModifiedDate>'.$modDate.'</FromModifiedDate>';
                        }

                    $xml .= '<OwnerID>0</OwnerID>';

                    $xml .= '</CustomerQueryRq>';
                $xml .= '</QBXMLMsgsRq>';
            $xml .= '</QBXML>';

        return $xml;

    }

    public function customer_query_response($queueID,$companyID,$ticket,$responseData){

        $dbh = $this->coreDB();

        $xmlData = new SimpleXMLElement($responseData);
        $data = $xmlData->xpath('//CustomerRet');

        foreach($data as $i){

            $tc = new DateTime($i->TimeCreated);
            $tm = new DateTime($i->TimeModified);

            $tcUTC = $this->convertDateTimeUTC($i->TimeCreated);
            $tmUTC = $this->convertDateTimeUTC($i->TimeModified);


            $q = $dbh->prepare("INSERT INTO Customers(customerListID,
                                                      parentCustomerListID,
                                                      subLevelID,
                                                      customerName,
                                                      fullCustomerName,
                                                      companyName,
                                                      salutation,
                                                      firstName,
                                                      middleName,
                                                      lastName,
                                                      jobTitle,
                                                      address1,
                                                      address2,
                                                      address3,
                                                      address4,
                                                      address5,
                                                      city,
                                                      stateName,
                                                      zipCode,
                                                      country,
                                                      timeCreated,
                                                      timeCreatedUTC,
                                                      timeModified,
                                                      timeModifiedUTC,
                                                      createdOn,
                                                      modifiedOn)

                            VALUES (:customerListID,
                                    :parentCustomerListID,
                                    :subLevelID,
                                    :customerName,
                                    :fullCustomerName,
                                    :companyName,
                                    :salutation,
                                    :firstName,
                                    :middleName,
                                    :lastName,
                                    :jobTitle,
                                    :address1,
                                    :address2,
                                    :address3,
                                    :address4,
                                    :address5,
                                    :city,
                                    :stateName,
                                    :zipCode,
                                    :country,
                                    :timeCreated,
                                    :timeCreatedUTC,
                                    :timeModified,
                                    :timeModifiedUTC,
                                    CURRENT_TIMESTAMP,
                                    CURRENT_TIMESTAMP)

                                ON DUPLICATE KEY
                                    UPDATE

                                        parentCustomerListID = :parentCustomerListID,
                                        subLevelID = :subLevelID,
                                        customerName = :customerName,
                                        fullCustomerName = :fullCustomerName,
                                        companyName = :companyName,
                                        salutation = :salutation,
                                        firstName = :firstName,
                                        middleName = :middleName,
                                        lastName = :lastName,
                                        jobTitle = :jobTitle,
                                        address1 = :address1,
                                        address2 = :address2,
                                        address3 = :address3,
                                        address4 = :address4,
                                        address5 = :address5,
                                        city = :city,
                                        stateName = :stateName,
                                        zipCode = :zipCode,
                                        country = :country,
                                        timeCreated = :timeCreated,
                                        timeCreatedUTC = :timeCreatedUTC,
                                        timeModified = :timeModified,
                                        timeModifiedUTC = :timeModifiedUTC,
                                        modifiedOn = CURRENT_TIMESTAMP");


            $q->bindParam("customerListID",$i->ListID);
            $q->bindParam("parentCustomerListID",$i->ParentRef->ListID);
            $q->bindParam("subLevelID",$i->Sublevel);
            $q->bindParam("customerName",$i->Name);
            $q->bindParam("fullCustomerName",$i->FullName);
            $q->bindParam("companyName",$i->CompanyName);
            $q->bindParam("salutation",$i->Salutation);
            $q->bindParam("firstName",$i->FirstName);
            $q->bindParam("middleName",$i->MiddleName);
            $q->bindParam("lastName",$i->LastName);
            $q->bindParam("jobTitle",$i->JobTitle);
            $q->bindParam("address1",$i->BillAddress->Addr1);
            $q->bindParam("address2",$i->BillAddress->Addr2);
            $q->bindParam("address3",$i->BillAddress->Addr3);
            $q->bindParam("address4",$i->BillAddress->Addr4);
            $q->bindParam("address5",$i->BillAddress->Addr5);
            $q->bindParam("city",$i->BillAddress->City);
            $q->bindParam("stateName",$i->BillAddress->State);
            $q->bindParam("zipCode",$i->BillAddress->PostalCode);
            $q->bindParam("country",$i->BillAddress->Country);

            $q->bindParam("timeCreated",$tc->format('Y-m-d H:i:s'));
            $q->bindParam("timeCreatedUTC",$tcUTC->format('Y-m-d H:i:s'));
            $q->bindParam("timeModified",$tm->format('Y-m-d H:i:s'));
            $q->bindParam("timeModifiedUTC",$tmUTC->format('Y-m-d H:i:s'));

            $q->execute();

        }

        $dbh = null;

        $this->updateLastMod($queueID);

        $complete = '100';

        return $complete;

    }





}