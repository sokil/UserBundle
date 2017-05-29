<?php

namespace Sokil\UserBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('user');

        $rootNode
            ->children()
                ->arrayNode('registration')
                    ->children()
                        ->arrayNode('security')
                            ->children()
                                ->scalarNode('firewall')->end()
                                ->arrayNode('roles')->prototype('variable')->end()
                            ->end()
                        ->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}
