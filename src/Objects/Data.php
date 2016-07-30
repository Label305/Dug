<?php


namespace HWai\Objects;


class Data
{

    /**
     * @var array
     */
    private $parts;

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
        $data->setParts($parts);
        $data->setValue($value);

        return $data;
    }

    /**
     * @return array
     */
    public function getParts(): array
    {
        return $this->parts;
    }

    /**
     * @param array $parts
     */
    public function setParts(array $parts)
    {
        $this->parts = $parts;
    }

    /**
     * @return array
     */
    public function getValue(): array
    {
        return $this->value;
    }

    /**
     * @param array $value
     */
    public function setValue(array $value)
    {
        $this->value = $value;
    }
}