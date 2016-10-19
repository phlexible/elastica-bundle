<?php

/*
 * This file is part of the phlexible elastica package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\ElasticaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Container configuration.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * If the kernel is running in debug mode.
     *
     * @var bool
     */
    private $debug;

    public function __construct($debug)
    {
        $this->debug = $debug;
    }

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('phlexible_elastica');

        $this->addClientsSection($rootNode);
        $this->addIndexesSection($rootNode);

        $rootNode
            ->children()
                ->scalarNode('default_client')
                    ->info('Defaults to the first client defined')
                ->end()
                ->scalarNode('default_index')
                    ->info('Defaults to the first index defined')
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Adds the configuration for the "clients" key.
     */
    private function addClientsSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('client')
            ->children()
                ->arrayNode('clients')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->performNoDeepMerging()
                        // BC - Renaming 'servers' node to 'connections'
                        ->beforeNormalization()
                            ->ifTrue(function ($v) {
                                return isset($v['servers']);
                            })
                            ->then(function ($v) {
                                $v['connections'] = $v['servers'];
                                unset($v['servers']);

                                return $v;
                            })
                        ->end()
                        // Elastica names its properties with camel case, support both
                        ->beforeNormalization()
                            ->ifTrue(function ($v) {
                                return isset($v['connection_strategy']);
                            })
                            ->then(function ($v) {
                                $v['connectionStrategy'] = $v['connection_strategy'];
                                unset($v['connection_strategy']);

                                return $v;
                            })
                        ->end()
                        // If there is no connections array key defined, assume a single connection.
                        ->beforeNormalization()
                            ->ifTrue(function ($v) {
                                return is_array($v) && !array_key_exists('connections', $v);
                            })
                            ->then(function ($v) {
                                return array(
                                    'connections' => array($v),
                                );
                            })
                        ->end()
                        ->children()
                            ->arrayNode('connections')
                                ->requiresAtLeastOneElement()
                                ->prototype('array')
                                    ->fixXmlConfig('header')
                                    ->children()
                                        ->scalarNode('url')
                                            ->validate()
                                                ->ifTrue(function ($url) {
                                                    return $url && substr($url, -1) !== '/';
                                                })
                                                ->then(function ($url) {
                                                    return $url.'/';
                                                })
                                            ->end()
                                        ->end()
                                        ->scalarNode('host')->end()
                                        ->scalarNode('port')->end()
                                        ->scalarNode('proxy')->end()
                                        ->scalarNode('logger')
                                            ->defaultValue($this->debug ? 'phlexible_elastica.logger' : false)
                                            ->treatNullLike('phlexible_elastica.logger')
                                            ->treatTrueLike('phlexible_elastica.logger')
                                        ->end()
                                        ->booleanNode('compression')->defaultValue(false)->end()
                                        ->arrayNode('headers')
                                            ->useAttributeAsKey('name')
                                            ->prototype('scalar')->end()
                                        ->end()
                                        ->scalarNode('transport')->end()
                                        ->scalarNode('timeout')->end()
                                        ->scalarNode('connectTimeout')->end()
                                        ->scalarNode('retryOnConflict')
                                            ->defaultValue(0)
                                        ->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('timeout')->end()
                            ->scalarNode('connectTimeout')->end()
                            ->scalarNode('headers')->end()
                            ->scalarNode('connectionStrategy')->defaultValue('Simple')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Adds the configuration for the "indexes" key.
     */
    private function addIndexesSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->fixXmlConfig('index')
            ->children()
                ->arrayNode('indexes')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('index_name')
                                ->info('Defaults to the name of the index, but can be modified if the index name is different in ElasticSearch')
                            ->end()
                            ->booleanNode('use_alias')->defaultValue(false)->end()
                            ->scalarNode('client')->end()
                            ->variableNode('settings')->defaultValue(array())->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
