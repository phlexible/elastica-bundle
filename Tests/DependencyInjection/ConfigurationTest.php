<?php

namespace Phlexible\Bundle\ElasticaBundle\Tests\DependencyInjection;

use Phlexible\Bundle\ElasticaBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;

/**
 * ConfigurationTest.
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Processor
     */
    private $processor;

    public function setUp()
    {
        $this->processor = new Processor();
    }

    private function getConfigs(array $configArray)
    {
        $configuration = new Configuration(true);

        return $this->processor->processConfiguration($configuration, array($configArray));
    }

    public function testUnconfiguredConfiguration()
    {
        $configuration = $this->getConfigs(array());

        $this->assertSame(array(
            'clients' => array(),
            'indexes' => array(),
        ), $configuration);
    }

    public function testClientConfiguration()
    {
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'default' => array(
                    'url' => 'http://localhost:9200',
                    'retryOnConflict' => 5,
                ),
                'clustered' => array(
                    'connections' => array(
                        array(
                            'url' => 'http://es1:9200',
                            'headers' => array(
                                'Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
                            ),
                        ),
                        array(
                            'url' => 'http://es2:9200',
                            'headers' => array(
                                'Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==',
                            ),
                        ),
                    ),
                ),
            ),
        ));

        $this->assertCount(2, $configuration['clients']);
        $this->assertCount(1, $configuration['clients']['default']['connections']);
        $this->assertCount(0, $configuration['clients']['default']['connections'][0]['headers']);
        $this->assertEquals(5, $configuration['clients']['default']['connections'][0]['retryOnConflict']);

        $this->assertCount(2, $configuration['clients']['clustered']['connections']);
        $this->assertEquals('http://es2:9200/', $configuration['clients']['clustered']['connections'][1]['url']);
        $this->assertCount(1, $configuration['clients']['clustered']['connections'][1]['headers']);
        $this->assertEquals('Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==', $configuration['clients']['clustered']['connections'][0]['headers'][0]);
    }

    public function testLogging()
    {
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'logging_enabled' => array(
                    'url' => 'http://localhost:9200',
                    'logger' => true,
                ),
                'logging_disabled' => array(
                    'url' => 'http://localhost:9200',
                    'logger' => false,
                ),
                'logging_not_mentioned' => array(
                    'url' => 'http://localhost:9200',
                ),
                'logging_custom' => array(
                    'url' => 'http://localhost:9200',
                    'logger' => 'custom.service',
                ),
            ),
        ));

        $this->assertCount(4, $configuration['clients']);

        $this->assertEquals('phlexible_elastica.logger', $configuration['clients']['logging_enabled']['connections'][0]['logger']);
        $this->assertFalse($configuration['clients']['logging_disabled']['connections'][0]['logger']);
        $this->assertEquals('phlexible_elastica.logger', $configuration['clients']['logging_not_mentioned']['connections'][0]['logger']);
        $this->assertEquals('custom.service', $configuration['clients']['logging_custom']['connections'][0]['logger']);
    }

    public function testSlashIsAddedAtTheEndOfServerUrl()
    {
        $config = array(
            'clients' => array(
                'default' => array('url' => 'http://www.github.com'),
            ),
        );
        $configuration = $this->getConfigs($config);

        $this->assertEquals('http://www.github.com/', $configuration['clients']['default']['connections'][0]['url']);
    }

    public function testClientConfigurationNoUrl()
    {
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'default' => array(
                    'host' => 'localhost',
                    'port' => 9200,
                ),
            ),
        ));

        $this->assertTrue(empty($configuration['clients']['default']['connections'][0]['url']));
    }

    public function testCompressionConfig()
    {
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'compression_enabled' => array(
                    'compression' => true,
                ),
                'compression_disabled' => array(
                    'compression' => false,
                ),
            ),
        ));

        $this->assertTrue($configuration['clients']['compression_enabled']['connections'][0]['compression']);
        $this->assertFalse($configuration['clients']['compression_disabled']['connections'][0]['compression']);
    }

    public function testCompressionDefaultConfig()
    {
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'default' => array(),
            ),
        ));

        $this->assertFalse($configuration['clients']['default']['connections'][0]['compression']);
    }

    public function testTimeoutConfig()
    {
        $configuration = $this->getConfigs(array(
            'clients' => array(
                'simple_timeout'       => array(
                    'url'    => 'http://localhost:9200',
                    'timeout' => 123,
                ),
                'connect_timeout'      => array(
                    'url'    => 'http://localhost:9200',
                    'connectTimeout' => 234,
                ),
            ),
        ));

        $this->assertEquals(123, $configuration['clients']['simple_timeout']['connections'][0]['timeout']);
        $this->assertEquals(234, $configuration['clients']['connect_timeout']['connections'][0]['connectTimeout']);
    }
}
