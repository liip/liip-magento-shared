<?php

/**
 * General product image downloader and attacher, respecting already existing images.
 *
 * Only adds images to products if they do not yet exist. Existance is determined by
 * md5'ing the url.
 *
 * @method  setDownloader(Liip_Shared_Model_Connection $connection)
 * @method  Liip_Shared_Model_Connection getDownloader()
 * @method  setDeleteUnknownImages(bool)
 * @method  bool getDeleteUnknownImages()
 * @method  setLabelize(int)
 * @method  int getLabelize()
 */
class Liip_Shared_Model_Product_Images extends Varien_Object
{
    const LABELIZE_MD5 = 1;
    const LABELIZE_URL = 2;

    /** @var array  [ url => ['visibility' => visibility, 'extra' => mixed], .. ] */
    protected $images = array();

    protected $product;
    protected $mediaGalleryAttributeBackend;

    public function __construct($product)
    {
        $this->product = $product;
        $this->setLabelize(self::LABELIZE_MD5);
    }

    /**
     * @return  Varien_Data_Collection
     */
    public function getAllMediaGalleryImages($includeDisabled = true)
    {
        $images = new Varien_Data_Collection();
        if ($includeDisabled) {
            foreach ($this->product->getMediaGallery('images') as $image) {
                $image['url'] = $this->product->getMediaConfig()->getMediaUrl($image['file']);
                $image['id'] = isset($image['value_id']) ? $image['value_id'] : null;
                $image['path'] = $this->product->getMediaConfig()->getMediaPath($image['file']);
                $images->addItem(new Varien_Object($image));
            }
        } else {
            $images = $this->product->getMediaGalleryImages();
        }

        return $images;
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
     * @return  $this
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

        return $this;
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
            $existing[$label] = true;
        }

        if ($this->getDeleteUnknownImages()) {
            // delete all images that we haven't seen and have a label
            foreach ($existing as $label=>$img) {
                if ($img !== true && strlen($label)) { // we didn't see this one
                    $this->getMediaGalleryAttributeBackend()->removeImage($this->product, $img->getFile());
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
            return;
        }

        $this->getMediaGalleryAttributeBackend()->updateImage($this->product, $movedname, array('label' => $label));
    }

    protected function getExistingImages()
    {
        $existing = array();
        $images = $this->product->getMediaGalleryImages();
        if (is_array($images) || $images instanceof Traversable) {
            foreach ($images as $image) {
                $existing[$image->getLabelDefault()] = $image;
            }
        }
        return $existing;
    }

    /**
     * Creates the image label to use in Magento
     *
     * @return string
     */
    protected function labelize($url, $info)
    {
        switch ($this->getLabelize()) {
        case self::LABELIZE_MD5:
            return md5($url);

        case self::LABELIZE_URL:
        default:
            return $url;
        }
        return $url;
    }

}

