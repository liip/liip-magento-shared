<?php

/* @var Liip_Shared_Model_Resource_Setup*/
$installer = $this;

$installer->startSetup();

$installer->run("ALTER TABLE {$this->getTable('eav_attribute_option')} ADD COLUMN `reference` VARCHAR(255) NOT NULL DEFAULT '';");

$installer->endSetup();

