<?php

namespace Kreeva\Freshchat\Model;

use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ApiClient
{
    const FRESHCHAT_API_URL = 'freshchat/general/apiurl';
    
    const FRESHCHAT_API_KEY = 'freshchat/general/apikey';
    
    const MESSAGE_TEMPLATE_ID = 'cart_abandonment_updated';
    
    const MESSAGE_NAMESPACE = '808797ef_e523_48ff_abfd_c1dc763d8e66';
    
    const MESSAGE_PHONE_NO = '+919328361778';

    /**
     * @var Curl
     */
    protected $curlClient;
    /**
     * @var string
     */
    protected $apiUrl;
    
    protected $apiKey;
    
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
            $this->apiUrl = $this->scopeConfig->getValue(self::FRESHCHAT_API_URL, ScopeInterface::SCOPE_STORE);
        }
        return $this->apiUrl;
    }
    
    public function getApiKey()
    {
        if (!$this->apiKey) {
            $this->apiKey = $this->scopeConfig->getValue(self::FRESHCHAT_API_KEY, ScopeInterface::SCOPE_STORE);
        }
        return $this->apiKey;
    }
    
    public function sendMessage($messageData)
    {
        $apiUrl = $this->getApiUrl().'v2/outbound-messages/whatsapp';
        $params = [];
        $params = ['from' => ["phone_number" => self::MESSAGE_PHONE_NO],
                    'provider' => 'whatsapp',
                    'to' => [["phone_number" => $messageData['phone']]],
                    'data' => ["message_template" => [
                                "storage" => "none",
                                "template_name" => self::MESSAGE_TEMPLATE_ID,
                                "namespace" => self::MESSAGE_NAMESPACE,
                                "language" => ["policy" => "deterministic", "code" => "en"],
                                "rich_template_data" => [
                                    "body" => [
                                        "params" => [
                                            ["data" => $messageData['name']],
                                            [                                          
                                            "data" => $messageData['purchaseLink'],
                                            ]
                                        ]
                                    ]
                                ]
                    ]]
                ];
        try {
            $curlClient = $this->getCurlClient()->create();
            $curlClient->addHeader('accept', 'application/json');
            $curlClient->addHeader('Content-Type', 'application/json');
            $curlClient->addHeader('Authorization', 'Bearer '.$this->getApiKey());
            $curlClient->post($apiUrl, json_encode($params));
            $response = json_decode($curlClient->getBody(), true);
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

    /**
     * @return Curl
     */
    public function getCurlClient()
    {
        return $this->curlClient;
    }
    
}