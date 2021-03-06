<?php

/*
 * This file is part of the phlexible elastica package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\ElasticaBundle\Tests;

use Phlexible\Bundle\ElasticaBundle\PhlexibleElasticaBundle;
use PHPUnit\Framework\TestCase;

/**
 * Elastica bundle test.
 *
 * @covers \Phlexible\Bundle\ElasticaBundle\PhlexibleElasticaBundle
 */
class PhlexibleElasticaBundleTest extends TestCase
{
    public function testBundle()
    {
        $bundle = new PhlexibleElasticaBundle();

        $this->assertSame('PhlexibleElasticaBundle', $bundle->getName());
    }
}
