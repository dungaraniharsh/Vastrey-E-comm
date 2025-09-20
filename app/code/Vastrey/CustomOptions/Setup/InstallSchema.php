<?php

namespace Vastrey\CustomOptions\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface {

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $setup->startSetup();

        $setup->getConnection()->addColumn(
                $setup->getTable('catalog_product_option_type_value'), 'is_international', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            'length' => 1,
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
            'comment' => 'Is International'
                ]
        );
        $setup->getConnection()->addColumn(
                $setup->getTable('catalog_product_option_type_value'), 'is_domestic', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
            'length' => 1,
            'unsigned' => true,
            'nullable' => false,
            'default' => '0',
            'comment' => 'Is Domestic'
                ]
        );
        $setup->getConnection()->addColumn(
                $setup->getTable('catalog_product_option_type_value'), 'bust_size', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 255,
            'unsigned' => true,
            'nullable' => false,
            'default' => '',
            'comment' => 'Bust Size'
                ]
        );
        $setup->endSetup();
    }

}
