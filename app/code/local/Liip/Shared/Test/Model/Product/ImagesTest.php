<?php

require_once dirname(__FILE__) . '/../../../Test/Magento.php';
require_once dirname(__FILE__) . '/../../../Model/Product/Images.php';

class Liip_Shared_Test_Model_ProductImages extends Liip_Shared_Test_Magento
{
    public function setUp()
    {
        parent::setUp();
        Mage::app('admin');
    }

    public function testSave()
    {
        $product = new Varien_Object();
        $imgr = $this->getMock(
            'Liip_Shared_Model_Product_Images',
            array('saveImage', 'getMediaGalleryAttribute', 'getExistingImages')
        );
        $imgr->setDownloader(Mage::getModel('liip/connection_mock', 'response'));
        $imgr->addImage('http://example.com/whadup.jpg', array('small_image', 'image'));
        $imgr->addImage('http://example.com/notmuch.png', array('thumbnail'));
        $imgr->addImage('http://example.com/whynot.jpg', array('image'));
        $imgr->addImage('http://example.com/writingtests.tif', array('small_image', 'image', 'thumbnail'));

        $imgr->expects($this->once())
            ->method('getExistingImages')
            ->will($this->returnValue(array('bf323c3cc11cafbbe71a2bf118d59afc'=>'{whadup.jpg}', '48f59d90437ad1261480f59d9ae6e8e3'=>'{notmuch.png}')));

        $imgr->expects($this->exactly(2))
            ->method('saveImage');

        /* why is this not working?
        $imgr->expects($this->any())
            ->method('saveImage') // whynot.jpg
            ->with('response', array('image'), '519c94d49ed175dc3d39408b64dd76d8');

        $imgr->expects($this->at(1))
            ->method('saveImage') // writingtests.tif
            ->with('response', array('small_image', 'image', 'thumbnail'), '18de5e910c0a7614dbf1bbda24ce656a');
        &*/

        $imgr->save();
    }
}
