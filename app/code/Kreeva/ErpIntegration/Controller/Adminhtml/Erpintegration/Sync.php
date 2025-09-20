<?php

namespace Kreeva\ErpIntegration\Controller\Adminhtml\Erpintegration;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Kreeva\ErpIntegration\Model\ErpintegrationFactory;
use Kreeva\ErpIntegration\Model\ResourceModel\Erpintegration\CollectionFactory;

class Sync extends Action
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $erpMgtFactory;

    protected $orderRepository;

    protected $erpMgtCollectionFactory;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        OrderRepositoryInterface $orderRepository,
        ErpintegrationFactory $erpMgtFactory,
        CollectionFactory $erpMgtCollectionFactory
    ) {
        $this->logger = $logger;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->erpMgtFactory = $erpMgtFactory;
        $this->orderRepository = $orderRepository;
        $this->erpMgtCollectionFactory = $erpMgtCollectionFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = [];
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['order_id'])) {
                $orderId = $params['order_id'];
                $order = $this->orderRepository->get($orderId);
                $this->orderRepository->save($order);
            }
            $this->messageManager->addSuccess(__('Message queue added successfully.'));
        } catch (Exception $ex) {
            $this->messageManager->addSuccess(__($ex->getMessage().' Please try again.'));
        }
        $resultRedirect->setPath(
                'sales/order/view',
                ['order_id' => $orderId, '_secure' => $this->getRequest()->isSecure()]
            );
        return $resultRedirect;
    }
}
