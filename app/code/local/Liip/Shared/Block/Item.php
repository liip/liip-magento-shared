<?php

class Liip_Shared_Block_Item extends Mage_Core_Block_Template
{
    public function getOptions($name)
    {
        $item = $this->getItem();

        if ($item instanceof Mage_Sales_Model_Quote_Item && $info = $item->getOptionByCode($name)) {
            $options = unserialize($info->getValue());
        } else if ($item instanceof Mage_Sales_Model_Order_Item) {
            $options = $item->getProductOptionByCode($name);
        } else {
            $options = false;
        }

        if ($options) {
            return new Varien_Object($options);
        }
        return new Varien_Object();
    }

    public function item($item)
    {
        $this->setItem($item);

        return $this->toHtml();
    }
}
