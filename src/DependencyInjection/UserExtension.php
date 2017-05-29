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
            $roles = empty($config['registration']['security']['roles'])
                ? 'ROLE_USER'
                : $config['registration']['security']['roles'];

            $container->setParameter('user.registration.security.roles', $roles);

            // firewall to auth registered user
            $firewall = empty($config['registration']['security']['firewall'])
                ? 'main'
                : $config['registration']['security']['firewall'];

            $container->setParameter('user.registration.security.firewall', $firewall);
        }

        // load config from files
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
