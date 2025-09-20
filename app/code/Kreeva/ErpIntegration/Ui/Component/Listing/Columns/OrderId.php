<?php

namespace Kreeva\ErpIntegration\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\Data\Form\FormKey;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderId extends Column
{

    private $urlBuilder;
    private $formKey;

    protected $orderRepository;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        OrderRepositoryInterface $orderRepository,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        $orderEntities = ['order','order_update','invoice','shipment','order_cancel'];
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['entity_id']) && isset($item['entity_type'])) {
                    if (in_array($item['entity_type'], $orderEntities)) {
                        $order = $this->orderRepository->get($item['entity_id']);
                        $item[$name] = $order->getIncrementId();
                    } else {
                        $item[$name] = null;
                    }
                }
            }
        }
        return $dataSource;
    }
}
