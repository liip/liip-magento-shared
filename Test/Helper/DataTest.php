<?php

require_once dirname(__FILE__) . '/../../../../../../Mage.php';

class Liip_Shared_Test_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        Mage::app('de');
        $this->helper = Mage::helper('liip/data');

    }

    public function testisDateInRange()
    {
        /*
        - Without Time
        */
        // within the date range
        $from = '2012-06-06 00:00:00';
        $to = '2012-06-08 00:00:00';
        $fakeDate = strtotime('2012-06-07 19:23:52');
        $this->assertTrue($this->helper->isDateInRange($from, $to, $fakeDate));

        // after the date range
        $from = '2012-06-06 00:00:00';
        $to = '2012-06-08 00:00:00';
        $fakeDate = '2012-06-09 00:00:01';
        $this->assertFalse($this->helper->isDateInRange($from, $to, $fakeDate));

        // before the date range
        $from = '2012-06-06 00:00:00';
        $to = '2012-06-08 00:00:00';
        $fakeDate = '2012-06-05 23:59:59';
        $this->assertFalse($this->helper->isDateInRange($from, $to, $fakeDate));

        /*
        - With Time
        */
        // within the time range
        $from = '2012-06-06 09:30:00';
        $to = '2012-06-08 09:30:00';
        $fakeDate = strtotime('2012-06-06 09:30:00');
        $this->assertTrue($this->helper->isDateInRange($from, $to, $fakeDate));

        // within the time range
        $from = '2012-06-06 09:30:00';
        $to = '2012-06-08 09:30:00';
        $fakeDate = strtotime('2012-06-08 09:30:00');
        $this->assertTrue($this->helper->isDateInRange($from, $to, $fakeDate));

        // before the time range
        $from = '2012-06-06 09:30:00';
        $to = '2012-06-08 09:30:00';
        $fakeDate = strtotime('2012-06-06 09:29:59');
        $this->assertFalse($this->helper->isDateInRange($from, $to, $fakeDate));

        // after the time range
        $from = '2012-06-06 09:30:00';
        $to = '2012-06-08 09:30:00';
        $fakeDate = strtotime('2012-06-08 09:30:01');
        $this->assertFalse($this->helper->isDateInRange($from, $to, $fakeDate));

        /*
        - Special Cases
        */
        // only one time set
        $from = '2012-06-06 09:30:00';
        $to = '2012-06-08 00:00:00';
        $fakeDate = strtotime('2012-06-06 09:30:00');
        $this->assertTrue($this->helper->isDateInRange($from, $to, $fakeDate));

        // only one date set
        $from = '2012-06-06 09:30:00';
        $to = NULL;
        $fakeDate = strtotime('2012-06-06 09:30:00');
        $this->assertTrue($this->helper->isDateInRange($from, $to, $fakeDate));

        // only one date set
        $from = NULL;
        $to = '2012-06-06 09:30:00';
        $fakeDate = strtotime('2012-06-06 09:30:01');
        $this->assertFalse($this->helper->isDateInRange($from, $to, $fakeDate));
    }
}
