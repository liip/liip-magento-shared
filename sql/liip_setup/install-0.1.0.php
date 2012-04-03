<?php

/* @var Liip_Shared_Model_Resource_Setup*/
$installer = $this;

$installer->startSetup();

// create eav_attribute_option.`reference` if not yet existing (MySQL only)
$config = $installer->getConnection()->getConfig();
$sql = 'SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table AND COLUMN_NAME = :column';
$binds = array('db' => $config['dbname'], 'table' => $this->getTable('eav_attribute_option'), 'column' => 'reference');
$stmt = $installer->getConnection()->query($sql, $binds);

if ($stmt->rowCount() == 0) {
    $installer->run("ALTER TABLE {$this->getTable('eav_attribute_option')} ADD COLUMN `reference` VARCHAR(255) NOT NULL DEFAULT '';");
}

$installer->endSetup();

