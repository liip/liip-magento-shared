<?php

class Liip_Shared_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Changes a store config and reloads it to reflect the change immediately
     *
     * @param   string  $path   Configuration key
     * @param   string  $value  New value
     * @param   Mage_Core_Model_Website $website
     * @param   Mage_Core_Model_Store   $store
     */
    public function setStoreConfig($path, $value, Mage_Core_Model_Website $website = null, Mage_Core_Model_Store $store = null)
    {
        $parts = explode('/', $path);

        if (count($parts) != 3) {
            throw new InvalidArgumentException('Invalid path `'.$path.'\' for config');
        }

        $groups = array();
        $groups[$parts[1]]['fields'][$parts[2]]['value'] = $value;

        Mage::getModel('adminhtml/config_data')
            ->setSection($parts[0])
            ->setWebsite($website)
            ->setStore($store)
            ->setGroups($groups)
            ->save();

        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();
    }

    public function translateWildcardsToSql($text)
    {
        return strtr($text, array('*' => '%', '?' => '_'));
    }

    /**
     * Formats a date according to the respective locale
     *
     * Acts the same as `Mage::helper('core')->formatDate()` except that we detect
     * the unsupported format "dd.mm.yy" and fix it accordingly.
     *
     * @param   string  The date to format
     * @param   string  The format type (full, short, long, medium)
     * @return  string  The formatted date
     */
    public function formatDate($date, $formatType = Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM)
    {
        if (!$date) {
            return $date;
        }

        // when the year only is two digits, strtotime() fails.
        // strtotime('31.3.12') -> returns today's date
        if (preg_match('/^(\d{1,2}\.\d{1,2})\.(\d{2})$/', $date, $matches)) {
            $date = $matches[1].'.20'.$matches[2];
        }

        return Mage::helper('core')->formatDate($date, $formatType);
    }

    /**
     * Check if current date is withingProduct can be ordered.
     *
     * @param   string $from Datetime
     * @param   string $to Datetime
     * @param   string $date Current date or null for now
     * @return true or false
     */
    public function isDateInRange($from = false, $to = false, $date = null)
    {
        if (!$from && !$to) {
            return true;
        }
        if (!$date) {
            $date = Mage::getModel('core/date')->timestamp();
        }
        if ($from) {
            $from = strtotime($from);
        }
        if ($to) {
            $to = strtotime($to);
        }
        return ($from && !$to && $from <= $date) ||
            (!$from && $to && $to >= $date) ||
            ($from && $to && $from <= $date && $to >= $date);
    }

    public function getNightsBetween($date1, $date2)
    {
        $date1 = ceil(strtotime($date1)/86400);
        $date2 = ceil(strtotime($date2)/86400);
        return abs($date1 - $date2);
    }

    /**
     * Uses the google maps geocoding API
     *
     * @return  [lat, lng, 'latitude' => lat, 'longitude' => lng]
     * @see https://developers.google.com/maps/documentation/geocoding/
     */
    public function fetchGeolocation($place)
    {
        $url = 'http://maps.google.com/maps/geo?output=xml&q='.urlencode($place);
        return $this->extractGeolocation(file_get_contents($url));
    }

    protected function extractGeolocation($xml)
    {
        $xml = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOERROR);
        if ($xml === false || !isset($xml->Response->Placemark->Point->coordinates)) {
            return false;
        }

        $cords = (string)$xml->Response->Placemark->Point->coordinates[0];
        $coordinates = explode(',', $cords);
        return array(0 => $coordinates[1], 1 => $coordinates[0], 'latitude' => $coordinates[1], 'longitude' => $coordinates[0]);
    }
}

