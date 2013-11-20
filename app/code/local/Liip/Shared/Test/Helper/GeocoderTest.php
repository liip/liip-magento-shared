<?php

require_once dirname(__FILE__) . '/../../../../../../../../../../app/Mage.php';

class Liip_Shared_Test_Helper_GeocoderTest extends PHPUnit_Framework_TestCase
{
    protected $helper;

    public function setUp()
    {
        parent::setUp();
        Mage::app('de');
        $this->helper = new HelperGeocoderProxy();

    }

    public function testExtractGeolocation()
    {
        $loc = $this->helper->extractV3Geolocation($this->geolocationResponse);

        $lat = 37.4217550;
        $long = -122.0846330;
        $this->assertEquals($long, $loc['longitude']);
        $this->assertEquals($long, $loc[1]);
        $this->assertEquals($lat, $loc['latitude']);
        $this->assertEquals($lat, $loc[0]);
    }

    public function testExtractGeolocationFail()
    {
        $xml = 'notanxml';

        $this->assertSame(false, $this->helper->extractV3Geolocation($xml));
    }


    protected $geolocationResponse =<<<STZBR
<GeocodeResponse>
 <status>OK</status>
 <result>
  <type>street_address</type>
  <formatted_address>1600 Amphitheatre Pkwy, Mountain View, CA 94043, USA</formatted_address>
  <address_component>
   <long_name>1600</long_name>
   <short_name>1600</short_name>
   <type>street_number</type>
  </address_component>
  <address_component>
   <long_name>Amphitheatre Pkwy</long_name>
   <short_name>Amphitheatre Pkwy</short_name>
   <type>route</type>
  </address_component>
  <address_component>
   <long_name>Mountain View</long_name>
   <short_name>Mountain View</short_name>
   <type>locality</type>
   <type>political</type>
  </address_component>
  <address_component>
   <long_name>San Jose</long_name>
   <short_name>San Jose</short_name>
   <type>administrative_area_level_3</type>
   <type>political</type>
  </address_component>
  <address_component>
   <long_name>Santa Clara</long_name>
   <short_name>Santa Clara</short_name>
   <type>administrative_area_level_2</type>
   <type>political</type>
  </address_component>
  <address_component>
   <long_name>California</long_name>
   <short_name>CA</short_name>
   <type>administrative_area_level_1</type>
   <type>political</type>
  </address_component>
  <address_component>
   <long_name>United States</long_name>
   <short_name>US</short_name>
   <type>country</type>
   <type>political</type>
  </address_component>
  <address_component>
   <long_name>94043</long_name>
   <short_name>94043</short_name>
   <type>postal_code</type>
  </address_component>
  <geometry>
   <location>
    <lat>37.4217550</lat>
    <lng>-122.0846330</lng>
   </location>
   <location_type>ROOFTOP</location_type>
   <viewport>
    <southwest>
     <lat>37.4188514</lat>
     <lng>-122.0874526</lng>
    </southwest>
    <northeast>
     <lat>37.4251466</lat>
     <lng>-122.0811574</lng>
    </northeast>
   </viewport>
  </geometry>
 </result>
</GeocodeResponse>
STZBR;
}

class HelperGeocoderProxy extends Liip_Shared_Helper_Geocoder
{
    public function extractV3Geolocation($xml)
    {
        return parent::extractV3Geolocation($xml);
    }
}
