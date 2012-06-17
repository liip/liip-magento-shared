<?php

class Liip_Shared_Model_Core_Store extends Mage_Core_Model_Store
{
    /**
     * Round price
     *
     * @param mixed $price
     * @return double
     */
    public function roundPrice($price) {

        $price = round(($price + 0.000001) * 20) / 20;

        return round($price, 2);
    }
}
