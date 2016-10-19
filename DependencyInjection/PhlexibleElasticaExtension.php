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

use InvalidArgumentException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Elastica extension.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class PhlexibleElasticaExtension extends Extension
{
    /**
     * Definition of elastica clients as configured by this extension.
     *
     * @var array
     */
    private $clients = array();

    /**
     * An array of indexes as configured by the extension.
     *
     * @var array
     */
    private $indexConfigs = array();

    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $configuration = $this->getConfiguration($config, $container);
        $config = $this->processConfiguration($configuration, $config);

        if (empty($config['clients']) || empty($config['indexes'])) {
            throw new InvalidArgumentException('You must define at least one client and index');
        }

        foreach (array('config', 'index') as $basename) {
            $loader->load(sprintf('%s.yml', $basename));
        }

        if (empty($config['default_client'])) {
            $keys = array_keys($config['clients']);
            $config['default_client'] = reset($keys);
        }

        if (empty($config['default_index'])) {
            $keys = array_keys($config['indexes']);
            $config['default_index'] = reset($keys);
        }

        $this->loadClients($config['clients'], $container);
        $container->setAlias('phlexible_elastica.client', sprintf('phlexible_elastica.client.%s', $config['default_client']));

        $this->loadIndexes($config['indexes'], $container);
        $container->setAlias('phlexible_elastica.index', sprintf('phlexible_elastica.index.%s', $config['default_index']));
    }

    /**
     * @param array            $config
     * @param ContainerBuilder $container
     *
     * @return Configuration
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }

    /**
     * Loads the configured clients.
     *
     * @param array            $clients   An array of clients configurations
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @return array
     */
    private function loadClients(array $clients, ContainerBuilder $container)
    {
        foreach ($clients as $name => $clientConfig) {
            $clientId = sprintf('phlexible_elastica.client.%s', $name);
            $clientDef = new DefinitionDecorator('phlexible_elastica.client_prototype');
            $clientDef->replaceArgument(0, $clientConfig);
            $logger = $clientConfig['connections'][0]['logger'];
            if (false !== $logger) {
                $clientDef->addMethodCall('setLogger', array(new Reference($logger)));
            }
            $clientDef->addTag('phlexible_elastica.client');
            $container->setDefinition($clientId, $clientDef);
            $this->clients[$name] = array(
                'id' => $clientId,
                'reference' => new Reference($clientId),
            );
        }
    }

    /**
     * Loads the configured indexes.
     *
     * @param array            $indexes   An array of indexes configurations
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws InvalidArgumentException
     *
     * @return array
     */
    private function loadIndexes(array $indexes, ContainerBuilder $container)
    {
        foreach ($indexes as $name => $index) {
            $indexId = sprintf('phlexible_elastica.index.%s', $name);
            $indexName = isset($index['index_name']) ? $index['index_name'] : $name;
            $indexDef = new DefinitionDecorator('phlexible_elastica.index_prototype');
            $indexDef->replaceArgument(0, $indexName);
            $indexDef->addTag('phlexible_elastica.index', array(
                'name' => $name,
            ));
            if (method_exists($indexDef, 'setFactory')) {
                $indexDef->setFactory(array(new Reference('phlexible_elastica.client'), 'getIndex'));
            } else {
                // To be removed when dependency on Symfony DependencyInjection is bumped to 2.6
                $indexDef->setFactoryService('phlexible_elastica.client');
                $indexDef->setFactoryMethod('getIndex');
            }
            if (isset($index['client'])) {
                $client = $this->getClient($index['client']);
                if (method_exists($indexDef, 'setFactory')) {
                    $indexDef->setFactory(array($client, 'getIndex'));
                } else {
                    // To be removed when dependency on Symfony DependencyInjection is bumped to 2.6
                    $indexDef->setFactoryService('phlexible_elastica.client');
                    $indexDef->setFactoryMethod('getIndex');
                }
            }
            $container->setDefinition($indexId, $indexDef);
            $reference = new Reference($indexId);
            $this->indexConfigs[$name] = array(
                'elasticsearch_name' => $indexName,
                'reference' => $reference,
                'name' => $name,
                'settings' => $index['settings'],
                'type_prototype' => isset($index['type_prototype']) ? $index['type_prototype'] : array(),
                'use_alias' => $index['use_alias'],
            );
        }
    }

    /**
     * Returns a reference to a client given its configured name.
     *
     * @param string $clientName
     *
     * @return Reference
     *
     * @throws InvalidArgumentException
     */
    private function getClient($clientName)
    {
        if (!array_key_exists($clientName, $this->clients)) {
            throw new InvalidArgumentException(sprintf('The elastica client with name "%s" is not defined', $clientName));
        }

        return $this->clients[$clientName]['reference'];
    }
}
