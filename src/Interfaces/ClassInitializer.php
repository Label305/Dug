<?php


namespace Dug\Interfaces;


interface ClassInitializer
{

    /**
     * @param string $className
     * @return mixed
     */
    public function instantiate(string $className);

}