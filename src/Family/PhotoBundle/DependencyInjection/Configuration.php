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
                    ->defaultValue('%kernel.root_dir%/../var/photos')
                ->end()
                ->scalarNode('cache')
                    ->defaultValue('%kernel.root_dir%/../var/photos-cache')
                ->end()
                ->arrayNode('presets')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('w')->end()
                            ->scalarNode('h')->end()
                            ->scalarNode('dpr')->end()
                            ->scalarNode('fit')->end()
                        ->end()
                    ->end()
                    ->defaultValue([
                        'thumbnail' => ['w' => 720, 'h' => 480, 'dpr' => 1, 'fit' => 'crop'],
                        'full' => ['w' => 1920, 'h' => 1280, 'dpr' => 1, 'fit' => 'contain'],
                    ])
                ->end()
            ->end();

        return $treeBuilder;
    }
}
