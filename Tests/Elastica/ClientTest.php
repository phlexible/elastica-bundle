<?php

/*
 * This file is part of the phlexible elastica package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\ElasticaBundle\Tests\Elastica;

use Elastica\Connection;
use Elastica\Request;
use Elastica\Response;
use Elastica\Transport\NullTransport;
use Phlexible\Bundle\ElasticaBundle\Elastica\Client;
use Phlexible\Bundle\ElasticaBundle\Elastica\Index;
use Phlexible\Bundle\ElasticaBundle\Logger\ElasticaLogger;
use Prophecy\Argument;

/**
 * Client test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\ElasticaBundle\Elastica\Client
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testRequestsAreLogged()
    {
        $transport = new NullTransport();

        $connection = $this->prophesize(Connection::class);
        $connection->hasConfig('headers')->willReturn(false);
        $connection->getTransport()->willReturn($transport);
        $connection->getPort()->willReturn(123);
        $connection->getHost()->willReturn('foo');
        $connection->getParams()->willReturn(array());
        $connection->isEnabled()->willReturn(true);
        $connection->getTransportObject()->willReturn($transport);
        $connection->toArray()->willReturn(array());

        $logger = $this->prophesize(ElasticaLogger::class);
        $logger
            ->logQuery(
                'foo',
                Request::GET,
                Argument::type('array'),
                Argument::type('float'),
                Argument::type('array'),
                Argument::type('array')
            );

        $client = new Client();
        $client->setLogger($logger->reveal());
        $client->addConnection($connection->reveal());

        $response = $client->request('foo');

        $this->assertInstanceOf(Response::class, $response);
    }

    public function testReturnsOurIndex()
    {
        $client = new Client();
        $result = $client->getIndex('foo');

        $this->assertInstanceOf(Index::class, $result);
    }

    public function testGetIndexUsesCache()
    {
        $client = new Client();
        $result1 = $client->getIndex('foo');
        $result2 = $client->getIndex('foo');

        $this->assertSame($result1, $result2);
    }
}
