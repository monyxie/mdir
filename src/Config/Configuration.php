<?php

namespace Monyxie\Mdir\Config;

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
            ->scalarNode('markdown_dir')->defaultValue(__DIR__ . '/../markdown')->end()
            ->arrayNode('markdown_extensions')->defaultValue(['md'])->end()
            ->arrayNode('extra_extensions')->defaultValue(['txt'])->end()
            ->end();

        return $treeBuilder;
    }
}