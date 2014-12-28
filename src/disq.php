<?php
/**
 * @author Dan Bettles <danbettles@yahoo.co.uk>
 * @license http://opensource.org/licenses/MIT MIT
 */

use Disq\Disq;

/**
 * Convenience factory function.
 * 
 * @param string $glob
 * @param string [$globContext = null]
 * @return \Disq\Disq
 */
function Disq($glob, $globContext = null)
{
    return new Disq($glob, $globContext);
}
