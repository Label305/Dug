<?php


namespace Tests\Helpers;


use Dug\Interfaces\DataProvider;
use Dug\Objects\Data;

class UserProvider implements DataProvider
{

    /**
     * @param array $path
     * @return array
     */
    public function handle(array $path):array
    {
        return [
            Data::build(['users', 1], [
                'id' => 1,
                'source' => 'UserProvider'
            ])
        ];
    }
}