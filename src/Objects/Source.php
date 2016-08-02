<?php


namespace Dug\Objects;


class Source
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
     * @return Source
     */
    public static function build(array $array, \Closure $callback):Source
    {
        $source = new Source();
        $source->setParts($array);
        $source->setCallback($callback);

        return $source;
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