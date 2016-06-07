<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\ElasticaBundle\Tests;

use Phlexible\Bundle\ElasticaBundle\PhlexibleElasticaBundle;

/**
 * Elastica bundle test
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class PhlexibleElasticaBundleTest extends \PHPUnit_Framework_TestCase
{
    public function testBundle()
    {
        $bundle = new PhlexibleElasticaBundle();

        $this->assertSame('PhlexibleElasticaBundle', $bundle->getName());
    }
}
