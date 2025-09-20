<?php

namespace Kreeva\ErpIntegration\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
  /**
   * {@inheritdoc}
   */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
        if (version_compare($context->getVersion(), "1.0.2", "<")) {
            $intMgtTable = 'kr_integration_management';
            $installer->getConnection()
                ->addColumn(
                $installer->getTable($intMgtTable),
                'message_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 250,
                    'nullable' => true,
                    'comment' => 'Queue MessageID'
                ]
            );
        }
        if (version_compare($context->getVersion(), "1.0.3", "<")) {
            $tableName = $installer->getTable('kr_integration_mapper');
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
                              'erp_id',
                              Table::TYPE_TEXT,
                              255,
                              [],
                              'FROM'
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
                      ->setComment('kr integration mapper');
                $installer->getConnection()->createTable($table);
            }
            if (version_compare($context->getVersion(), "1.0.4", "<")) {
                $intMgtTable = 'kr_integration_management';
                $installer->getConnection()
                    ->addColumn(
                    $installer->getTable($intMgtTable),
                    'response',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => '2M',
                        'nullable' => true,
                        'comment' => 'Queue response'
                    ]
                );
            }
        }
        $installer->endSetup();
    }
}
