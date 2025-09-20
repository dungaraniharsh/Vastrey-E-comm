<?php

namespace Kreeva\ErpIntegration\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\Data\Form\FormKey;
use Kreeva\ErpIntegration\Model\ResourceModel\IntegrationMapper\CollectionFactory;

class SyncStatus extends Column
{

    private $urlBuilder;
    private $formKey;
    protected $integrationMapperCollectionFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        CollectionFactory $integrationMapperCollectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->integrationMapperCollectionFactory = $integrationMapperCollectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    $mapper = $this->integrationMapperCollectionFactory->create()
                            ->addFieldToFilter('entity_id',$item['entity_id'])
                            ->addFieldToFilter('entity_type', 'order')
                            ->getFirstItem();
                    if ($mapper->getId()) {
                        $item[$name] = 'yes';
                    } else {
                        $item[$name] = 'no';
                    }
                }
            }
        }
        return $dataSource;
    }
}
