<?php

class Liip_Shared_Helper_Category extends Mage_Core_Helper_Abstract
{
    /**
     * @param string $name Name in the default store view
     * @return Mage_Catalog_Model_Category|NULL The category or NULL if not found
     */
    public function getCategoryByName($name)
    {
        $collection = Mage::getModel('catalog/category')->getCollection();

        $collection->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
        $collection->addAttributeToFilter('name', $name);

        foreach ($collection as $category) {
            return $category;
        }
        return null; // no category found
    }

    /**
     * @param string $name Name in the default store view
     * @return int|NULL The id of the category or NULL if not found
     */
    public function getCategoryIdByName($name)
    {
        $category = $this->getCategoryByName($name);

        if ($category) {
            return $category->getId();
        }
        return null; // no category found
    }

    /**
     * @param int $id Category ID
     * @return string The category or NULL if not found
     */
    public function getCategoryAdminName($id)
    {
        $collection = Mage::getModel('catalog/category')->getCollection();

        $collection->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
        $collection->addAttributeToFilter('entity_id', $id);
        $collection->addAttributeToSelect('name');

        foreach ($collection as $category) {
            return $category->getName();
        }
        return null; // no category found
    }
}

