<?php
/**
 * @author Dan Bettles <danbettles@yahoo.co.uk>
 * @license http://opensource.org/licenses/MIT MIT
 * @codingStandardsIgnoreFile
 */

/**
 * @author Dan Bettles <danbettles@yahoo.co.uk>
 */
class DisqTest extends PHPUnit_Framework_TestCase
{
    public function testReturnsADisqDisq()
    {
        $glob = __FILE__;
        $disq = Disq($glob);

        $this->assertInstanceOf('Disq\Disq', $disq);
        $this->assertSame($glob, $disq->getGlob());
        $this->assertSame(1, $disq->getLength());
    }

    public function testAcceptsAGlobContext()
    {
        $glob = basename(__FILE__);
        $globContext = __DIR__;
        $disq = Disq($glob, $globContext);

        $this->assertInstanceOf('Disq\Disq', $disq);
        $this->assertSame($glob, $disq->getGlob());
        $this->assertSame($globContext, $disq->getGlobContext());
        $this->assertSame(1, $disq->getLength());
    }
}
