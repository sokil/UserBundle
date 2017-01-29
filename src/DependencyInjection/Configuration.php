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
                        ->scalarNode('id')
                            ->defaultValue('user.action.register')
                        ->end()
                    ->end()
            ->end();

        return $treeBuilder;
    }
}
