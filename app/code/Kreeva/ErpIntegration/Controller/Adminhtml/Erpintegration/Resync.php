<?php

namespace Kreeva\ErpIntegration\Controller\Adminhtml\Erpintegration;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Kreeva\ErpIntegration\Model\ErpintegrationFactory;
use Kreeva\ErpIntegration\Model\ResourceModel\Erpintegration\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Kreeva\ErpIntegration\Model\Order\SyncWebhookPublisher;

class Resync extends Action
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $erpMgtFactory;

    protected $orderRepository;

    protected $erpMgtCollectionFactory;

    protected $syncWebhookPublisher;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        OrderRepositoryInterface $orderRepository,
        ErpintegrationFactory $erpMgtFactory,
        Filter $filter,
        CollectionFactory $erpMgtCollectionFactory,
        SyncWebhookPublisher $syncWebhookPublisher
    ) {
        $this->logger = $logger;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->erpMgtFactory = $erpMgtFactory;
        $this->orderRepository = $orderRepository;
        $this->erpMgtCollectionFactory = $erpMgtCollectionFactory;
        $this->filter = $filter;
        $this->syncWebhookPublisher = $syncWebhookPublisher;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = [];
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = $this->getRequest()->getParams();
        $erpMgtCollection = $this->filter->getCollection($this->erpMgtCollectionFactory->create());
        $orderEntities = ['order_update','invoice','shipment'];

        try {
            foreach ($erpMgtCollection as $erpMgtModel) {
                if ($erpMgtModel) {
                    if ($erpMgtModel->getData('entity_type') == 'order') {
                        $order = $this->orderRepository->get($erpMgtModel->getData('entity_id'));
                        $this->orderRepository->save($order);
                        $erpMgtModel->setData('status','Resync');
                        $erpMgtModel->save();
                    } else if (in_array($erpMgtModel->getData('entity_type'), $orderEntities)) {
                        $syncData = json_decode(json_decode($erpMgtModel->getData('sync_data'), true), true);
                        $this->syncWebhookPublisher->execute(json_encode($syncData));
                    }
                }
            }

            $this->messageManager->addSuccess(__('Message queue added successfully.'));
        } catch (Exception $ex) {
            $this->messageManager->addSuccess(__($ex->getMessage().' Please try again.'));
        }
        $resultRedirect->setPath(
                'erpintegration/erpintegration/index',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        return $resultRedirect;
    }
}
