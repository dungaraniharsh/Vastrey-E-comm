<?php

namespace Kreeva\ErpIntegration\Model;

use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ApiClient
{
    const INTEGRATION_API_URL = 'erpintegration/general/apiurl';

    const INTEGRATION_API_KEY = 'erpintegration/general/apikey';

    const INTEGRATION_COMPANY_ID = 'erpintegration/general/companyid';

    const INTEGRATION_CHANNEL_ID = 'erpintegration/general/channelid';

    /**
     * @var Curl
     */
    protected $curlClient;
    /**
     * @var string
     */
    protected $apiUrl;

    protected $apiKey;

    protected $companyId;

    protected $channelId;

    protected $scopeConfig;

    private $logger;

    /**
     * @param Curl $curl
     */
    public function __construct(
        CurlFactory $curl,
        ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->curlClient = $curl;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        if (!$this->apiUrl) {
            $this->apiUrl = $this->scopeConfig->getValue(self::INTEGRATION_API_URL, ScopeInterface::SCOPE_STORE);
        }
        return $this->apiUrl;
    }

    public function getApiKey()
    {
        if (!$this->apiKey) {
            $this->apiKey = $this->scopeConfig->getValue(self::INTEGRATION_API_KEY, ScopeInterface::SCOPE_STORE);
        }
        return $this->apiKey;
        //return $this->apiKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjYxNWU4ZjhiZmJlZjUzMTFmYWJhYTMxNSIsIm5hbWUiOiJLcmVldmEiLCJlbWFpbCI6ImtyZWV2YUBhcnlhZGVzaWducy5jby5pbiIsImlhdCI6MTYzMzU4NzA4NCwiZXhwIjoxNjM4NzcxMDg0fQ.xP4lOGK_aS6Uoal0AYSfcLMfzeimsGOPXsmSv1zbvBc';
    }

    public function getCompanyId()
    {
        if (!$this->companyId) {
            $this->companyId = $this->scopeConfig->getValue(self::INTEGRATION_COMPANY_ID, ScopeInterface::SCOPE_STORE);
        }
        return $this->companyId;
    }

    public function getChannelId()
    {
        if (!$this->channelId) {
            $this->channelId = $this->scopeConfig->getValue(self::INTEGRATION_CHANNEL_ID, ScopeInterface::SCOPE_STORE);
        }
        return $this->channelId;
    }

    public function sendPostReqest($requestEndPoint, $requestData)
    {
        $requestEndPoint = str_replace('{{ChannelId}}', $this->getChannelId(), $requestEndPoint);
        $apiUrl = $this->getApiUrl().$requestEndPoint;
        try {
            $curlClient = $this->getCurlClient()->create();
            $curlClient->addHeader('accept', 'application/json');
            $curlClient->addHeader('Content-Type', 'application/json');
            $curlClient->addHeader('Authorization', 'Bearer '.$this->getApiKey());
            $curlClient->post($apiUrl, json_encode($requestData));
            $response = json_decode($curlClient->getBody(), true);

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/er-reponse.log');
  $logger = new \Zend\Log\Logger();
  $logger->addWriter($writer);
  $logger->info(print_r($apiUrl, true));
  $logger->info(print_r($response, true));
            if ($response) {
                return $response;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->critical('Error message', ['exception' => $e]);
            return false;
        }
    }

    public function sendPutRequest($requestEndPoint, $requestData)
    {
        $url = $this->getApiUrl().$requestEndPoint;
        try {
            /*$curlClient = $this->getCurlClient()->create();
            $curlClient->addHeader('accept', 'application/json');
            $curlClient->addHeader('Content-Type', 'application/json');
            $curlClient->addHeader('Authorization', 'Bearer '.$this->getApiKey());
            $curlClient->setOption(CURLOPT_CUSTOMREQUEST, 'put');
            $curlClient->setOption(CURLOPT_POSTFIELDS, is_array($requestData) ? http_build_query($requestData) : $requestData);
            $curlClient->put($apiUrl, json_encode($requestData));*/

            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.$this->getApiKey(),
            'Content-Type: application/json'
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/er-reponse.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info(print_r($response, true));

            if ($response) {
                return json_decode($response, true);
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->critical('Error message', ['exception' => $e]);
            return false;
        }
    }

    /**
     * @return Curl
     */
    public function getCurlClient()
    {
        return $this->curlClient;
    }

}
