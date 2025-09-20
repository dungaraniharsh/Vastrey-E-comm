<?php
namespace Kreeva\ErpIntegration\Plugin;

class Grid
{

    public static $table = 'kr_integration_management';
    public static $leftJoinTable = 'sales_order';

    public static $salesOrderGrid = 'sales_order_grid';
    public static $integrationMapper = 'kr_integration_mapper';

    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {

            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);
            $collection
                ->getSelect()
                ->joinLeft(
                    ['sc' => $leftJoinTableName],
                    "sc.entity_id = main_table.entity_id",
                    [
                        'increment_id'
                    ]
                );

            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);

            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);


        }

        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$salesOrderGrid)) {

            $leftJoinTableName = $collection->getConnection()->getTableName(self::$integrationMapper);
            $collection
                ->getSelect()
                ->joinLeft(
                    ['im' => $leftJoinTableName],
                    "im.entity_id = main_table.entity_id",
                    [
                        'im.entity_id' => "if (im.entity_id is not null, 'yes', 'no') as sync_status",
                    ]
                );

            $where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);

            $collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);


        }
        return $collection;


    }


}
