<?php

require_once dirname(__FILE__) . '/../../../../../../../Mage.php';

class Liip_Shared_Test_Model_Core_StoreTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        Mage::app('de');
    }

    public function testRoundPriceUp()
    {
        $this->assertSame(4.05, Mage::getModel('core/store')->roundPrice(4.025));
        $this->assertSame(4.05, Mage::getModel('core/store')->roundPrice(4.04));
        $this->assertSame(4.05, Mage::getModel('core/store')->roundPrice(4.05));
        $this->assertSame(4.0, Mage::getModel('core/store')->roundPrice(3.98));
    }

    public function testRoundPriceDown()
    {
        $this->assertSame(4.0, Mage::getModel('core/store')->roundPrice(4.024));
        $this->assertSame(4.0, Mage::getModel('core/store')->roundPrice(4.01));
        $this->assertSame(4.0, Mage::getModel('core/store')->roundPrice(4));
    }
}

