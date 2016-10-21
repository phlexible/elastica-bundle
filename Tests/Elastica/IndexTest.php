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

use Elastica\Type;
use Phlexible\Bundle\ElasticaBundle\Elastica\Client;
use Phlexible\Bundle\ElasticaBundle\Elastica\Index;

/**
 * Index test.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 *
 * @covers \Phlexible\Bundle\ElasticaBundle\Elastica\Index
 */
class IndexTest extends \PHPUnit_Framework_TestCase
{
    public function testGetOriginalName()
    {
        $client = $this->prophesize(Client::class);

        $index = new Index($client->reveal(), 'foo');

        $this->assertSame('foo', $index->getOriginalName());
        $this->assertSame('foo', $index->getName());

        $index->overrideName('bar');

        $this->assertSame('foo', $index->getOriginalName());
        $this->assertSame('bar', $index->getName());
    }

    public function testGetType()
    {
        $client = $this->prophesize(Client::class);

        $index = new Index($client->reveal(), 'foo');
        $result = $index->getType('bar');

        $this->assertInstanceOf(Type::class, $result);
    }

    public function testGetTypeUsesCache()
    {
        $client = $this->prophesize(Client::class);

        $index = new Index($client->reveal(), 'foo');
        $result1 = $index->getType('bar');
        $result2 = $index->getType('bar');

        $this->assertSame($result1, $result2);
    }
}
