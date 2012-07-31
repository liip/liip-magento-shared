<?php


/**
 * Provides a cache container whose cacheId is defined in the cookie
 *
 * Thus makes it possible to invalidate the cache from elsewhere without initializing the complete
 * Magento application.
 * To invalidate the cache call {@link Liip_Shared_Model_Container_Abstract_Cookie#invalidate($newCacheId)}
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
            <container>Jungfrau_Module_Model_Container_Name</container>
            <cache_lifetime>86400</cache_lifetime>
        </example_block>
 *
 * NOTE that cache_lifetime is ignored and should be set by overriding
 * {@link Enterprise_PageCache_Model_Container_Abstract#_saveCache()}
 *
 * 3. Extend Liip_Shared_Model_Container_Abstract_Cookie and implement _getName() returning the placeholder
 *    name from above (EXAMPLE_BLOCK)

class Jungfrau_Module_Model_Container_Name extends Liip_Shared_Model_Container_Abstract_ Cookie
{
    protected function _getName()
    {
        return 'EXAMPLE_BLOCK';
    }
}
 *
 *
 */
abstract class Liip_Shared_Model_Container_Abstract_Cookie extends Enterprise_PageCache_Model_Container_Abstract
{

    const COOKIE_CACHE_CONTAINER = 'CACHE_CONTAINER';


    /**
     * Changes the cacheId thus invalidating the cache
     *
     * You should provide an id which is the hashed properties of the cache.
     *
     * @param   string|TRUE     $newId     A hash to set the cacheId to or TRUE for auto-generating
     */
    public function invalidate($newId = true)
    {
        $this->_setIdentifier($newId);
    }

    /**
     * @return  string  The name to use as placeholder, must match the one in cache.xml
     */
    abstract protected function _getName();


    /**
     * @return  string
     */
    protected function _getCacheId()
    {
        return $this->_getName().'_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }


    /**
     * Updates the cookie to represent the given cacheId
     *
     * @param   string|TRUE     $id     A hash to set the cacheId to or TRUE for autogenerating
     * @return  string                  The new cacheId
     */
    protected function _setIdentifier($id = true)
    {
        if ($id === true) {
            $id = md5(microtime());
        }

        $ids = (array)json_decode($this->_getCookieValue(self::COOKIE_CACHE_CONTAINER, '[]'));
        $ids[$this->_getName()] = $id;
        setcookie(self::COOKIE_CACHE_CONTAINER, json_encode($ids), 0, '/');

        return $id;
    }

    /**
     * @return string
     */
    protected function _getIdentifier()
    {
        $ids = (array)json_decode($this->_getCookieValue(self::COOKIE_CACHE_CONTAINER, '[]'));

        return isset($ids[$this->_getName()]) ? $ids[$this->_getName()] : '';
    }

    /**
     * Default block HTML rendering
     */
    protected function _renderBlock() {

        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        $block = new $block;
        $block->setTemplate($template);
        $block->setLayout(Mage::app()->getLayout());

        return $block->toHTML();
    }
}

