<?php

class Liip_Shared_Model_Resource_Setup extends Mage_Catalog_Model_Resource_Setup
{

    /**
     * Adds support to add the an attribute to a set through the `attribute_set` property
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
}

