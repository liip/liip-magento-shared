<?php

/**
 * Cache per customer
 */
class Liip_Shared_Model_PageCache_Container_Customer extends Enterprise_PageCache_Model_Container_Customer
{
    /**
     * @return string
     */
    protected function _getIdentifier()
    {
        return $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER, '');
    }

    /**
     * @return string
     */
    protected function _getCacheId()
    {
        return 'LIIP_CUSTOMER_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }

    /**
     * @return string
     */
    protected function _renderBlock()
    {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        $block = new $block;
        $block->setTemplate($template);
        $block->setLayout(Mage::app()->getLayout());

        return $block->toHTML();
    }
}
