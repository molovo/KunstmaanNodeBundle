<?php

namespace Kunstmaan\NodeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class KunstmaanNodeExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $container->setParameter('twig.form.resources', array_merge(
            $container->getParameter('twig.form.resources'),
            array('KunstmaanNodeBundle:Form:formWidgets.html.twig')
        ));
        $container->setParameter('version_timeout', $config['version_timeout']);

        $loader->load('services.yml');
    }
}
