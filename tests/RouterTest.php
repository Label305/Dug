<?php


namespace Tests;


use HWai\Exceptions\RouteNotFoundException;
use HWai\Objects\Data;
use HWai\Objects\Reference;
use HWai\Objects\ReferenceToSingle;
use HWai\Objects\Route;
use HWai\Router;
use Tests\Helpers\Bag;

class RouterTest extends TestCase
{

    public function testEmptyResult()
    {
        /* Given */
        $router = new Router();
        $route = Route::build(['categories'], function (array $path) {
            return [];
        });

        $router->register($route);

        /* When */
        $result = $router->fetch(['categories']);

        /* Then */
        assertThat($result, is([]));
    }

    public function missingRoute()
    {
        /* Given */
        $router = new Router();
        $route = Route::build(['categories'], function (array $path) {
            return [];
        });

        $router->register($route);

        /* When */
        $exception = false;
        try {
            $router->fetch(['tracks']);
        } catch (RouteNotFoundException $e) {
            $exception = true;
        }

        /* Then */
        assertThat($exception, is(true));
    }


    public function testCalledWithCorrectPath()
    {
        /* Given */
        $router = new Router();
        $bag = new Bag();
        $route = Route::build(['categories'], function (array $path) use ($bag) {
            $bag->set('path', $path);

            return [];
        });

        $router->register($route);

        /* When */
        $router->fetch(['categories']);

        /* Then */
        assertThat(count($bag->get('path')), is(1));
        assertThat($bag->get('path')[0], is('categories'));
    }

    public function testSingleResult()
    {
        /* Given */
        $router = new Router();
        $route = Route::build(['me'], function (array $path) {
            return [
                Data::build(['me'], ['id' => 123])
            ];
        });

        $router->register($route);

        /* When */
        $result = $router->fetch(['me']);

        /* Then */
        assertThat($result, is([['id' => 123]]));
    }

    public function testSingleFetchSingleResult()
    {
        /* Given */
        $router = new Router();
        $route = Route::build(['me'], function (array $path) {
            return [
                Data::build(['me'], ['id' => 123])
            ];
        });

        $router->register($route);

        /* When */
        $result = $router->fetchSingle(['me']);

        /* Then */
        assertThat($result, is([
            'id' => 123
        ]));
    }

    public function testSingleCombinedResult()
    {
        /* Given */
        $router = new Router();
        $route = Route::build(['me'], function (array $path) {
            return [
                Data::build(['me'], ['id' => 123]),
                Data::build(['me'], ['name' => 'Joris'])
            ];
        });

        $router->register($route);

        /* When */
        $result = $router->fetch(['me']);

        /* Then */
        assertThat($result, is([['id' => 123, 'name' => 'Joris']]));
    }

    public function testMultipleResult()
    {
        /* Given */
        $router = new Router();
        $route = Route::build(['users', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['users', 123], ['id' => 123, 'name' => 'Joris']),
                Data::build(['users', 321], ['id' => 321, 'name' => 'Jisca'])
            ];
        });
        $router->register($route);

        /* When */
        $result = $router->fetch(['users', [123, 321]]);

        /* Then */
        assertThat($result, is([
            ['id' => 123, 'name' => 'Joris'],
            ['id' => 321, 'name' => 'Jisca']
        ]));

    }

    public function testMultipleCombinedResult()
    {
        /* Given */
        $router = new Router();
        $route = Route::build(['users', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['users', 123], ['id' => 123]),
                Data::build(['users', 321], ['id' => 321]),
                Data::build(['users', 123], ['name' => 'Joris']),
                Data::build(['users', 321], ['name' => 'Jisca'])
            ];
        });
        $router->register($route);

        /* When */
        $result = $router->fetch(['users', [123, 321]]);

        /* Then */
        assertThat($result, is([
            ['id' => 123, 'name' => 'Joris'],
            ['id' => 321, 'name' => 'Jisca']
        ]));
    }

    public function testReferenceToSingle()
    {
        /* Given */
        $router = new Router();
        $bag = new Bag();
        $bag->set('count', 0);
        $route = Route::build(['petForUser', '/[0-9]+/'], function (array $path) use ($bag) {
            $bag->set('count', $bag->get('count') + 1);

            return [
                Data::build(['petForUser', 123], ['name' => 'Fluffy']),
                Data::build(['petForUser', 321], ['name' => 'Loesje'])
            ];
        });
        $router->register($route);
        $route = Route::build(['users', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['users', 123], ['id' => 123, 'pet' => new ReferenceToSingle(['petForUser', 123])]),
                Data::build(['users', 321], ['id' => 321, 'pet' => new ReferenceToSingle(['petForUser', 321])])
            ];
        });
        $router->register($route);

        /* When */
        $result = $router->fetch(['users', [123, 321]]);

        /* Then */
        assertThat($result, is([
            [
                'id' => 123,
                'pet' => ['name' => 'Fluffy']
            ],
            [
                'id' => 321,
                'pet' => ['name' => 'Loesje']
            ]
        ]));
        assertThat($bag->get('count'), is(1));
    }

    public function testReferenceList()
    {
        /* Given */
        $router = new Router();
        $route = Route::build(['pets', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['pets', 1], ['name' => 'Fluffy']),
                Data::build(['pets', 2], ['name' => 'Loesje'])
            ];
        });
        $router->register($route);
        $route = Route::build(['users', '/[0-9]+/', 'pets'], function (array $path) {
            return [
                Data::build(['users', 123, 'pets'], new ReferenceToSingle(['pets', 1])),
                Data::build(['users', 123, 'pets'], new ReferenceToSingle(['pets', 2])),
            ];
        });
        $router->register($route);

        /* When */
        $result = $router->fetchSingle(['users', [123], 'pets']);

        /* Then */
        assertThat($result, is([
            ['name' => 'Fluffy'],
            ['name' => 'Loesje']
        ]));
    }

    public function testReferenceListIntermediate()
    {
        /* Given */
        $router = new Router();
        $route = Route::build(['pets', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['pets', 1], ['name' => 'Fluffy']),
                Data::build(['pets', 2], ['name' => 'Loesje'])
            ];
        });
        $router->register($route);
        $route = Route::build(['users', '/[0-9]+/', 'pets'], function (array $path) {
            return [
                Data::build(['users', 123, 'pets'], new ReferenceToSingle(['pets', 1])),
                Data::build(['users', 123, 'pets'], new ReferenceToSingle(['pets', 2])),
            ];
        });
        $router->register($route);
        $route = Route::build(['users', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['users', 123], [
                    'id' => 123,
                    'pets' => new ReferenceToSingle(['users', 123, 'pets'])
                ])
            ];
        });
        $router->register($route);

        /* When */
        $result = $router->fetch(['users', [123]]);

        /* Then */
        assertThat($result, is([
            [
                'id' => 123,
                'pets' => [
                    ['name' => 'Fluffy'],
                    ['name' => 'Loesje']
                ]
            ]
        ]));
    }

    public function testReferenceListToMultiple()
    {
        /* Given */
        $router = new Router();
        $route = Route::build(['pets', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['pets', 1], ['name' => 'Fluffy']),
                Data::build(['pets', 2], ['name' => 'Loesje'])
            ];
        });
        $router->register($route);
        $route = Route::build(['users', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['users', 123], [
                    'id' => 123,
                    'pets' => new Reference(['pets', [1, 2]])
                ])
            ];
        });
        $router->register($route);

        /* When */
        $result = $router->fetch(['users', [123]]);

        /* Then */
        assertThat($result, is([
            [
                'id' => 123,
                'pets' => [
                    ['name' => 'Fluffy'],
                    ['name' => 'Loesje']
                ]
            ]
        ]));
    }

}