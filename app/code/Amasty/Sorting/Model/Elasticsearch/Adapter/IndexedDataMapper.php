<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Sorting
 */


declare(strict_types=1);

namespace Amasty\Sorting\Model\Elasticsearch\Adapter;

use Magento\Framework\Indexer\IndexerRegistry;
use Amasty\Sorting\Model\ResourceModel\Method\AbstractMethod;
use Amasty\Sorting\Helper\Data;

abstract class IndexedDataMapper implements DataMapperInterface
{
    const DEFAULT_VALUE = 0;

    /**
     * @var AbstractMethod
     */
    protected $resourceMethod;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var IndexerRegistry
     */
    private $indexerRegistry;

    public function __construct(
        IndexerRegistry $indexerRegistry,
        AbstractMethod $resourceMethod,
        Data $helper
    ) {
        $this->resourceMethod = $resourceMethod;
        $this->helper = $helper;
        $this->indexerRegistry = $indexerRegistry;
    }

    /**
     * @return string
     */
    abstract public function getIndexerCode();

    /**
     * @param int $storeId
     * @param array $entityIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function forceLoad(int $storeId, ?array $entityIds = []): array
    {
        try {
            $indexer = $this->indexerRegistry->get($this->getIndexerCode());
            $indexer->reindexAll();
        } catch (\InvalidArgumentException $e) {
            ;//No action required
        }

        return $this->resourceMethod->getIndexedValues($storeId, $entityIds);
    }

    public function isAllowed(int $storeId): bool
    {
        return !$this->helper->isMethodDisabled($this->resourceMethod->getMethodCode(), $storeId);
    }

    public function map(int $entityId, array $entityIndexData, int $storeId, ?array $context = []): array
    {
        $value = isset($this->values[$storeId][$entityId]) ? $this->values[$storeId][$entityId] : self::DEFAULT_VALUE;

        return [static::FIELD_NAME => $value];
    }

    public function loadEntities(int $storeId, array $entityIds): void
    {
        if (!$this->values) {
            $this->values[$storeId] = $this->forceLoad($storeId, $entityIds);
        }
    }

    public function clearValues(): void
    {
        $this->values = null;
    }
}
