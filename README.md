A Magento module containing recurring methods we have been using in most projects.


Features
========

* Extended set of attribute manipulation methods for the installer (sql setup)
* Connection class to download files
* Adds a `reference` field to attribute options for improved linking of imported values
* Many methods to handle attributes and their options
* Full page caching container classes for: disabling caching / cookie-based caching

Installation
============

Clone the repo

    $ git clone gitosis@git.liip.ch:liip/magento/modules/liip-shared app/code/local/Liip/Shared


Deploy files outside the module directory

    $ cp -R app/code/local/Liip/Shared/install/* .


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



Attribute
---------

See `Helper/Attribute.php`



Connection
----------

See `Model/Connection*`
