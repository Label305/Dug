<?php


namespace HWai\Objects;


class Reference
{
    /**
     * @var array
     */
    private $path;

    /**
     * ReferenceToSingle constructor.
     */
    public function __construct(array $path)
    {
        $this->path = $path;
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
    
}