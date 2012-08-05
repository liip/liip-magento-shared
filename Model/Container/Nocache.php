<?php

class Liip_Shared_Model_Container_Nocache extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * Get container individual cache id
     *
     * Override to return false to cause the block to never get cached
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return false;
    }

    /**
     * Render block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
        $block = $this->_placeholder->getAttribute('block');
        $block = new $block;

        // only needed if the block uses a template
        $block->setTemplate($this->_placeholder->getAttribute('template'));

        return $block->toHtml();
    }

    /**
     * Generate placeholder content before application was initialized and
     * apply to page content if possible
     *
     * Override to enforce calling {@see _renderBlock()}
     *
     * @param string &$content The content
     *
     * @return bool
     */
    public function applyWithoutApp(&$content)
    {
        return false;
    }
}
