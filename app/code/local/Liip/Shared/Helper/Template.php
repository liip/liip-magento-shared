<?php

class Liip_Shared_Helper_Template extends Mage_Core_Helper_Abstract
{
    /**
     * Load an e-mail template by code
     *
     * @param   string  $code   Template code
     * @return Mage_Core_Model_Email_Template
     */
    public function loadEmail($code, $fallback = null)
    {
        $template = Mage::getModel('core/email_template')->getCollection()
            ->addFieldToFilter('template_code', $code)->getFirstItem();

        if (!$template->getTemplateId()) {
            $template = Mage::getModel('core/email_template')->getCollection()
                ->addFieldToFilter('template_code', $fallback)->getFirstItem();
        }
        return $template;
    }
}
