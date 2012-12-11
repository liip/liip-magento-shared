<?php


class Liip_Shared_Helper_Mail extends Mage_Core_Helper_Abstract
{
    /**
     * Mimicks Magento's behaviour for setting the return header
     * @see Mage_Core_Model_Email_Template::send()
     */
    public function forceReturnPath($senderEmail = null)
    {
        $setReturnPath = Mage::getStoreConfig(Mage_Core_Model_Email_Template::XML_PATH_SENDING_SET_RETURN_PATH);
        switch ($setReturnPath) {
            case 1:
                $returnPathEmail = $senderEmail;
                break;
            case 2:
                $returnPathEmail = Mage::getStoreConfig(Mage_Core_Model_Email_Template::XML_PATH_SENDING_RETURN_PATH_EMAIL);
                break;
            default:
                $returnPathEmail = null;
                break;
        }

        if ($returnPathEmail !== null) {
            $mailTransport = new Zend_Mail_Transport_Sendmail('-f'.$returnPathEmail);
            Zend_Mail::setDefaultTransport($mailTransport);
        }
    }
}

