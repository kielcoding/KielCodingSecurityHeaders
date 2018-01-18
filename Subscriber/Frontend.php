<?php

namespace KielCodingSecurityHeaders\Subscriber;

use Enlight\Event\SubscriberInterface;
use Shopware\Models\Shop\DetachedShop;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Frontend implements SubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var
     */
    private $config;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $this->getPluginConfig();
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
        $response = $args->getResponse();

        $this->setSecurityHeaders($response);
        $this->setCustomHeaders($response);
        $this->removeInsecureHeaders($response);
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
        if ($this->config['contentSecurityPolicyEnabled'] && $this->isSecure()) {
            if ($this->config['contentSecurityPolicyDebug']) {
                $response->setHeader('Content-Security-Policy', $this->config['contentSecurityPolicy']);
            } else {
                $response->setHeader('Content-Security-Policy-Report-Only', $this->config['contentSecurityPolicy']);
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

    /**
     * @param \Enlight_Controller_Response_ResponseHttp $response
     */
    private function removeInsecureHeaders(\Enlight_Controller_Response_ResponseHttp $response)
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

        $headers = explode('\n', $this->config['customHeaders']);

        $headersFormatted = [];
        foreach ($headers as $header) {
            $headerParts = explode(':', $header);
            $headersFormatted[$headerParts[0]] = $headerParts[1];
        }

        return $headersFormatted;
    }

    /**
     * @return array
     */
    private function getPluginConfig()
    {
        $pluginName = $this->container->getParameter('kiel_coding_security_headers.plugin_name');

        return $this->container->get('shopware.plugin.cached_config_reader')->getByPluginName($pluginName);
    }

    /**
     * @return bool
     */
    private function isSecure()
    {
        /** @var DetachedShop $shop */
        $shop = $this->container->get('shop');

        return $shop->getSecure();
    }
}