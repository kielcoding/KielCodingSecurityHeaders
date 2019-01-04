<?php

namespace KielCodingSecurityHeaders\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Components\Plugin\CachedConfigReader;

class Frontend implements SubscriberInterface
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param CachedConfigReader $configReader
     * @param string             $pluginName
     */
    public function __construct(CachedConfigReader $configReader, $pluginName)
    {
        $this->config = $configReader->getByPluginName($pluginName, Shopware()->Shop());
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            'Enlight_Controller_Action_PostDispatchSecure_Frontend' => 'onPostDispatch',
        ];
    }

    /**
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        /** @var \Enlight_Controller_Response_ResponseHttp $response */
        $response = $args->getResponse();

        $this->setSecurityHeaders($response);
        $this->setCustomHeaders($response);
        $this->removeInsecureHeaders();
    }

    /**
     * @param \Enlight_Controller_Response_ResponseHttp $response
     */
    private function setSecurityHeaders(\Enlight_Controller_Response_ResponseHttp $response)
    {
        if ($this->config['strictTransportSecurityEnabled']) {
            $response->setHeader('Strict-Transport-Security', $this->config['strictTransportSecurity']);
        }
        if ($this->config['xFrameOptionsEnabled']) {
            $response->setHeader('X-Frame-Options', $this->config['xFrameOptions']);
        }
        if ($this->config['xXssProtectionEnabled']) {
            $response->setHeader('X-XSS-Protection', $this->config['xXssProtection']);
        }
        if ($this->config['xContentTypeOptionsEnabled']) {
            $response->setHeader('X-Content-Type-Options', $this->config['xContentTypeOptions']);
        }
        if ($this->config['referrerPolicyEnabled']) {
            $response->setHeader('Referrer-Policy', $this->config['referrerPolicy']);
        }
        if ($this->config['featureolicyEnabled']) {
            $response->setHeader('Feature-Policy', $this->config['featurePolicy']);
        }
        if ($this->config['contentSecurityPolicyEnabled'] && $this->isSecure()) {
            if ($this->config['contentSecurityPolicyDebug']) {
                $response->setHeader('Content-Security-Policy-Report-Only', $this->config['contentSecurityPolicy']);
            } else {
                $response->setHeader('Content-Security-Policy', $this->config['contentSecurityPolicy']);
            }
        }
    }

    /**
     * @param \Enlight_Controller_Response_ResponseHttp $response
     */
    private function setCustomHeaders(\Enlight_Controller_Response_ResponseHttp $response)
    {
        foreach ($this->getCustomHeaders() as $header => $value) {
            $response->setHeader($header, $value);
        }
    }

    private function removeInsecureHeaders()
    {
        if ($this->config['xPoweredByDisabled']) {
            @ini_set('expose_php', 'off');
        }
    }

    /**
     * @return array
     */
    private function getCustomHeaders()
    {
        if (empty($this->config['customHeaders'])) {
            return [];
        }

        $headers = explode(PHP_EOL, $this->config['customHeaders']);

        $headersFormatted = [];
        foreach ($headers as $header) {
            // Use preg_split with limit to prevent url splitting caused by ":" inside.
            $headerParts = preg_split('/[\\s+:\\s+]/', $header, 2);
            $headersFormatted[$headerParts[0]] = $headerParts[1];
        }

        return $headersFormatted;
    }

    /**
     * @return bool
     */
    private function isSecure()
    {
        return Shopware()->Shop()->getSecure();
    }
}
