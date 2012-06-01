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
     * @param Mage_Catalog_Model_Product $product the product model
     * @return string URL
     */
    public function getUrl($product)
    {
        $rewrite = null;
        if ($categories = $product->getCategoryIds()) {
            $rewrite = Mage::getModel('catalog/url')->getResource()->getRewriteByIdPath('product/' . $product->getId() . '/' . reset($categories), 1);
        }
        if (!$rewrite) {
            $rewrite = Mage::getModel('catalog/url')->getResource()->getRewriteByIdPath('product/' . $product->getId(), 1);
        }
        return Mage::getUrl('', array('_direct' => $rewrite->getRequestPath()));
    }
}
