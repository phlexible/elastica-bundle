<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\ElasticaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Container configuration
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('phlexible_elastica');

        $this->addClientsSection($rootNode);

        $rootNode
            ->children()
                ->scalarNode('default_client')->end()
                ->arrayNode('serializer')
                    ->treatNullLike(array())
                    ->children()
                        ->scalarNode('callback_class')->defaultValue('FOS\ElasticaBundle\Serializer\Callback')->end()
                        ->scalarNode('serializer')->defaultValue('serializer')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Adds the configuration for the "clients" key
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
                        ->beforeNormalization()
                            ->ifTrue(function($v) { return isset($v['host']) && isset($v['port']); })
                            ->then(function($v) {
                                return array(
                                    'servers' => array(
                                        array(
                                            'host'   => $v['host'],
                                            'port'   => $v['port'],
                                            'logger' => isset($v['logger']) ? $v['logger'] : null
                                        )
                                    )
                                );
                            })
                        ->end()
                        ->beforeNormalization()
                            ->ifTrue(function($v) { return isset($v['url']); })
                            ->then(function($v) {
                                return array(
                                    'servers' => array(
                                        array(
                                            'url'    => $v['url'],
                                            'logger' => isset($v['logger']) ? $v['logger'] : null
                                        )
                                    )
                                );
                            })
                        ->end()
                        ->children()
                            ->arrayNode('servers')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('url')
                                            ->validate()
                                                ->ifTrue(function($v) { return substr($v['url'], -1) !== '/'; })
                                                ->then(function($v) { return $v['url'].'/'; })
                                            ->end()
                                        ->end()
                                        ->scalarNode('host')->end()
                                        ->scalarNode('port')->end()
                                        ->scalarNode('logger')
                                            ->defaultValue('logger')
                                            ->treatNullLike('logger')
                                            ->treatTrueLike('logger')
                                        ->end()
                                        ->scalarNode('timeout')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->scalarNode('timeout')->end()
                            ->scalarNode('headers')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

}