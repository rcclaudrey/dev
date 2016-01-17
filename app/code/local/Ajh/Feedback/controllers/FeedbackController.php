<?php

/**
 * Created by PhpStorm.
 * User: Camille
 * Date: 2/19/15
 * Time: 10:40 PM
 */
class Ajh_Feedback_FeedbackController extends Mage_Core_Controller_Front_Action {

    public function submitAction() {
        $receiverEmail = 'pleonard@temeculamotorsports.com';
        $receiverName = 'TMSParts';

        $senderEmail = $this->getRequest()->getPost('email')?$this->getRequest()->getPost('email'):'customer@tmsparts.com';
        $feedback = $this->getRequest()->getPost('feedback');        

        $body = "<div><strong>Email:</strong> {$senderEmail}</div><br/>";
        $body .= "<div><strong>Feedback:</strong> {$feedback}</div>";


        //Sending E-Mail to Customers.
        $mail = Mage::getModel('core/email')
                ->setToName($receiverName)
                ->setToEmail($receiverEmail)
                ->setBody($body)
                ->setSubject('TMSParts Customer Feedback')
                ->setFromEmail($senderEmail)
                ->setFromName('TMSParts Customer')
                ->setType('html');
        try {
            //Confimation E-Mail Send
            $mail->send();
        } catch (Exception $error) {
            print_r($error->getMessage());
            Mage::getSingleton('core/session')->addError($error->getMessage());
            return false;
        }                

        die;
    }

}
