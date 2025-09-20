<?php

namespace PayUIndia\Payu\Controller\Standard;

class Response extends \PayUIndia\Payu\Controller\PayuAbstract {

    public function execute() {
        $returnUrl = $this->getCheckoutHelper()->getUrl('checkout');

        try {
            $paymentMethod = $this->getPaymentMethod();
            $params = $this->getRequest()->getParams();
			if (isset($params['udf1']) && !empty($params['udf1'])) {
				$this->getCheckoutSession()->setSessionId($params['udf1']);
				$this->getCustomerSession()->setSessionId($params['udf1']);
			}
            if ($paymentMethod->validateResponse($params)) {

                $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/success');
				$order = $this->getOrderById($params['txnid']);								
				$payment = $order->getPayment();
				$paymentMethod->postProcessing($order, $payment, $params);
            } else {
                $this->messageManager->addErrorMessage(__('Payment failed. Please try again or choose a different payment method'));
                $returnUrl = $this->getCheckoutHelper()->getUrl('checkout/onepage/failure');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
			//echo $e->getMessage();
            $this->messageManager->addExceptionMessage($e, $e->getMessage());
        } catch (\Exception $e) {
			//echo $e->getMessage();
            $this->messageManager->addExceptionMessage($e, __('We can\'t place the order.'));			
        }

        $this->getResponse()->setRedirect($returnUrl);
    }

}
