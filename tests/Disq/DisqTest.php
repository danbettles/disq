<?php
/**
 * @author Dan Bettles <danbettles@yahoo.co.uk>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Disq\Tests\Disq;

use Disq\Disq;

/**
 * @author Dan Bettles <danbettles@yahoo.co.uk>
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
    private static function createFixturePath($path)
    {
        $fixturesDirBasename = basename(__FILE__, '.php');
        return __DIR__ . "/{$fixturesDirBasename}/{$path}";
    }

    private function createTestDisq($fixtureGlob)
    {
        return new Disq(self::createFixturePath($fixtureGlob));
    }

    public function testIsInstantiable()
    {
        $glob = __FILE__;
        $disq = new Disq($glob);

        $this->assertSame($glob, $disq->getGlob());
    }

    public function testConstructorAcceptsAGlobContext()
    {
        $glob = './*';
        $globContext = __DIR__;
        $disq = new Disq($glob, $globContext);

        $this->assertSame($globContext, $disq->getGlobContext());
        $this->assertSame("{$globContext}/./*", $disq->getCompleteGlob());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The specified glob context does not point at a directory.
     */
    public function testConstructorThrowsAnExceptionIfTheGlobContextDoesNotPointAtADirectory()
    {
        $disq = new Disq('*', '');
    }

    public static function providesCompleteGlobsByGlobAndGlobContext()
    {
        return array(
            array(  //Superfluous left, and right, slashes
                __DIR__ . '/*',
                '/*',
                __DIR__ . '/',
            ),
            array(  //Superfluous left slash
                __DIR__ . '/*',
                '/*',
                __DIR__,
            ),
            array(  //Superfluous right slash
                __DIR__ . '/*',
                '*',
                __DIR__ . '/',
            ),
        );
    }

    /**
     * @dataProvider providesCompleteGlobsByGlobAndGlobContext
     */
    public function testGetcompleteglobTrimsUnneededSlashes($expectedCompleteGlob, $glob, $globContext)
    {
        $disq = new Disq($glob, $globContext);
        $this->assertSame($expectedCompleteGlob, $disq->getCompleteGlob());
    }

    public static function providesMatchedPathsByGlob()
    {
        return array(
            array(
                array(
                    self::createFixturePath('testGetmatchedpathsReturnsThePathsMatchingTheGlob.1'),
                ),
                'testGetmatchedpathsReturnsThePathsMatchingTheGlob.1',
            ),
            array(
                array(
                    self::createFixturePath('testGetmatchedpathsReturnsThePathsMatchingTheGlob.1'),
                    self::createFixturePath('testGetmatchedpathsReturnsThePathsMatchingTheGlob.2'),
                ),
                'testGetmatchedpathsReturnsThePathsMatchingTheGlob.*',
            ),
        );
    }

    /**
     * @dataProvider providesMatchedPathsByGlob
     */
    public function testGetmatchedpathsReturnsThePathsMatchingTheGlob($expectedMatchedPaths, $fixtureGlob)
    {
        $disq = $this->createTestDisq($fixtureGlob);
        $this->assertEquals($expectedMatchedPaths, $disq->getMatchedPaths());
    }

    public static function providesLengthOfMatchedPathsByGlob()
    {
        return array(
            array(
                1,
                'testGetlengthReturnsTheNumberOfPathsMatchingTheGlob.1',
            ),
            array(
                2,
                'testGetlengthReturnsTheNumberOfPathsMatchingTheGlob.*',
            ),
        );
    }

    /**
     * @dataProvider providesLengthOfMatchedPathsByGlob
     */
    public function testGetlengthReturnsTheNumberOfPathsMatchingTheGlob($expectedLength, $fixtureGlob)
    {
        $disq = $this->createTestDisq($fixtureGlob);
        $this->assertSame($expectedLength, $disq->getLength());
    }

    public static function providesNumberOfEachCallsByGlob()
    {
        return array(
            array(
                1,
                'testEachCallsTheSpecifiedFunctionForEachPathMatchingTheGlob.1',
            ),
            array(
                2,
                'testEachCallsTheSpecifiedFunctionForEachPathMatchingTheGlob.*',
            ),
        );
    }

    /**
     * @dataProvider providesNumberOfEachCallsByGlob
     */
    public function testEachCallsTheSpecifiedFunctionForEachPathMatchingTheGlob($expectedNumCalls, $fixtureGlob)
    {
        $disq = $this->createTestDisq($fixtureGlob);

        $actualNumCalls = 0;

        $disq->each(function () use (&$actualNumCalls) {
            $actualNumCalls += 1;
        });

        $this->assertSame($expectedNumCalls, $actualNumCalls);
    }

    public function testEachReturnsTheInstance()
    {
        $disq = new Disq(__FILE__);

        $returnValue = $disq->each(function () {
        });

        $this->assertSame($disq, $returnValue);
    }

    public function testEachCallsTheSpecifiedFunctionInTheContextOfANewInstance()
    {
        $disq = $this->createTestDisq('testEachCallsTheSpecifiedFunctionInTheContextOfANew*.1');

        $context = null;

        $disq->each(function () use (&$context) {
            $context = $this;
        });

        $this->assertInstanceOf('Disq\Disq', $context);
        $this->assertNotSame($disq, $context);

        $expectedFixturePath = self::createFixturePath('testEachCallsTheSpecifiedFunctionInTheContextOfANewInstance.1');
        $this->assertSame($expectedFixturePath, $context->getGlob());
    }

    public function testEachPassesTheIndexOfTheCurrentPathToTheSpecifiedFunction()
    {
        $disq = $this->createTestDisq('testEachCallsTheSpecifiedFunctionInTheContextOfANew*.1');

        $capturedIndex = null;

        $disq->each(function ($yieldedIndex) use (&$capturedIndex) {
            $capturedIndex = $yieldedIndex;
        });

        $this->assertSame(0, $capturedIndex);
    }

    public function testEachWillStopLoopingIfTheSpecifiedFunctionReturnsFalse()
    {
        $fixtureGlob = 'testEachWillStopLoopingIfTheSpecifiedFunctionReturnsFalse.*';
        $disq = $this->createTestDisq($fixtureGlob);

        $numCalls = 0;

        $disq->each(function () use (&$numCalls) {
            $numCalls += 1;
        });

        $this->assertSame(2, $numCalls);

        $numCalls = 0;

        $disq->each(function () use (&$numCalls) {
            $numCalls += 1;
            return false;
        });

        $this->assertSame(1, $numCalls);
    }

    public function testGetinfoReturnsASplfileinfoForTheFirstPathInTheCollectionOfMatchedPaths()
    {
        $disq = $this->createTestDisq('testGetinfoReturnsASplfileinfoForTheFirstFileInTheResultSet.*');

        $this->assertSame(2, $disq->getLength());

        $fixturePath = self::createFixturePath('testGetinfoReturnsASplfileinfoForTheFirstFileInTheResultSet.1');
        $splFileInfo = new \SplFileInfo($fixturePath);

        $this->assertEquals($splFileInfo, $disq->getInfo());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The collection of matched paths is empty.
     */
    public function testGetinfoThrowsAnExceptionIfTheCollectionOfMatchedPathsIsEmpty()
    {
        $disq = new Disq('');
        $disq->getInfo();
    }

    /**
     * @expectedException BadMethodCallException
     * @expectedExceptionMessage The method "foo" does not exist.
     */
    public function testThrowsAnExceptionIfTheCalledMethodDoesNotExistInTheInstanceOrInSplfileinfo()
    {
        $disq = new Disq(__FILE__);
        $disq->foo();
    }

    public function testIsfileReturnsTrueIfTheFirstPathInTheCollectionOfMatchedPathsPointsAtAFile()
    {
        $disq = new Disq(__FILE__);
        $this->assertTrue($disq->isFile());
    }

    public function testIsdirReturnsTrueIfTheFirstPathInTheCollectionOfMatchedPathsPointsAtADirectory()
    {
        $disq = new Disq(__DIR__);
        $this->assertTrue($disq->isDir());
    }

    public function testIslinkReturnsTrueIfTheFirstPathInTheCollectionOfMatchedPathsPointsAtALink()
    {
        $fixtureGlob = 'testIslinkReturnsTrueIfTheFirstPathInTheCollectionOfMatchedPathsPointsAtALink.2';
        $disq = $this->createTestDisq($fixtureGlob);

        $this->assertTrue($disq->isLink());
    }

    public function testGetrealpathReturnsTheAbsoluteVersionOfTheFirstPathInTheCollectionOfMatchingPaths()
    {
        $previousWorkingDir = getcwd();
        chdir(__DIR__);

        $fixtureBasename = 'testGetrealpathReturnsTheAbsoluteVersionOfTheFirstPathInTheCollectionOfMatchingPaths.1';
        $disq = new Disq("./DisqTest/{$fixtureBasename}");

        $this->assertSame(self::createFixturePath($fixtureBasename), $disq->getRealPath());

        chdir($previousWorkingDir);
    }

    public function testGetbasenameReturnsTheBasenameOfTheFirstPathInTheCollectionOfMatchingPaths()
    {
        $basenameExcludingSuffix = 'testGetbasenameReturnsTheBasenameOfTheFirstPathInTheCollectionOfMatchingPaths';
        $suffix = '.1';
        $fixtureGlob = "{$basenameExcludingSuffix}{$suffix}";
        $disq = $this->createTestDisq($fixtureGlob);

        $this->assertSame($fixtureGlob, $disq->getBasename());
        $this->assertSame($basenameExcludingSuffix, $disq->getBasename($suffix));
    }
}
