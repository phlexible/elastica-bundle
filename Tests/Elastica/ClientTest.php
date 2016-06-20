<?php

namespace Phlexible\Bundle\ElasticaBundle\Tests\Client;

use Elastica\Connection;
use Elastica\Request;
use Elastica\Transport\Null as NullTransport;
use Phlexible\Bundle\ElasticaBundle\Elastica\Client;
use Phlexible\Bundle\ElasticaBundle\Logger\ElasticaLogger;

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

        $this->assertInstanceOf('Elastica\Response', $response);
    }
}
