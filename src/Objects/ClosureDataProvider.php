<?php


namespace Dug\Objects;


use Dug\Interfaces\DataProvider;
use stdClass;

class ClosureDataProvider implements DataProvider
{
    /**
     * @var \Closure
     */
    private $closure;

    /**
     * ClosureDataProvider constructor.
     * @param \Closure $closure
     */
    public function __construct(\Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @param array $path
     * @return array
     */
    public function handle(array $path):array
    {
        return $this->closure->call($this, $path);
    }
}