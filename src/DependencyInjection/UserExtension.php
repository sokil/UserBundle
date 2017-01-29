<?php

namespace Sokil\UserBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class UserExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // get global config
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // registration
        if (!empty($config['registration']['id'])) {
            $container->set('user.action.register', $config['registration']['id']);
        }

        // load config from files
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
