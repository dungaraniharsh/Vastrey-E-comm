<?php

namespace PayUIndia\Payu\Controller\Standard;

class Cancel extends \PayUIndia\Payu\Controller\PayuAbstract {

    public function execute() {
		$params = $this->getRequest()->getParams();
		if (isset($params['udf1']) && !empty($params['udf1'])) {
			$this->getCheckoutSession()->setSessionId($params['udf1']);
			$this->getCustomerSession()->setSessionId($params['udf1']);
		}
		$order = $this->getOrderById($params['txnid']);	
        $order->cancel()->save();
        
        $this->messageManager->addErrorMessage(__('Your order has been can cancelled'));
        $this->getResponse()->setRedirect(
                $this->getCheckoutHelper()->getUrl('checkout')
        );
    }

}
