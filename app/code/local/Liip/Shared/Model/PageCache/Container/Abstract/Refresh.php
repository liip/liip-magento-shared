<?php

/**
 * Provides a container that never caches
 *
 * USAGE:
 * 1. @layout.xml
 *
    <block type="core/template" name="example" ... />
 *
 * 2. @etc/cache.xml:
 *
         <example_block>
            <block>core/template</block>
            <name>example</name>
            <placeholder>EXAMPLE_BLOCK</placeholder>
            <container>Liip_Shared_Model_PageCache_Container_Name</container>
            <cache_lifetime>86400</cache_lifetime>
        </example_block>
 *
 * 3. Extend this class and implement _getName() returning the placeholder from cache.xml (EXAMPLE_BLOCK)
 */
abstract class Liip_Shared_Model_PageCache_Container_Abstract_Refresh extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * @return  string  The name to use as placeholder (must be same as <placeholder> in cache.xml)
     */
    abstract protected function _getName();

    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false; // never cache
    }

    protected function _getCacheId()
    {
        return $this->_placeholder->getAttribute('name').'_' . md5($this->_placeholder->getAttribute('cache_id'));
    }

    protected function _renderBlock() {

        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        $block = new $block;
        $block->setTemplate($template);
        $block->setLayout(Mage::app()->getLayout());

        return $block->toHTML();
    }
}


