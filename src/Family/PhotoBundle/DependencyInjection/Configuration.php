<?php

namespace Family\PhotoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('family_photo');

        $rootNode
            ->children()
                ->scalarNode('site_title')
                    ->defaultValue('Family Photos')
                ->end()
                ->scalarNode('path')
                    ->defaultValue('photos')
                ->end()
                ->scalarNode('web')
                    ->defaultValue('%kernel.root_dir%/../web')
                ->end()
            ->end();

        return $treeBuilder;
    }
}
