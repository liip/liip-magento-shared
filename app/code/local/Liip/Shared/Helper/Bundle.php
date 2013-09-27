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
        if (!$product instanceof Mage_Catalog_Model_Product_Type_Price) {
            $product = Mage::getModel('catalog/product')->load($product);
        }
        $options = Mage::helper('daytrips/cart')->getOptions($product);


        // if user passed all params
        if (isset($bundleOptions['bundle_option'])) {
            $bundleOptions = $bundleOptions['bundle_option'];
        }

        $total = 0;

        $priceModel = $product->getPriceModel();
        foreach ($bundleOptions as $optionId => $selectionIds) {
            $selectionIds = (array)$selectionIds;

            foreach ($selectionIds as $selectionId) {
                $selection = $options[$optionId]->getSelectionById($selectionId);
                if ($selection) {
                    // calculate special cases
                    $price = $this->calculateProductPrice($selection, $extra);

                    if ($price === false) {
                        // default
                        $price = $priceModel->getSelectionPreFinalPrice($product, $selection, $selection->getSelectionQty());
                    }
                    $total += $price;
                } else {
                    return false;
                }
            }
        }
        return $total;
    }

    protected function calculateProductPrice($selection, $extra = array())
    {
        $price = false;

        if (Mage::helper('bergbahn')->isBergbahnTicket($selection) && $selection->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL) {
            $price = Mage::helper('jungfrau')->getPrice($selection->getPricematrix(), $extra['date']);
        }

        return $price;
    }


    /**
     * Retrieves the option collection of a bundle including their respective selections.
     *
     * @param   Mage_Catalog_Model_Product      $product          The product
     * @param   bool                            $appendAll        Whether to get all selections or just those saleable
     * @return  array
     */
    public function getOptions(Mage_Catalog_Model_Product $product, $appendAll = true)
    {
        $typeInstance = $product->getTypeInstance(true);
        $typeInstance->setStoreFilter($product->getStoreId(), $product);

        $optionCollection = $typeInstance->getOptionsCollection($product);

        $optionIds = $typeInstance->getOptionsIds($product);
        $selectionIds = array();

        $selectionCollection = $typeInstance->getSelectionsCollection($optionIds, $product);

        $options = $optionCollection->appendSelections($selectionCollection, false, $appendAll);

        return $options;
    }
}

