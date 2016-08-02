<?php


namespace Dug\Interfaces;


interface DataProvider
{

    /**
     * @param array $path
     * @return array
     */
    public function handle(array $path):array;

}