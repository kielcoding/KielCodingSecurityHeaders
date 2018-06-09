<?php

namespace KielCodingSecurityHeaders;

use Shopware\Components\Plugin;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class KielCodingSecurityHeaders extends Plugin
{
    /**
     * @param ActivateContext $context
     */
    public function activate(ActivateContext $context)
    {
        $context->scheduleClearCache(ActivateContext::CACHE_LIST_FRONTEND);
    }

    /**
     * @param DeactivateContext $context
     */
    public function deactivate(DeactivateContext $context)
    {
        $context->scheduleClearCache(DeactivateContext::CACHE_LIST_FRONTEND);
    }

    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        $container->setParameter('kiel_coding_security_headers.plugin_dir', $this->getPluginPath());

        parent::build($container);
    }

    /**
     * Gets the Plugin directory path.
     *
     * @return string The Plugin absolute path
     */
    public function getPluginPath()
    {
        $reflected = new \ReflectionObject($this);

        return dirname($reflected->getFileName());
    }
}
