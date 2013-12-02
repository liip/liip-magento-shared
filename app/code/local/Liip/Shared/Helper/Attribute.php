<?php

class Liip_Shared_Helper_Attribute extends Mage_Core_Helper_Abstract
{
    /**
     * Retrieves the attribute set id by given name.
     *
     * @param   string  $attributeSetName
     * @return  int     The attribute set id
     */
    public function getSetId($attributeSetName)
    {
        return Mage::getModel('eav/entity_attribute_set')->load($attributeSetName, 'attribute_set_name')->getAttributeSetId();
    }

    /**
     * Get all options for an attribute.
     *
     * @param   string  $code   Attribute code
     * @param   string  $index  Column to index array with (reference, sort_order)
     * @param   string  $entityType  Entity type
     * @return  array   [index => ['option_id' => id, 'label' => str, 'reference' => ref], .. ]
     */
    public function getOptions($code, $index = 'reference', $entityType = Mage_Catalog_Model_Product::ENTITY, $storeId = Mage_Core_Model_App::ADMIN_STORE_ID)
    {
        $attribute = Mage::getModel('eav/entity_attribute');
        $resource = $attribute->getResource();
        $connection = $resource->getReadConnection();

        $select = $connection->select()->from(array('o' => $resource->getTable('attribute_option')), array('option_id', 'sort_order', 'reference'));
        $select->joinLeft(array('v' => $resource->getTable('attribute_option_value')), 'v.option_id = o.option_id', array('label' => 'value'));
        $select->where('o.attribute_id = ?', $attribute->getIdByCode($entityType, $code));
        $select->where('v.store_id = ?', $storeId);
        $select->order('o.sort_order');

        $options = array();
        foreach ($connection->fetchAll($select) as $row) {
            $options[$row[$index]] = new Varien_Object($row);
        }

        return $options;
    }

    /**
     * Get all options for an attribute but provides a fallback for value if it was empty
     *
     * @param   string  $code   Attribute code
     * @param   string  $index  Column to index array with (reference, sort_order)
     * @param   string  $entityType  Entity type
     * @param   int     $storeId
     * @param   int     $fallbackStoreId    The store id to use for the value if $storeId's value is empty
     * @return  array   [index => ['option_id' => id, 'label' => str, 'reference' => ref], 'value' => localized ]
     */
    public function getOptionsWithFallback($code, $index = 'reference', $entityType = Mage_Catalog_Model_Product::ENTITY, $storeId = Mage_Core_Model_App::ADMIN_STORE_ID, $fallbackStoreId = Mage_Core_Model_App::ADMIN_STORE_ID)
    {
        $attribute = Mage::getModel('eav/entity_attribute');
        $resource = $attribute->getResource();
        $connection = $resource->getReadConnection();

        $select = $connection->select()->from(
            array('o' => $resource->getTable('attribute_option')),
            array(
                'option_id', 'sort_order', 'reference',
                'label' => 'IFNULL(v.value, v_fallback.value) AS label',
            )
        );
        $select->joinLeft(
            array('v' => $resource->getTable('attribute_option_value')),
            'v.option_id = o.option_id '.$connection->quoteInto(' AND v.store_id = ?', $storeId),
            array()
        );
        $select->joinLeft(
            array('v_fallback' => $resource->getTable('attribute_option_value')),
            'v_fallback.option_id = o.option_id '.$connection->quoteInto('AND v_fallback.store_id = ?', $fallbackStoreId),
            array()
        );
        $select->where('o.attribute_id = ?', $attribute->getIdByCode($entityType, $code));
        $select->order('o.sort_order');

        $options = array();
        foreach ($connection->fetchAll($select) as $row) {
            $options[$row[$index]] = new Varien_Object($row);
        }

        return $options;
    }

    /**
     * Get an option id by attribute code and reference.
     *
     * @param   string  $code       Attribute code
     * @param   string  $reference
     * @return  int|null    Option id or null if not found
     */
    public function getOptionIdByReference($code, $reference)
    {
        $attribute = Mage::getModel('eav/entity_attribute');
        $resource = $attribute->getResource();
        $id = $attribute->getIdByCode('catalog_product', $code);

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $connection->select()->from($resource->getTable('attribute_option'), array('option_id'))->where('attribute_id = ?', $id)->where('reference = ?', $reference);
        return $connection->fetchOne($select);
    }

    /**
     * @deprecated use self::getOptionIdByReference()
     */
    public function getOptionId($code, $reference)
    {
        return $this->getOptionIdByReference($code, $reference);
    }

    /**
     * Get an option by attribute code and reference.
     *
     * @param   string  $code       Attribute code
     * @param   string  $reference
     * @return  array   Option row with admin label
     */
    public function getOption($code, $reference, $storeId = Mage_Core_Model_App::ADMIN_STORE_ID)
    {
        $attribute = Mage::getModel('eav/entity_attribute');
        $resource = $attribute->getResource();
        $id = $attribute->getIdByCode('catalog_product', $code);

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $connection->select()->from(array('o' => $resource->getTable('attribute_option')));
        $select->joinLeft(array('v' => $resource->getTable('attribute_option_value')), 'v.option_id = o.option_id', array('label' => 'value'));
        $select->where('o.attribute_id = ?', $id);
        $select->where('o.reference = ?', $reference);
        $select->where('v.store_id = ?', $storeId);
        return $connection->fetchRow($select);
    }

    /**
     * Get the reference of an attribute option by its id.
     *
     * @param   string  $code       Attribute code
     * @param   int     $int        Option id
     * @return  string|null     Reference or null if not found
     */
    public function getOptionReference($code, $id)
    {
        // attribute model, its resource model and id
        $attribute = Mage::getModel('eav/entity_attribute');
        $resource = $attribute->getResource();
        $connection = $resource->getReadConnection();

        $select = $connection->select();
        $select->from($resource->getTable('attribute_option'), array('reference'));
        $select->where('attribute_id = ?', $attribute->getIdByCode('catalog_product', $code));
        $select->where('option_id = ?', $id);

        return $connection->fetchOne($select);
    }

    /**
     * Retrieves the human readable name of an option
     *
     * @param   string  $code   Attribute code
     * @param   int     $id     Option id
     * @param   string|null     Name or null it not found
     */
    public function getOptionName($code, $id, $store = Mage_Core_Model_App::ADMIN_STORE_ID, $entityType = Mage_Catalog_Model_Product::ENTITY)
    {
        // attribute model, its resource model and id
        $attribute = Mage::getModel('eav/entity_attribute');
        $resource = $attribute->getResource();
        $attributeId = $attribute->getIdByCode($entityType, $code);

        // write connection
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $connection->select()
        ->from(array('o' => $resource->getTable('attribute_option')), array())
        ->where('o.attribute_id = ?', $attributeId)
        ->where('o.option_id = ?', $id);

        $select->joinLeft(array('v' => $resource->getTable('attribute_option_value')), 'o.option_id = v.option_id', array('value'))
        ->where('v.store_id = ?', $store);

        $name = $connection->fetchOne($select);
        if ($name === false && $store != Mage_Core_Model_App::ADMIN_STORE_ID) {
            // must be inherited from admin
            $name = $this->getOptionName($code, $id, Mage_Core_Model_App::ADMIN_STORE_ID, $entityType);
        }

        return $name;
    }

    /**
     * Retrieves the human readable name of an option by reference
     *
     * @param   string  $code   Attribute code
     * @param   int     $id     Option id
     * @param   string|null     Name or null it not found
     */
    public function getOptionNameByReference($code, $reference, $store = Mage_Core_Model_App::ADMIN_STORE_ID, $entityType = Mage_Catalog_Model_Product::ENTITY)
    {
        // attribute model, its resource model and id
        $attribute = Mage::getModel('eav/entity_attribute');
        $resource = $attribute->getResource();
        $attributeId = $attribute->getIdByCode($entityType, $code);

        // write connection
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $connection->select()
        ->from(array('o' => $resource->getTable('attribute_option')), array())
        ->where('o.attribute_id = ?', $attributeId)
        ->where('o.reference = ?', $reference);

        $select->joinLeft(array('v' => $resource->getTable('attribute_option_value')), 'o.option_id = v.option_id', array('value'))
        ->where('v.store_id = ?', $store);

        $name = $connection->fetchOne($select);
        if ($name === false && $store != Mage_Core_Model_App::ADMIN_STORE_ID) {
            // must be inherited from admin
            $name = $this->getOptionNameByReference($code, $reference, Mage_Core_Model_App::ADMIN_STORE_ID, $entityType);
        }

        return $name;
    }

    /**
     * Retrieves the reference by name
     * @param   string  $code   Attribute code
     * @param   string  $name   Option name
     * @return  string|null     Reference or null it not found
     */
    public function getOptionReferenceByName($code, $name)
    {
        $attribute = Mage::getModel('eav/entity_attribute');
        $resource = $attribute->getResource();
        $id = $attribute->getIdByCode('catalog_product', $code);

        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $connection->select();
        $select->from(array('o' => $resource->getTable('attribute_option')), array('option_id'));
        $select->where('o.attribute_id = ?', $id);
        $select->joinLeft(array('ov' => $resource->getTable('attribute_option_value')), 'o.option_id = ov.option_id', array());
        $select->where('ov.value = ?', $name);
        $select->limit(1);

        return $connection->fetchOne($select);
    }

    /**
     * Creates or updates an attribute option by reference
     *
     * If the reference does not yet exist we insert and otherwise update the option
     *
     * @param   string  $code       Attribute code
     * @param   string  $reference  Reference to option row (optional, will then go for `name' instead)
     * @param   string|array $name  Name to store or array of names for multi-store sites, e.g., [store_id => 'Switzerland', store_id => 'Schweiz'] (first one will be set as admin)
     * @param int $sort
     * @param bool $override Whether to update the label if it already exists
     * @return int The id of the option
     */
    public function addOption($code, $reference, $name, $sort = 0, $override = true)
    {
        // attribute model, its resource model and id
        $attribute = Mage::getModel('eav/entity_attribute');
        $resource = $attribute->getResource();
        $id = $attribute->getIdByCode('catalog_product', $code);

        // write connection
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        if (strlen($reference)) {
            $select = $connection->select()->from($resource->getTable('attribute_option'), array('option_id'))->where('attribute_id = ?', $id)->where('reference = ?', $reference);
        } else {
            $select = $connection->select()
                ->from(array('o' => $resource->getTable('attribute_option')), array('option_id'))
                ->join(array('v' => $resource->getTable('attribute_option_value')), 'o.option_id = v.option_id', array())
                    ->where('o.attribute_id = ?', $id)->where('v.value = ?', $name);
        }

        if ($option = $connection->fetchOne($select)) {
            // UPDATE

            if ($override) {
                if (is_array($name)) {

                    // other stores
                    foreach ($name as $storeId => $value) {
                        $connection->update($resource->getTable('attribute_option_value'), array('value' => $value), array($connection->quoteInto('store_id = ?', $storeId), $connection->quoteInto('option_id = ?', $option)));
                    }

                } else {
                    $connection->update($resource->getTable('attribute_option_value'), array('value' => $name), $connection->quoteInto('option_id = ?', $option));
                }

                // update sort
                $connection->update($resource->getTable('attribute_option'), array('sort_order' => $sort), $connection->quoteInto('option_id = ?', $option));
            }

        } else {
            // INSERT
            $data = array(
               'attribute_id' => $id,
               'sort_order' => $sort,
               'reference' => (string)$reference
            );
            $connection->insert($resource->getTable('attribute_option'), $data);

            // get option id
            $option = $connection->lastInsertId();

            if (is_array($name)) {

                // other stores
                foreach ($name as $storeId => $value) {
                    $data = array(
                        'option_id' => $option,
                        'store_id' => $storeId,
                        'value' => $value,
                    );
                    $connection->insert($resource->getTable('attribute_option_value'), $data);
                }

            } else {

                $data = array(
                    'option_id' => $option,
                    'store_id' => Mage_Core_Model_App::ADMIN_STORE_ID,
                    'value' => $name
                );
                $connection->insert($resource->getTable('attribute_option_value'), $data);
            }
        }
        return $option;
    }

    /**
     * Deletes all options of given attribute
     *
     * @param   string  $code       Attribute code
     */
    public function removeAllOptions($code)
    {
        $optionsDel = array();
        $options = Mage::helper('liip/attribute')->getOptions($code);

        foreach ($options as $key => $option) {
            $optionsDel['delete'][$option->option_id] = true;
            $optionsDel['value'][$option->option_id] = true;
        }
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $setup->addAttributeOption($optionsDel);
    }

    /**
     * Deletes the given option of the attribute
     *
     * @param   string  $code       Attribute code
     * @param   string|array    $reference      The reference to delete or multiple
     */
    public function removeOptions($code, $references)
    {
        $optionsDel = array();
        $options = Mage::helper('liip/attribute')->getOptions($code);

        foreach ($options as $key => $option) {
            if (in_array($option->getReference(), (array)$references)) {
                $optionsDel['delete'][$option->option_id] = true;
                $optionsDel['value'][$option->option_id] = true;
            }
        }
        $setup = new Mage_Eav_Model_Entity_Setup('core_setup');
        $setup->addAttributeOption($optionsDel);
    }
}
