<?php

require_once dirname(__FILE__) . '/../../../../../../../../../app/Mage.php';
require_once dirname(__FILE__) . '/Abstract.php';

class Liip_Shared_Test_Magento extends Liip_Shared_Test_Abstract
{
    public function setUp()
    {
        parent::setUp();
        Mage::$headersSentThrowsException = false;
    }

    public function tearDown()
    {
        parent::tearDown();

        // reset $_POST
        Mage::app()->getRequest()->setPost(array());

        // close connections
        Mage::getSingleton('core/resource')->getConnection(Mage_Core_Model_Resource_Setup::DEFAULT_SETUP_CONNECTION)->closeConnection();

        // reset session
        if (session_id()) {
            session_destroy();
        }

        // reset Magento
        Mage::reset();
    }

    /**
     * You aren't supposed to call this multiple times per test...
     * @param   string  $url    The relative url (e.g. "/en/catalog/product/view/id/123")
     */
    protected function load($url, $post = array())
    {
        $request = Mage::app()->getRequest();
        $request->setRequestUri($url);
        if ($post) {
            $request->setPost($post);
            $_SERVER['REQUEST_METHOD'] = 'POST';
        }

        // real magic!
        $controller = new Mage_Core_Controller_Varien_Front();
        $controller->init();
        $request->setPathInfo()->setDispatched(false);
        if (!$request->isStraight()) {
            Mage::getModel('core/url_rewrite')->rewrite();
        }
        $controller->rewrite();
        $i = 0;
        while (!$request->isDispatched() && $i++<100) {
            foreach ($controller->getRouters() as $router) {
                if ($router->match($request)) {
                    break;
                }
            }
        }
        if ($i>100) {
            throw new Exception ('Front controller reached 100 router match iterations');
        }

        return $controller->getResponse();
    }
}

