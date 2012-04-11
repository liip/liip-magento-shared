<?php

class Liip_Shared_Helper_Date extends Mage_Core_Helper_Abstract
{
    /**
     * Formats a date according to the respective locale
     *
     * @param   string  The date to format
     * @param   int     The format type
     * @return  string  The formatted date
     */
    public function formatLocaleDate($date, $formatType = Mage_Core_Model_Locale::FORMAT_TYPE_SHORT)
    {
        if (!$date) {
            return $date;
        }

        $locale = Mage::app()->getLocale();
        return $locale->date(strtotime($date), Zend_Date::TIMESTAMP)->toString($locale->getDateFormat($formatType));
    }
}

