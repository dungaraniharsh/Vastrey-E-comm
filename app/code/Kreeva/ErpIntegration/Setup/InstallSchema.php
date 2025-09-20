<?php

namespace Kreeva\ErpIntegration\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class InstallSchema implements InstallSchemaInterface {

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        $installer->startSetup();
        $tableName = $installer->getTable('kr_integration_management');
        //Check for the existence of the table
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $installer->getConnection()
                    ->newTable($tableName)
                    ->addColumn(
                            'id',
                            Table::TYPE_BIGINT,
                            null,
                            [
                                'identity' => true,
                                'unsigned' => true,
                                'nullable' => false,
                                'primary' => true
                            ],
                            'ID'
                    )
                    ->addColumn(
                            'entity_type',
                            Table::TYPE_TEXT,
                            255,
                            [],
                            'ENTITY_TYPE'
                    )
                    ->addColumn(
                            'entity_id',
                            Table::TYPE_BIGINT,
                            null,
                            [],
                            'ENTITY_ID'
                    )
                    ->addColumn(
                            'from',
                            Table::TYPE_TEXT,
                            255,
                            [],
                            'FROM'
                    )
                    ->addColumn(
                            'to',
                            Table::TYPE_TEXT,
                            255,
                            [],
                            'TO'
                    )
                    ->addColumn(
                            'status',
                            Table::TYPE_TEXT,
                            255,
                            ['default' => 'pending'],
                            'STATUS'
                    )
                    ->addColumn(
                            'sync_data',
                            Table::TYPE_TEXT,
                            '2M',
                            [],
                            'DATA'
                    )
                    ->addColumn(
                            'created_at',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            null,
                            ['default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT],
                            'Created At'
                    )->addColumn(
                            'update_at',
                            \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                            null,
                            [ 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT_UPDATE],
                            'Updated At'
                    )
                    ->setComment('kr integration management Table');
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }

}
