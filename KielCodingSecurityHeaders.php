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
        $container->setParameter($this->getContainerPrefix() . '.plugin_dir', $this->getPath());
        $container->setParameter($this->getContainerPrefix() . '.plugin_name', $this->getName());

        parent::build($container);
    }
}
