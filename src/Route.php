<?php


namespace HWai;


class Route
{

    /**
     * @var string[]
     */
    private $parts;

    /**
     * @var \Closure
     */
    private $callback;

    /**
     * @param array    $array
     * @param \Closure $callback
     * @return Route
     */
    public static function build(array $array, \Closure $callback):Route
    {
        $route = new Route();
        $route->setParts($array);
        $route->setCallback($callback);

        return $route;
    }

    /**
     * @return \string[]
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * @param \string[] $parts
     */
    public function setParts(array $parts)
    {
        $this->parts = $parts;
    }

    /**
     * @return \Closure
     */
    public function getCallback(): \Closure
    {
        return $this->callback;
    }

    /**
     * @param \Closure $callback
     */
    public function setCallback(\Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function toString():string
    {
        return md5(implode('|', array_map(function ($el) {
            return $el;
        }, $this->parts)));
    }
}