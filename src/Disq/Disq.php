<?php
/**
 * @author Dan Bettles <danbettles@yahoo.co.uk>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace Disq;

/**
 * @author Dan Bettles <danbettles@yahoo.co.uk>
 * @method bool isFile() Returns TRUE if the first path in the collection of matched paths points at a file
 * @method bool isDir() Returns TRUE if the first path in the collection of matched paths points at a directory
 * @method bool isLink() Returns TRUE if the first path in the collection of matched paths points at a link
 * @method string getRealPath() Returns the real path of the first path in the collection of matched paths
 * @method string getBasename() Returns the basename of the first path in the collection of matched paths
 */
class Disq
{
    /**
     * The glob used to construct the instance.
     * 
     * @var string
     */
    private $glob;

    /**
     * The context (think "working directory") of the glob used to construct the instance.
     * 
     * @var string
     */
    private $globContext;

    /**
     * The paths matching the glob used to construct the instance.
     * 
     * @var array
     */
    private $matchedPaths;

    /**
     * @param string $glob
     * @param string|null [$globContext = null]
     * @return void
     * @throws \RuntimeException If globbing, using the specified glob, failed.
     */
    public function __construct($glob, $globContext = null)
    {
        $this->setGlob($glob);
        $this->setGlobContext($globContext);

        $matchedPaths = glob($this->getCompleteGlob());

        if (false === $matchedPaths) {
            throw new \RuntimeException("Globbing, using \"{$this->getCompleteGlob()}\", failed.");
        }

        $this->setMatchedPaths($matchedPaths);
    }

    /**
     * Sets the glob used to construct the instance.
     * 
     * @param string $glob
     * @return \Disq\Disq $this
     */
    private function setGlob($glob)
    {
        $this->glob = $glob;
        return $this;
    }

    /**
     * Returns the glob used to construct the instance.
     * 
     * @return string
     */
    public function getGlob()
    {
        return $this->glob;
    }

    /**
     * Sets the context (think "working directory") of the glob used to construct the instance.
     * 
     * @param string|null $globContext
     * @return \Disq\Disq $this
     * @throws \InvalidArgumentException If the specified glob context does not point at a directory.
     */
    private function setGlobContext($globContext)
    {
        if (!is_null($globContext) && !is_dir($globContext)) {
            throw new \InvalidArgumentException('The specified glob context does not point at a directory.');
        }

        $this->globContext = $globContext;
        return $this;
    }

    /**
     * Returns the context (think "working directory") of the glob used to construct the instance.
     * 
     * @return string
     */
    public function getGlobContext()
    {
        return $this->globContext;
    }

    /**
     * Returns the complete glob, assembled from the arguments used to construct the instance.
     * 
     * @return string
     */
    public function getCompleteGlob()
    {
        if (null === $this->getGlobContext()) {
            return $this->getGlob();
        }

        return (
            rtrim($this->getGlobContext(), DIRECTORY_SEPARATOR) .
            DIRECTORY_SEPARATOR .
            ltrim($this->getGlob(), DIRECTORY_SEPARATOR)
        );
    }

    /**
     * Sets the paths matching the glob used to construct the instance.
     * 
     * @param array $matchedPaths
     * @return \Disq\Disq $this
     */
    private function setMatchedPaths(array $matchedPaths)
    {
        $this->matchedPaths = $matchedPaths;
        return $this;
    }

    /**
     * Returns the paths matching the glob used to construct the instance.
     * 
     * @return array
     */
    public function getMatchedPaths()
    {
        return $this->matchedPaths;
    }

    /**
     * Returns the first path matching the glob used to construct the instance.
     * 
     * @return string
     */
    private function getFirstMatchedPath()
    {
        $matchedPaths = $this->getMatchedPaths();
        return reset($matchedPaths);
    }

    /**
     * Returns a `SplFileInfo` for the first path matching the glob used to construct the instance.
     * 
     * @return \SplFileInfo
     * @throws \RuntimeException If the collection of matched paths is empty.
     */
    public function getInfo()
    {
        $firstMatchedPath = $this->getFirstMatchedPath();

        if (false === $firstMatchedPath) {
            throw new \RuntimeException('The collection of matched paths is empty.');
        }

        return new \SplFileInfo($firstMatchedPath);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \BadMethodCallException If the called method does not exist.
     */
    public function __call($name, array $arguments)
    {
        $info = $this->getInfo();

        if (!method_exists($info, $name)) {
            throw new \BadMethodCallException("The method \"{$name}\" does not exist.");
        }

        return call_user_func_array(array($info, $name), $arguments);
    }

    /**
     * Returns the number of paths matching the glob used to construct the instance.
     * 
     * @return int
     */
    public function getLength()
    {
        return count($this->getMatchedPaths());
    }

    /**
     * Calls the specified function for each path matching the glob used to construct the instance.
     * 
     * The function is called in the context of a `Disq` for the current path, and is passed the index of the current 
     * path.
     * 
     * @param \Closure $closure function($index)
     * @return \Disq\Disq $this
     */
    public function each(\Closure $closure)
    {
        foreach ($this->getMatchedPaths() as $index => $matchedPath) {
            $context = new self($matchedPath);
            $boundClosure = $closure->bindTo($context);

            if (false === $boundClosure($index)) {
                break;
            }
        }

        return $this;
    }
}
