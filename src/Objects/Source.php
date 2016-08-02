<?php


namespace Dug\Objects;


use Dug\Interfaces\DataProvider;
use Dug\Objects\ClosureDataProvider;

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
     * @var string
     */
    private $dataProviderClass;

    /**
     * @param array           $array
     * @param \Closure|string $callback
     * @return Source
     */
    public static function build(array $array, $callback):Source
    {
        $source = new Source();
        $source->setParts($array);

        if (is_string($callback)) {
            $source->setDataProviderClass($callback);
        } else {
            $source->setCallback($callback);
        }

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
     * @return DataProvider
     */
    public function getDataProviderInstance()
    {
        if ($this->callback instanceof \Closure) {
            return new ClosureDataProvider($this->callback);
        }

        return new $this->dataProviderClass;
    }

    /**
     * @param \string[] $parts
     */
    public function setParts(array $parts)
    {
        $this->parts = $parts;
    }

    /**
     * @param \Closure $callback
     */
    public function setCallback(\Closure $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param string $dataProviderClass
     */
    public function setDataProviderClass(string $dataProviderClass)
    {
        $this->dataProviderClass = $dataProviderClass;
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