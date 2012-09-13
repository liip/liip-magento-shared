<?php

class Liip_Shared_Helper_Customer extends Mage_Core_Helper_Abstract
{
    /**
     * Load a customer by an attribute.
     * 
     * @return customer model
     */
    public function load($attribute, $value)
    {
        $customer = Mage::getModel('customer/customer');
        $customer->setWebsiteId(Mage::app()->getStore()->getWebsiteId());

        $resource = $customer->getResource();
        $connection = $resource->getReadConnection();

        $customerTypeId = Mage::getModel('eav/entity')->setType('customer')->getTypeId();
        $attribute = Mage::getModel('eav/entity_attribute')->loadByCode($customerTypeId, $attribute);

        $select = $connection->select();
        $select->from($attribute->getBackendTable(), array('entity_id'));
        $select->where('attribute_id = ?', $attribute->getId());
        $select->where('value = ?', $value);
        $select->order('entity_id DESC');
        $id = $connection->fetchOne($select);

        $customer->load($id);

        return $customer;
    }
}
