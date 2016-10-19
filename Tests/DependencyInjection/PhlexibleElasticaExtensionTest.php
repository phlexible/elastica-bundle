<?php

/*
 * This file is part of the phlexible elastica package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\ElasticaBundle\Tests\DependencyInjection;

use Phlexible\Bundle\ElasticaBundle\DependencyInjection\PhlexibleElasticaExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;

class PhlexibleElasticaExtensionTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicConfig()
    {
        $yaml = <<<EOF
phlexible_elastica:
    clients:
        test_client:
            url: http://localhost:9200
    indexes:
        test_index:
            client: test_client
EOF;

        $config = Yaml::parse($yaml);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', true);

        $extension = new PhlexibleElasticaExtension();

        $extension->load($config, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('phlexible_elastica.client.test_client'));
        $this->assertTrue($containerBuilder->hasAlias('phlexible_elastica.client'));
        $this->assertTrue($containerBuilder->hasDefinition('phlexible_elastica.index.test_index'));
        $this->assertTrue($containerBuilder->hasAlias('phlexible_elastica.index'));
    }
    public function testDefaultClientAndIndexConfig()
    {
        $yaml = <<<EOF
phlexible_elastica:
    default_client: test_client2
    clients:
        test_client1:
            url: http://localhost:9200
        test_client2:
            url: http://localhost:8200
    default_index: test_index2
    indexes:
        test_index1:
            client: test_client1
        test_index2:
            client: test_client2
EOF;

        $config = Yaml::parse($yaml);

        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setParameter('kernel.debug', true);

        $extension = new PhlexibleElasticaExtension();

        $extension->load($config, $containerBuilder);

        $this->assertTrue($containerBuilder->hasDefinition('phlexible_elastica.client.test_client1'));
        $this->assertTrue($containerBuilder->hasDefinition('phlexible_elastica.client.test_client2'));
        $this->assertTrue($containerBuilder->hasAlias('phlexible_elastica.client'));
        $this->assertSame('phlexible_elastica.client.test_client2', (string) $containerBuilder->getAlias('phlexible_elastica.client'));
        $this->assertTrue($containerBuilder->hasDefinition('phlexible_elastica.index.test_index1'));
        $this->assertTrue($containerBuilder->hasDefinition('phlexible_elastica.index.test_index2'));
        $this->assertTrue($containerBuilder->hasAlias('phlexible_elastica.index'));
        $this->assertSame('phlexible_elastica.index.test_index2', (string) $containerBuilder->getAlias('phlexible_elastica.index'));
    }
}
