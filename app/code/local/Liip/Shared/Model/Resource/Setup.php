<?php

class Liip_Shared_Model_Resource_Setup extends Mage_Catalog_Model_Resource_Setup
{
    public function addCustomOption($product, $title, array $optionData, array $values = array())
    {
        Mage::app()->getStore()->setId(Mage_Core_Model_App::ADMIN_STORE_ID);
        if (!($product instanceof Mage_Catalog_Model_Product)) {
            if (!$product = Mage::getModel('catalog/product')->load($product)) {
                return false;
            }
        }

        $defaultValue = array(
            'price'         => 0,
            'price_type'    => 'fixed',
        );
        foreach ($values as $idx=>$value) {
            $values[$idx] = array_merge($defaultValue, $value);
        }

        $defaultData = array(
            'type'          => 'field',
            'is_require'    => 0,
            'price'         => 0,
            'price_type' => 'fixed',
        );

        $data = array_merge($defaultData, $optionData, array(
            'product_id'    => (int)$product->getId(),
            'title'         => $title,
            'values'        => $values,
        ));

        $product->setHasOptions(1)->save();
        $option = Mage::getModel('catalog/product_option')->setData($data)->setProduct($product)->save();

        return $option;
    }

    /**
     * Sets config value in db
     *
     * @param   string  $path
     * @param   string  $value
     * @param   string  $scope  E.g., Mage_Adminhtml_Block_System_Config_Form::SCOPE_STORES
     * @param   int     $scopeId
     */
    public function setConfig($path, $value, $scope = Mage_Adminhtml_Block_System_Config_Form::SCOPE_DEFAULT,
        $scopeId = Mage_Core_Model_App::ADMIN_STORE_ID)
    {
        $table = $this->getTable('core_config_data');

        // fk unique constraint on scope/scope_id/path
        $exists = $this->_conn->select()->from($table)
            ->where('scope = ?', $scope)
            ->where('scope_id = ?', $scopeId)
            ->where('path = ?', $path);
        $exists = $this->_conn->fetchAll($exists);
        if (count($exists)) {
            $exists = reset($exists);
            $this->_conn->update($table, array('value' => $value), $this->_conn->quoteInto('config_id = ?', $exists['config_id']));
        } else {
            $this->_conn->insert($table, array('scope' => $scope, 'scope_id' => $scopeId, 'path' => $path, 'value' => $value));
        }
    }

    /**
     * Adds support to add the an attribute to a set through the `attribute_set` property.
     *
     *
     * Example $attr:
     * array(
     *     'attribute_set'               => 'Feratel',
     *     'group'                       => 'Feratel',
     *     'label'                       => 'Feratel Updated At',
     *     'type'                        => 'datetime',
     *     'input'                       => 'date',
     *     'backend'                     => '', // Model for handling input in the backend (e.g., eav/entity_attribute_backend_datetime)
     *     'global'                      => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
     *     'required'                    => true,
     *     'unique'                      => false,
     *     'user_defined'                => true,
     *     'apply_to'                    => Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL,
     *     'visible_on_front'            => false,
     *     'searchable'                  => false,
     *     'filterable'                  => false,
     *     'is_configurable'             => false,
     *     'used_in_product_listing'     => false,
     *     'used_for_sort_by'            => false, // «Used for Sorting in Product Listing»
     *     'visible_in_advanced_search'  => false,
     *     'frontend'                    => '', // Model for handling display of attribute on the frontend (eg catalog/product_attribute_frontend_image)
     *     'table'                       => '',
     *     'class'                       => '', // Validation class for data. Example values: validate-digits, validate-number
     *     'source'                      => '', // Model defining values for select boxes/dropdowns (see below)
     *     'visible'                     => true,
     *     'default'                     => '',
     *     'comparable'                  => false,
     *     'unique'                      => false,
     * );
     *
     * {@inheritDoc}
     *
     * @param string|integer $entityTypeId
     * @param string $code
     * @param array $attr
     * @return Mage_Eav_Model_Entity_Setup
     *
     * @see _prepareValues() for available settings
     * @see http://www.magentocommerce.com/wiki/5_-_modules_and_development/0_-_module_development_in_magento/installing_custom_attributes_with_your_module
     */
    public function addAttribute($entityTypeId, $code, array $attr)
    {
        $entityTypeId = $this->getEntityTypeId($entityTypeId);
        $data = array_merge(
            array(
                'entity_type_id' => $entityTypeId,
                'attribute_code' => $code
            ),
            $this->_prepareValues($attr)
         );

        $sortOrder = isset($attr['sort_order']) ? $attr['sort_order'] : null;
        if ($id = $this->getAttribute($entityTypeId, $code, 'attribute_id')) {
            $this->updateAttribute($entityTypeId, $id, $data, null, $sortOrder);
        } else {
            $this->_insertAttribute($data);
        }

        if (!empty($attr['group'])) {
            $sets = $this->_conn->fetchAll('select * from '.$this->getTable('eav/attribute_set').' where entity_type_id=?', $entityTypeId);
            foreach ($sets as $set) {
                if (!empty($attr['attribute_set'])) {
                    if ($attr['attribute_set'] == $set['attribute_set_name']) {
                        $this->addAttributeGroup($entityTypeId, $set['attribute_set_id'], $attr['group']);
                        $this->addAttributeToSet($entityTypeId, $set['attribute_set_id'], $attr['group'], $code, $sortOrder);    
                    }
                } else {
                    $this->addAttributeGroup($entityTypeId, $set['attribute_set_id'], $attr['group']);
                    $this->addAttributeToSet($entityTypeId, $set['attribute_set_id'], $attr['group'], $code, $sortOrder);
                }
            }
        }
        if (empty($attr['user_defined'])) {
            $sets = $this->_conn->fetchAll('select * from '.$this->getTable('eav/attribute_set').' where entity_type_id=?', $entityTypeId);
            foreach ($sets as $set) {
                if (!empty($attr['attribute_set'])) {
                    if ($attr['attribute_set'] == $set['attribute_set_name']) {
                        $this->addAttributeToSet($entityTypeId, $set['attribute_set_id'], $attr['group'], $code, $sortOrder);
                    }
                } else {
                    $this->addAttributeToSet($entityTypeId, $set['attribute_set_id'], $this->_generalGroupName, $code, $sortOrder);
                }
            }
        }

        if (isset($attr['option']) && is_array($attr['option'])) {
            $option = $attr['option'];
            $option['attribute_id'] = $this->getAttributeId($entityTypeId, $code);
            $this->addAttributeOption($option);
        }

        return $this;
    }

    /**
     * @param   string|int      $entityTypeId
     * @param   string          $code           Attribute code
     * @param   string          $set            Target Set name
     * @param   string          $group          Target Group name
     * @throws  Exception   If attribute not found
     */
    public function copyAttributeToSet($entityTypeId, $code, $set, $group, $sortOrder = null)
    {
        $entityTypeId = $this->getEntityTypeId($entityTypeId);

        if (!$this->getAttribute($entityTypeId, $code, 'attribute_id')) {
            throw new Exception('Attribute not found: '.$code);
        }

        $sets = $this->_conn->fetchAll('select * from '.$this->getTable('eav/attribute_set').' where entity_type_id=?', $entityTypeId);
        foreach ($sets as $s) {
            if ($set == $s['attribute_set_name']) {
                $this->addAttributeGroup($entityTypeId, $s['attribute_set_id'], $group);
                $this->addAttributeToSet($entityTypeId, $s['attribute_set_id'], $group, $code, $sortOrder);
            }
        }
    }

    /**
     * Creates a new attribute set
     *
     * @param   string  $name       The name of the attribute set
     * @param   int     $baseSet    Id or name of the set the new set will be based upon (default set = 4)
     */
    public function createAttributeSet($name, $baseSet = 4)
    {
        if (is_string($baseSet)) {
            $baseSet = Mage::getModel('eav/entity_attribute_set')
                            ->load($baseSet, 'attribute_set_name')
                            ->getAttributeSetId();
        }

        $entityTypeId = Mage::getModel('catalog/product')
            ->getResource()->getEntityType()->getId();

        $attributeSet = Mage::getModel('eav/entity_attribute_set')
            ->setEntityTypeId($entityTypeId)
            ->setAttributeSetName($name);

        $attributeSet->validate();
        $attributeSet->save();
        $attributeSet->initFromSkeleton($baseSet);
        $attributeSet->save();
    }

    /**
     * Remove an Attribute from an Attribute Set
     *
     * @param mixed $entityTypeId
     * @param mixed $setId
     * @param mixed $attributeId
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function removeAttributeFromSet($entityTypeId, $setId, $attributeId)
    {
        $entityTypeId = $this->getEntityTypeId($entityTypeId);
        $setId = $this->getAttributeSetId($entityTypeId, $setId);
        $attributeId = $this->getAttributeId($entityTypeId, $attributeId);

        $this->_conn->delete($this->getTable('eav/entity_attribute'), array(
            'entity_type_id = ?'    => $entityTypeId,
            'attribute_set_id = ?'  => $setId,
            'attribute_id = ?'      => $attributeId,
        ));

        return $this;
    }

    /**
     * Update Sort Order of Attribute on Attribute Set
     *
     * @param mixed $entityTypeId
     * @param mixed $setId
     * @param mixed $groupId
     * @param mixed $attributeId
     * @param int $sortOrder
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function updateAttributeSortOrder($entityTypeId, $setId, $groupId, $attributeId, $sortOrder=null)
    {
        $entityTypeId   = $this->getEntityTypeId($entityTypeId);
        $setId          = $this->getAttributeSetId($entityTypeId, $setId);
        $groupId        = $this->getAttributeGroupId($entityTypeId, $setId, $groupId);
        $attributeId    = $this->getAttributeId($entityTypeId, $attributeId);
        $table          = $this->getTable('eav/entity_attribute');

        $bind = array(
            'attribute_set_id' => $setId,
            'attribute_group_id' => $groupId,
            'attribute_id'     => $attributeId
        );
        $select = $this->_conn->select()
            ->from($table)
            ->where('attribute_set_id = :attribute_set_id')
            ->where('attribute_group_id = :attribute_group_id')
            ->where('attribute_id = :attribute_id');
        $result = $this->_conn->fetchRow($select, $bind);

        if ($result) {
            $where = array('entity_attribute_id =?' => $result['entity_attribute_id']);
            $data  = array('sort_order' => $this->getAttributeSortOrder($entityTypeId, $setId, $groupId, $sortOrder));
            $this->_conn->update($table, $data, $where);
        }

        return $this;
    }
}

