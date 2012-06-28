<?php

require_once dirname(__FILE__) . '/../../../../../../Mage.php';

class Liip_Shared_Test_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        Mage::app('de');
        $this->helper = Mage::helper('liip');

    }

    public function testExtractGeolocation()
    {
        $helper = new HelperDataProxy();

        $xml =<<<STZBR
<?xml version="1.0" encoding="UTF-8" ?>
<kml xmlns="http://earth.google.com/kml/2.0"><Response>
  <name>Feldstrasse 133, 8004 Zürich</name><Status><code>200</code><request>geocode</request></Status>
  <Placemark id="p1">
    <address>Feldstrasse 133, 8004 Zurich, Switzerland</address>
    <ExtendedData>
      <LatLonBox north="47.3812973" south="47.3785993" east="8.5256893" west="8.5229913" />
    </ExtendedData>
    <Point><coordinates>8.5243403,47.3799483,0</coordinates></Point>
  </Placemark>
</Response></kml>
STZBR;

        $loc = $helper->extractGeolocation($xml);
        $this->assertEquals(8.5243403, $loc['longitude']);
        $this->assertEquals(8.5243403, $loc[1]);
        $this->assertEquals(47.3799483, $loc['latitude']);
        $this->assertEquals(47.3799483, $loc[0]);
    }

    public function testExtractGeolocationFail()
    {
        $helper = new HelperDataProxy();

        $xml = 'notanxml';

        $this->assertSame(false, $helper->extractGeolocation($xml));
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

class HelperDataProxy extends Liip_Shared_Helper_Data
{
    public function extractGeolocation($xml)
    {
        return parent::extractGeolocation($xml);
    }
}
