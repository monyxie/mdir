<?php

namespace Monyxie\Mdir;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('database');

        $treeBuilder->getRootNode()
            ->children()
            ->booleanNode('debug')->defaultTrue()->end()
            ->scalarNode('app_name')->defaultValue('MDir')->end()
            ->scalarNode('dir')->defaultValue(__DIR__ . '/../markdown')->end()
            ->scalarNode('ext')->defaultValue('md')->end()
            ->end();

        return $treeBuilder;
    }
}