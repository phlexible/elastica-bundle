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

        $connection = $this->getMock(Connection::class);
        $connection->expects($this->any())->method('getTransportObject')->will($this->returnValue($transport));
        $connection->expects($this->any())->method('toArray')->will($this->returnValue(array()));

        $logger = $this->getMock(ElasticaLogger::class);
        $logger
            ->expects($this->once())
            ->method('logQuery')
            ->with(
                'foo',
                Request::GET,
                $this->isType('array'),
                $this->isType('float'),
                $this->isType('array'),
                $this->isType('array')
            );

        $client = $this->getMockBuilder(Client::class)
            ->setMethods(array('getConnection'))
            ->getMock();

        $client->expects($this->any())->method('getConnection')->will($this->returnValue($connection));

        $client->setLogger($logger);

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
