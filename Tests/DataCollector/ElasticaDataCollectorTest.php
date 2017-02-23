<?php

/*
 * This file is part of the phlexible elastica package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\ElasticaBundle\Tests\DataCollector;

use Phlexible\Bundle\ElasticaBundle\DataCollector\ElasticaDataCollector;
use Phlexible\Bundle\ElasticaBundle\Logger\ElasticaLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Elastica data collector test
 *
 * @covers \Phlexible\Bundle\ElasticaBundle\DataCollector\ElasticaDataCollector
 */
class ElasticaDataCollectorTest extends TestCase
{
    public function testCorrectAmountOfQueries()
    {
        /* @var $request Request */
        $request = $this->prophesize(Request::class);

        /* @var $response Response */
        $response = $this->prophesize(Response::class);

        /* @var $logger ElasticaLogger */
        $logger = $this->prophesize(ElasticaLogger::class);

        $totalQueries = rand();

        $logger->getNbQueries()->willReturn($totalQueries);
        $logger->getQueries()->willReturn(array());

        $elasticaDataCollector = new ElasticaDataCollector($logger->reveal());
        $elasticaDataCollector->collect($request->reveal(), $response->reveal());

        $this->assertEquals($totalQueries, $elasticaDataCollector->getQueryCount());
    }

    public function testCorrectQueriesReturned()
    {
        /* @var $request Request */
        $request = $this->prophesize(Request::class);

        /* @var $response Response */
        $response = $this->prophesize(Response::class);

        /* @var $logger ElasticaLogger */
        $logger = $this->prophesize(ElasticaLogger::class);

        $queries = array('testQueries');

        $logger->getNbQueries()->willReturn(count($queries));
        $logger->getQueries()->willReturn($queries);

        $elasticaDataCollector = new ElasticaDataCollector($logger->reveal());
        $elasticaDataCollector->collect($request->reveal(), $response->reveal());

        $this->assertEquals($queries, $elasticaDataCollector->getQueries());
    }

    public function testCorrectQueriesTime()
    {
        /* @var $request Request */
        $request = $this->prophesize(Request::class);

        /* @var $response Response */
        $response = $this->prophesize(Response::class);

        /* @var $logger ElasticaLogger */
        $logger = $this->prophesize(ElasticaLogger::class);

        $queries = array(
            array(
                'engineMS' => 15,
                'executionMS' => 10,
            ),
            array(
                'engineMS' => 25,
                'executionMS' => 20,
            ),
        );

        $logger->getNbQueries()->willReturn(count($queries));
        $logger->getQueries()->willReturn($queries);

        $elasticaDataCollector = new ElasticaDataCollector($logger->reveal());
        $elasticaDataCollector->collect($request->reveal(), $response->reveal());

        $this->assertEquals(40, $elasticaDataCollector->getTime());
    }

    public function testGetName()
    {
        /* @var $logger ElasticaLogger */
        $logger = $this->prophesize(ElasticaLogger::class);

        $elasticaDataCollector = new ElasticaDataCollector($logger->reveal());

        $this->assertSame('elastica', $elasticaDataCollector->getName());
    }
}
