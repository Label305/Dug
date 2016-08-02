<?php


namespace Dug\Business;


use Dug\Interfaces\ClassInitializer;

class BasicClassInitializer implements ClassInitializer
{

    /**
     * @param string $className
     * @return mixed
     */
    public function instantiate(string $className)
    {
        return new $className;
    }
}