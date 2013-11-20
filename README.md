A Magento module containing recurring methods we have been using in most projects.


Features
========

* Extended set of attribute manipulation methods for the installer (sql setup)
* Connection class to download files
* Adds a `reference` field to attribute options for improved linking of imported values
* Many methods to handle attributes and their options
* Full page caching container classes (EE only) for:
    disabling / cookie-based / per-customer / per-session
* Proper URL umlaut replacements (ä: ae)
* Rounding of price to 0.05 (hardcoded)
* Various helpers



Installation
============

Add repository to composer.json

    {
        "repositories": [
            {
              "type": "composer",
              "url": "http://packages.firegento.com"
            }
        ],
        "require": {
            "liip/liip-magento-shared": "1.*",
        },
        "extra":{
            "magento-root-dir": "./"
        }
    }

Install package through composer

    $ ./composer.phar install


Usage
=====

The module renames its Magento namespace as `liip`. Therefore, you access models and helpers
as `Mage::getModel('liip/connection_curl')` or `Mage::helper('liip')`.

Use the Setup class in your module
----------------------------------

In your module's `config.xml`, specify the new setup class

    <global>
        <resources>
            <mymodule_setup>
                <setup>
                    <module>Liip_Shared</module>
                    <class>Liip_Shared_Model_Resource_Setup</class>
                </setup>
            </mymodule_setup>
        </resources>
    </global>

