<?php

/*
 * This file is part of the phlexible elastica package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\ElasticaBundle\Tests\Logger;

use Phlexible\Bundle\ElasticaBundle\Logger\ElasticaLogger;
use Psr\Log\LoggerInterface;

/**
 * @author Richard Miller <info@limethinking.co.uk>
 *
 * @covers \Phlexible\Bundle\ElasticaBundle\Logger\ElasticaLogger
 */
class ElasticaLoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $level
     * @param string $message
     * @param array  $context
     *
     * @return ElasticaLogger
     */
    private function createLoggerForLevelMessageAndContext($level, $message, $context)
    {
        $loggerMock = $this->prophesize(LoggerInterface::class);

        $loggerMock->$level($message, $context)->shouldBeCalled();

        $elasticaLogger = new ElasticaLogger($loggerMock->reveal());

        return $elasticaLogger;
    }

    public function testGetZeroIfNoQueriesAdded()
    {
        $elasticaLogger = new ElasticaLogger();
        $this->assertEquals(0, $elasticaLogger->getNbQueries());
    }

    public function testCorrectAmountIfRandomNumberOfQueriesAdded()
    {
        $elasticaLogger = new ElasticaLogger(null, true);

        $total = rand(1, 15);
        for ($i = 0; $i < $total; ++$i) {
            $elasticaLogger->logQuery('testPath', 'testMethod', array('data'), 12);
        }

        $this->assertEquals($total, $elasticaLogger->getNbQueries());
    }

    public function testCorrectlyFormattedQueryReturned()
    {
        $elasticaLogger = new ElasticaLogger(null, true);

        $path = 'testPath';
        $method = 'testMethod';
        $data = array('data');
        $time = 12;
        $connection = array('host' => 'localhost', 'port' => '8999', 'transport' => 'https');
        $query = array('search_type' => 'dfs_query_then_fetch');

        $expected = array(
            'path' => $path,
            'method' => $method,
            'data' => $data,
            'executionMS' => $time,
            'connection' => $connection,
            'queryString' => $query,
            'engineMS' => 0,
            'itemCount' => 0,
        );

        $elasticaLogger->logQuery($path, $method, $data, $time, $connection, $query);
        $returnedQueries = $elasticaLogger->getQueries();
        $this->assertArrayHasKey('backtrace', $returnedQueries[0]);
        $this->assertNotEmpty($returnedQueries[0]['backtrace']);
        unset($returnedQueries[0]['backtrace']);
        $this->assertEquals($expected, $returnedQueries[0]);
    }

    public function testNoQueriesStoredIfDebugFalseAdded()
    {
        $elasticaLogger = new ElasticaLogger(null, false);

        $total = rand(1, 15);
        for ($i = 0; $i < $total; ++$i) {
            $elasticaLogger->logQuery('testPath', 'testMethod', array('data'), 12);
        }

        $this->assertEquals(0, $elasticaLogger->getNbQueries());
    }

    public function testQueryIsLogged()
    {
        $loggerMock = $this->prophesize(LoggerInterface::class);

        $elasticaLogger = new ElasticaLogger($loggerMock->reveal());

        $path = 'testPath';
        $method = 'testMethod';
        $data = array('data');
        $time = 12;

        $expectedMessage = 'testPath (testMethod) 12000.00 ms';

        $loggerMock->info($expectedMessage, $data)->shouldBeCalled();

        $elasticaLogger->logQuery($path, $method, $data, $time);
    }

    /**
     * @return array
     */
    public function logLevels()
    {
        return array(
            array('emergency'),
            array('alert'),
            array('critical'),
            array('error'),
            array('warning'),
            array('notice'),
            array('info'),
            array('debug'),
        );
    }

    /**
     * @dataProvider logLevels
     */
    public function testMessagesCanBeLoggedAtSpecificLogLevels($level)
    {
        $message = 'foo';
        $context = array('data');

        $logger = $this->createLoggerForLevelMessageAndContext($level, $message, $context);

        call_user_func(array($logger, $level), $message, $context);
    }

    public function testMessagesCanBeLoggedToArbitraryLevels()
    {
        $loggerMock = $this->prophesize(LoggerInterface::class);

        $level = 'info';
        $message = 'foo';
        $context = array('data');

        $loggerMock->log($level, $message, $context)->shouldBeCalled();

        $elasticaLogger = new ElasticaLogger($loggerMock->reveal());

        $elasticaLogger->log($level, $message, $context);
    }
}
