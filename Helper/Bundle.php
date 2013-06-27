<?php

class Liip_Shared_Helper_Bundle extends Mage_Core_Helper_Abstract
{
    /**
     * Calculates the price of a bundle with the given selection.
     *
     * @param   object|int      The product
     * @param   array           Bundle item selection
     * @return  double|false    The price otherwise FALSE if a selection couldn't be found
     */
    public function getPrice($product, $bundleOptions, $extra = array())
    {
        if (!$product instanceof Mage_Catalog_Model_Product) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        $options = Mage::helper('daytrips/cart')->getOptions($product);


        // if user passed all params
        if (isset($bundleOptions['bundle_option'])) {
            $bundleOption = $bundleOptions['bundle_option'];
        }

        $price = 0;
        $priceModel = $product->getPriceModel();

        foreach ($bundleOption as $optionId => $selectionIds) {
            $selectionIds = (array)$selectionIds;

            foreach ($selectionIds as $selectionId) {
                $selection = $options[$optionId]->getSelectionById($selectionId);
                if ($selection) {
                    $price += $this->calculateProductPrice($selection, $extra);
                } else {
                    return false;
                }
            }
        }
        return $price;
    }

    protected function calculateProductPrice($product, $extra = array())
    {
        $price = 0;
        if (Mage::helper('bergbahn')->isBergbahnTicket($product) && $product->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL) {
            $price = Mage::helper('jungfrau')->getPrice($product->getPricematrix(), $extra['date']);
        } else {
            $price = $priceModel->getSelectionPreFinalPrice($product, $selection, $selection->getSelectionQty());
        }
        return $price;
    }
}

