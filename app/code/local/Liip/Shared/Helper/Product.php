<?php

class Liip_Shared_Helper_Product extends Mage_Core_Helper_Abstract
{
    /**
     * @return  int     The status of the product (in the admin store)
     */
    public function getStatus($productId)
    {
        $statuses = Mage::getSingleton('catalog/product_status')->getProductStatus($productId, Mage_Core_Model_App::ADMIN_STORE_ID);
        return reset($statuses);
    }

    /**
     * @param   array|int     $productIds
     * @param   int           $status         Mage_Catalog_Model_Product_Status::STATUS_*
     */
    public function updateStatus($productIds, $status)
    {
        $this->updateAttribute($productIds, 'status', $status);
    }

    /**
     * Updates an attribute of a product in admin store without touching any view settings
     * and no event dispatching
     *
     * In particular, it does not uncheck the 'Use Default Value' checkboxes and it does not
     * care which store is currently active
     * @param   array|int   $productIds     The ids of products to update, either an array or a single id
     * @param   string      $attribute      The attribute to change
     * @param   mixed       $value          The value to set the attribute to
     */
    public function updateAttribute($productIds, $attribute, $value)
    {
        Mage::getSingleton('catalog/product_action')->updateAttributes((array)$productIds, array($attribute => $value), Mage_Core_Model_App::ADMIN_STORE_ID);
    }

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
        // New url rewrites with enterprise 1.13
        $magentoVersion = Mage::getVersion();
        if (version_compare($magentoVersion, '1.13', '>=')) {
            $requestPath = $product->getUrlKey();
            if ($requestPath) {
                return Mage::getUrl('', array('_direct' => $requestPath));
            }
        }

        $rewrite = null;
        if ($categories = $product->getCategoryIds()) {
            $rewrite = Mage::getModel('catalog/url')->getResource()->getRewriteByIdPath('product/' . $product->getId() . '/' . reset($categories), 1);
        }
        if (!$rewrite) {
            $rewrite = Mage::getModel('catalog/url')->getResource()->getRewriteByIdPath('product/' . $product->getId(), 1);
        }
        return Mage::getUrl('', array('_direct' => $rewrite->getRequestPath()));
    }
    
    /**
     * @param array $productIds
     */
    public function removeCategoryRelations(array $productIds)
    {
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('write');

        $connection->delete(
            $resource->getTableName('catalog/category_product'),
            array('product_id IN(?)' => $productIds)
        );

        /** @var Mage_Index_Model_Indexer $indexer */
        $indexer = Mage::getSingleton('index/indexer');
        $categoryProductIndexer = $indexer->getProcessByCode('catalog_category_product');
        $categoryProductIndexer->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
    }
}
