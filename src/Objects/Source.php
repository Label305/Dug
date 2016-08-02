<?php


namespace Dug\Objects;


use Dug\Interfaces\ClassInitializer;
use Dug\Interfaces\DataProvider;

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
    private $dataProviderClassName;

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
            $source->setDataProviderClassName($callback);
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
    public function getDataProviderInstance(ClassInitializer $injectionHandler):DataProvider
    {
        if ($this->callback instanceof \Closure) {
            return new ClosureDataProvider($this->callback);
        }

        return $injectionHandler->instantiate($this->dataProviderClassName);
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
     * @param string $dataProviderClassName
     */
    public function setDataProviderClassName(string $dataProviderClassName)
    {
        $this->dataProviderClassName = $dataProviderClassName;
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