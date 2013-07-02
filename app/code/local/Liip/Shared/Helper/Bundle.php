<?php

class Liip_Shared_Helper_Bundle extends Mage_Core_Helper_Abstract
{
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

