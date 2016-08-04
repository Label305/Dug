<?php


namespace Dug\Objects;


class Data implements PathProvider
{

    /**
     * @var array
     */
    private $path;

    /**
     * @var array
     */
    private $value;

    /**
     * @param $parts
     * @param $value
     * @return Data
     */
    public static function build($parts, $value)
    {
        $data = new Data();
        $data->setPath($parts);
        $data->setValue($value);

        return $data;
    }

    /**
     * @return array
     */
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * @param array $path
     */
    public function setPath(array $path)
    {
        $this->path = $path;
    }

    /**
     * @return array|Reference
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param array|Reference|Data $value
     */
    public function setValue($value)
    {
        if ($value instanceof Data) {
            $child = $value->getValue();
            $this->value = $child instanceof Data ? $child->getValue() : $value;
        } else {
            $this->value = $value;
        }
    }
}