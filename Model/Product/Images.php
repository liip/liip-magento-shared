<?php

/**
 * General product image downloader and attacher, respecting already existing images.
 *
 * Only adds images to products if they do not yet exist. Existance is determined by
 * {@link labelize()} whose result is stored as label for the image.
 *
 * @method  setDownloader(Liip_Shared_Model_Connection $connection)
 * @method  Liip_Shared_Model_Connection getDownloader()
 */
class Liip_Shared_Model_Product_Images extends Varien_Object
{
    /** @var array  [ url => ['visibility' => visibility, 'extra' => mixed], .. ] */
    protected $images = array();

    protected $product;
    protected $mediaGalleryAttributeBackend;

    public function __construct($product)
    {
        $this->product = $product;
    }

    protected function getMediaGalleryAttributeBackend()
    {
        if (!$this->mediaGalleryAttributeBackend) {
            $attributes = $this->product->getTypeInstance(true)->getSetAttributes($this->product);
            if (!isset($attributes['media_gallery'])) {
                Mage::throwException('Product does not have a gallery');
            }
            $this->mediaGalleryAttributeBackend = $attributes['media_gallery']->getBackend();
        }
        return $this->mediaGalleryAttributeBackend;
    }

    /**
     * Adds an image to the product
     *
     * @param   string          $url
     * @param   array[]string   $visibility     Flags: 'small_image', 'image', 'thumbnail'
     * @param   mixed           $extra          Metadata to be used for labelizing which cannot be found in the url
     */
    public function addImage($url, array $visibility, $extra = false)
    {
        if (isset($this->images[$url]['visibility'])) {
            $visibility = array_unique(array_merge($this->images[$url]['visibility'], $visibility));
        }

        if (!isset($this->images[$url])) {
            $this->images[$url] = array();
        }

        $this->images[$url]['visibility'] = $visibility;
        if ($extra) {
            $this->images[$url]['extra'] = $extra;
        }
    }

    /**
     * Batch download and save images to the product
     */
    public function save()
    {
        $existing = $this->getExistingImages();

        $downloader = $this->getDownloader();
        if (!$downloader) {
            $downloader = Mage::getModel('liip/connection_curl');
        }

        foreach ($this->images as $url=>$info) {
            $label = $this->labelize($url, $info);

            if (!isset($existing[$label])) {
                $downloader->setUrl($url);
                $filename = $downloader->download(null, null, 'product');

                if (!$filename) {
                    Mage::log('Failed downloading image to '.$filename.': '.$url, Zend_Log::WARN);
                } else {
                    $this->saveImage($filename, $info['visibility'], $label);
                }
            }
        }
    }

    protected function saveImage($filename, array $visibility, $label)
    {
        try {
            $movedname = $this->getMediaGalleryAttributeBackend()->addImage(
                $this->product,
                $filename,
                $visibility,
                true,
                false
            );
        } catch (Exception $e) {
            Mage::log('Error adding image ('.$filename.') to product ('.$this->product->getId().'): '.$e->getMessage(), Zend_Log::WARN);
        }

        $this->getMediaGalleryAttributeBackend()->updateImage($this->product, $movedname, array('label' => $label));
    }

    protected function getExistingImages()
    {
        $existing = array();
        foreach ($this->product->getMediaGalleryImages() as $image) {
            $existing[$image->getLabelDefault()] = $image;
        }
        return $existing;
    }

    protected function labelize($url, $info)
    {
        return md5($url);
    }

}

