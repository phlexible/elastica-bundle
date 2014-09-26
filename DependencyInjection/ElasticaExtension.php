<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\ElasticaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Elastica extension
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class ElasticaExtension extends Extension
{
    public function load(ContainerBuilder $container, array $configs)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        //$loader->load('services.yml');

        $configuration = $this->getConfiguration($config, $container);
        $config = $this->processConfiguration($configuration, $config);

        if (empty($config['clients'])) {
            throw new \InvalidArgumentException('You must define at least one client');
        }

        if (empty($config['default_client'])) {
            $keys = array_keys($config['clients']);
            $config['default_client'] = reset($keys);
        }

        $clientIdsByName = $this->loadClients($config['clients'], $container);

        $container->setAlias('elasticaClient', sprintf('elasticaClient%s', ucfirst(strtolower($config['default_client']))));
    }

    /**
     * Loads the configured clients.
     *
     * @param array $clients An array of clients configurations
     * @param ContainerBuilder $container A ContainerBuilder instance
     * @return array
     */
    private function loadClients(array $clients, ContainerBuilder $container)
    {
        $clientIds = array();
        foreach ($clients as $name => $clientConfig) {
            $clientId = sprintf('elasticaClient%s', ucfirst(strtolower($name)));
            $clientDef = new Definition('Elastica\Client', array($clientConfig));
            $logger = $clientConfig['servers'][0]['logger'];
            if (false !== $logger) {
                $clientDef->addCall(new Call('setLogger', array(new Reference($logger))));
            }

            $container->setDefinition($clientId, $clientDef);

            $clientIds[$name] = $clientId;
        }

        return $clientIds;
    }

}
