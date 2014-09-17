<?php


class QBInvoiceClass extends QBClass{

    public function invoice_query_request($queueID,$isCon,$refID,$maxRecords,$getAll=1,$modDate=''){

        $xml = '<?xml version="1.0" encoding="utf-8"?>';
            $xml .= '<?qbxml version="13.0"?>';
                $xml .= '<QBXML>';
                    $xml .= '<QBXMLMsgsRq onError="stopOnError">';

                        if($isCon==1){
                            $xml .= '<InvoiceQueryRq requestID="'.$queueID.'" iterator="Continue" iteratorID="'.$refID.'">';
                        }else{
                            $xml .= '<InvoiceQueryRq requestID="'.$queueID.'" iterator="Start">';
                        }

                        $xml .= '<MaxReturned>'.$maxRecords.'</MaxReturned>';

                        if($getAll!=1){
                            $xml .= '<ModifiedDateRangeFilter>';
                                $xml .= '<FromModifiedDate>'.$modDate.'</FromModifiedDate>';
                            $xml .= '</ModifiedDateRangeFilter>';
                        }
                        $xml .= '<IncludeLineItems>1</IncludeLineItems>';
                    $xml .= '</InvoiceQueryRq>';
            $xml .= '</QBXMLMsgsRq>';
        $xml .= '</QBXML>';

        return $xml;

    }

    public function invoice_query_response($queueID,$companyID,$ticket,$responseData){

        $dbh = $this->coreDB();

        $xmlData = new SimpleXMLElement($responseData);
        $data = $xmlData->xpath('//InvoiceRet');


        foreach($data as $i){

            $isPaid = $this->convertTF($i->IsPaid);
            $isFinanceCharge = $this->convertTF($i->IsFinaceCharge);
            $isPending = $this->convertTF($i->IsPending);
            $isToBePrinted = $this->convertTF($i->IsToBePrinted);
            $isToBeEmailed = $this->convertTF($i->IsToBeEmailed);

            $tc = new DateTime($i->TimeCreated);
            $tm = new DateTime($i->TimeModified);

            $tcUTC = $this->convertDateTimeUTC($i->TimeCreated);
            $tmUTC = $this->convertDateTimeUTC($i->TimeModified);

            // Invoice Loop
            $q = $dbh->prepare("INSERT INTO Invoices(companyID,
                                                      invNo,
                                                      qbTxnID,
                                                      qbTxnNumber,
                                                      qbTxnDate,
                                                      qbTimeCreated,
                                                      qbTimeModified,
                                                      qbTimeCreatedUTC,
                                                      qbTimeModifiedUTC,
                                                      qbEditSequence,
                                                      qbCustomerListID,
                                                      qbCustomerFullName,
                                                      qbAccountListID,
                                                      qbBillAddress1,
                                                      qbBillAddress2,
                                                      qbBillCity,
                                                      qbBillState,
                                                      qbBillZip,
                                                      qbShipAddress1,
                                                      qbShipAddress2,
                                                      qbShipCity,
                                                      qbShipState,
                                                      qbShipZip,
                                                      qbDueDate,
                                                      qbShipDate,
                                                      qbSubtotal,
                                                      qbSalesTaxPercentage,
                                                      qbSalesTaxTotal,
                                                      qbBalanceRemaining,
                                                      qbAppliedAmount,
                                                      qbIsPaid,
                                                      qbIsFinanceCharge,
                                                      qbIsPending,
                                                      qbIsToBePrinted,
                                                      qbIsToBeEmailed,
                                                      qbTerms,
                                                      qbPoNumber,
                                                      qbFob,
                                                      qbCustomerMessage,
                                                      qbMemo,
                                                      qbOther,
                                                      qbSalesRep,
                                                      createdOn,
                                                      modifiedOn)

                                        VALUES(:companyID,
                                                :invNo,
                                                :txnID,
                                                :txnNumber,
                                                :txnDate,
                                                :timeCreated,
                                                :timeModified,
                                                :timeCreatedUTC,
                                                :timeModifiedUTC,
                                                :editSequence,
                                                :customerListID,
                                                :customerFullName,
                                                :accountListID,
                                                :billAddress1,
                                                :billAddress2,
                                                :billCity,
                                                :billState,
                                                :billZip,
                                                :shipAddress1,
                                                :shipAddress2,
                                                :shipCity,
                                                :shipState,
                                                :shipZip,
                                                :dueDate,
                                                :shipDate,
                                                :subtotal,
                                                :salesTaxPercentage,
                                                :salesTaxTotal,
                                                :balanceRemaining,
                                                :appliedAmount,
                                                :isPaid,
                                                :isFinanceCharge,
                                                :isPending,
                                                :isToBePrinted,
                                                :isToBeEmailed,
                                                :terms,
                                                :poNumber,
                                                :fob,
                                                :customerMessage,
                                                :memo,
                                                :other,
                                                :salesRep,
                                                CURRENT_TIMESTAMP,
                                                CURRENT_TIMESTAMP)

                                ON DUPLICATE KEY
                                UPDATE
                                                companyID = :companyID,
                                                invNo = :invNo,
                                                qbTxnNumber = :txnNumber,
                                                qbTxnDate = :txnDate,
                                                qbTimeCreated = :timeCreated,
                                                qbTimeModified = :timeModified,
                                                qbTimeCreatedUTC = :timeCreatedUTC,
                                                qbTimeModifiedUTC = :timeModifiedUTC,
                                                qbEditSequence = :editSequence,
                                                qbCustomerListID = :customerListID,
                                                qbCustomerFullName = :customerFullName,
                                                qbAccountListID = :accountListID,
                                                qbBillAddress1 = :billAddress1,
                                                qbBillAddress2 = :billAddress2,
                                                qbBillCity = :billCity,
                                                qbBillState = :billState,
                                                qbBillZip = :billZip,
                                                qbShipAddress1 = :shipAddress1,
                                                qbShipAddress2 = :shipAddress2,
                                                qbShipCity = :shipCity,
                                                qbShipState = :shipState,
                                                qbShipZip = :shipZip,
                                                qbDueDate = :dueDate,
                                                qbShipDate = :shipDate,
                                                qbSubtotal = :subtotal,
                                                qbSalesTaxPercentage = :salesTaxPercentage,
                                                qbSalesTaxTotal = :salesTaxTotal,
                                                qbBalanceRemaining = :balanceRemaining,
                                                qbAppliedAmount = :appliedAmount,
                                                qbIsPaid = :isPaid,
                                                qbIsFinanceCharge = :isFinanceCharge,
                                                qbIsPending = :isPending,
                                                qbIsToBePrinted = :isToBePrinted,
                                                qbIsToBeEmailed = :isToBeEmailed,
                                                qbTerms = :terms,
                                                qbPoNumber = :poNumber,
                                                qbFob = :fob,
                                                qbCustomerMessage = :customerMessage,
                                                qbMemo = :memo,
                                                qbOther = :other,
                                                qbSalesRep = :salesRep,
                                                modifiedOn = CURRENT_TIMESTAMP");


            $q->bindParam("companyID",$companyID);
            $q->bindParam("invNo",$i->RefNumber);
            $q->bindParam("txnID",$i->TxnID);
            $q->bindParam("txnNumber",$i->TxnNumber);
            $q->bindParam("txnDate",$i->TxnDate);
            $q->bindParam("timeCreated",$tc->format('Y-m-d H:i:s'));
            $q->bindParam("timeModified",$tm->format('Y-m-d H:i:s'));
            $q->bindParam("timeCreatedUTC",$tcUTC->format('Y-m-d H:i:s'));
            $q->bindParam("timeModifiedUTC",$tmUTC->format('Y-m-d H:i:s'));
            $q->bindParam("editSequence",$i->EditSequence);

            $q->bindParam("customerListID",$i->CustomerRef->ListID);
            $q->bindParam("customerFullName",$i->CustomerRef->FullName);
            $q->bindParam("accountListID",$i->ARAccountRef->ListID);
            $q->bindParam("billAddress1",$i->BillAddress->Addr1);
            $q->bindParam("billAddress2",$i->BillAddress->Addr2);
            $q->bindParam("billCity",$i->BillAddress->City);
            $q->bindParam("billState",$i->BillAddress->State);
            $q->bindParam("billZip",$i->BillAddress->PostalCode);

            $q->bindParam("shipAddress1",$i->ShipAddress->Addr1);
            $q->bindParam("shipAddress2",$i->ShipAddress->Addr2);
            $q->bindParam("shipCity",$i->ShipAddress->City);
            $q->bindParam("shipState",$i->ShipAddress->State);
            $q->bindParam("shipZip",$i->ShipAddress->PostalCode);

            $q->bindParam("dueDate",$i->DueDate);
            $q->bindParam("shipDate",$i->ShipDate);
            $q->bindParam("subtotal",$i->Subtotal);
            $q->bindParam("salesTaxPercentage",$i->SalesTaxPercentage);
            $q->bindParam("salesTaxTotal",$i->SalesTaxTotal);
            $q->bindParam("balanceRemaining",$i->BalanceRemaining);
            $q->bindParam("appliedAmount",$i->AppliedAmount);
            $q->bindParam("isPaid",$isPaid);
            $q->bindParam("isFinanceCharge",$isFinanceCharge);
            $q->bindParam("isPending",$isPending);
            $q->bindParam("isToBePrinted",$isToBePrinted);
            $q->bindParam("isToBeEmailed",$isToBeEmailed);

            $q->bindParam("terms",$i->TermsRef->FullName);
            $q->bindParam("poNumber",$i->PONumber);

            $q->bindParam("fob",$i->FOBNumber);
            $q->bindParam("customerMessage",$i->CustomerMsgRef->FullName);
            $q->bindParam("memo",$i->Memo);
            $q->bindParam("other",$i->Other);
            $q->bindParam("salesRep",$i->SalesRepRef->FullName);

            $q->execute();

            $invoiceLineID = $dbh->lastInsertId();

            $err = $q->errorInfo();

            // Invoice Line Loop
            foreach($i->InvoiceLineRet as $line){

                $q2 = $dbh->prepare("INSERT INTO Invoice_Lines(txnLineID,
                                                                invoiceID,
                                                                txnID,
                                                                itemListID,
                                                                itemName,
                                                                itemDescription,
                                                                quantity,
                                                                rate,
                                                                amount,
                                                                salesTaxCodeListID,
                                                                salesTaxName,
                                                                serviceDate,
                                                                other1,
                                                                other2)

                                                            VALUES(:txnLineID,
                                                                    :invoiceID,
                                                                    :txnID,
                                                                    :itemListID,
                                                                    :itemName,
                                                                    :itemDescription,
                                                                    :quantity,
                                                                    :rate,
                                                                    :amount,
                                                                    :salesTaxCodeListID,
                                                                    :salesTaxName,
                                                                    :serviceDate,
                                                                    :other1,
                                                                    :other2)


                                                        ON DUPLICATE KEY
                                                        UPDATE
                                                            invoiceID = :invoiceID,
                                                            txnID = :txnID,
                                                            itemListID = :itemListID,
                                                            itemName = :itemName,
                                                            itemDescription = :itemDescription,
                                                            quantity = :quantity,
                                                            rate = :rate,
                                                            amount = :amount,
                                                            salesTaxCodeListID = :salesTaxCodeListID,
                                                            salesTaxName = :salesTaxName,
                                                            serviceDate = :serviceDate,
                                                            other1 = :other1,
                                                            other2 = :other2");

                $q2->bindParam("txnLineID",$line->TxnLineID);
                $q2->bindParam("invoiceID",$invoiceLineID);
                $q2->bindParam("txnID",$i->TxnID);
                $q2->bindParam("itemListID",$line->ItemRef->ListID);
                $q2->bindParam("itemName",$line->ItemRef->FullName);
                $q2->bindParam("itemDescription",$line->Desc);
                $q2->bindParam("quantity",$line->Quantity);
                $q2->bindParam("rate",$line->Rate);
                $q2->bindParam("amount",$line->Amount);
                $q2->bindParam("salesTaxCodeListID",$line->SalesTaxCodeRef->ListID);
                $q2->bindParam("salesTaxName",$line->SalesTaxCodeRef->FullName);

                $q2->bindParam("serviceDate",$line->ServiceDate);
                $q2->bindParam("other1",$line->Other1);
                $q2->bindParam("other2",$line->Other2);

                $q2->execute();

            } // End Line Loop

        } // End Invoice Loop

        $this->updateLastMod($queueID);

        $complete = '100';

        $dbh = null;

        return $complete;

    }


}