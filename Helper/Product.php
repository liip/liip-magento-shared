<?php

class Liip_Shared_Helper_Product extends Mage_Core_Helper_Abstract
{
    /**
     * Load a product by SKU.
     * 
     * @param string $sku SKU
     * @return Mage_Catalog_Model_Product the product model
     */
    public function load($sku)
    {
        $product = Mage::getModel('catalog/product');
        $product->load($product->getIdBySku($sku));
        return $product;
    }
    
    /**
     * Check if Product can be ordered.
     * 
     * @param string $from Datetime
     * @param string $to Datetime
     * @return true or false
     */
    public function isOrderingActive($from = false, $to = false)
    {
    	$today = strtotime(date('Y-m-d H:i:s'));
    	
    	if(!$from && !$to) return true; 
    	
    	if($from) $from = strtotime($from);
    	if($to) $to = strtotime($to);
    	    	
        if($from && !$to && $from <= $today) return true;
        elseif(!$from && $to && $to >= $today) return true;
        elseif($from && $to && $from <= $today && $to >= $today) return true;
        else return false;
    }
}
