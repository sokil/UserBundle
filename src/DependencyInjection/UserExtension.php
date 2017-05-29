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

        // if registration allowed
        if (!empty($config['registration'])) {
            // define roles, added to user on registration
            $container->setParameter('user.registration.security.roles', $config['registration']['security']['roles']);

            // firewall to auth registered user
            $container->setParameter('user.registration.security.firewall', $config['registration']['security']['firewall']);
        }

        // load config from files
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
