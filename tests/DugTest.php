<?php


namespace Tests;


use Dug\Exceptions\RouteNotFoundException;
use Dug\Objects\Data;
use Dug\Objects\Reference;
use Dug\Objects\ReferenceToSingle;
use Dug\Objects\Source;
use Dug\Dug;
use Tests\Helpers\Bag;

class DugTest extends TestCase
{

    public function testEmptyResult()
    {
        /* Given */
        $dug = new Dug();
        $source = Source::build(['categories'], function (array $path) {
            return [];
        });

        $dug->register($source);

        /* When */
        $result = $dug->fetch(['categories']);

        /* Then */
        assertThat($result, is([]));
    }

    public function missingRoute()
    {
        /* Given */
        $dug = new Dug();
        $source = Source::build(['categories'], function (array $path) {
            return [];
        });

        $dug->register($source);

        /* When */
        $exception = false;
        try {
            $dug->fetch(['tracks']);
        } catch (RouteNotFoundException $e) {
            $exception = true;
        }

        /* Then */
        assertThat($exception, is(true));
    }


    public function testCalledWithCorrectPath()
    {
        /* Given */
        $dug = new Dug();
        $bag = new Bag();
        $source = Source::build(['categories'], function (array $path) use ($bag) {
            $bag->set('path', $path);

            return [];
        });

        $dug->register($source);

        /* When */
        $dug->fetch(['categories']);

        /* Then */
        assertThat(count($bag->get('path')), is(1));
        assertThat($bag->get('path')[0], is('categories'));
    }

    public function testSingleResult()
    {
        /* Given */
        $dug = new Dug();
        $source = Source::build(['me'], function (array $path) {
            return [
                Data::build(['me'], ['id' => 123])
            ];
        });

        $dug->register($source);

        /* When */
        $result = $dug->fetch(['me']);

        /* Then */
        assertThat($result, is([['id' => 123]]));
    }

    public function testSingleFetchSingleResult()
    {
        /* Given */
        $dug = new Dug();
        $source = Source::build(['me'], function (array $path) {
            return [
                Data::build(['me'], ['id' => 123])
            ];
        });

        $dug->register($source);

        /* When */
        $result = $dug->fetchSingle(['me']);

        /* Then */
        assertThat($result, is([
            'id' => 123
        ]));
    }

    public function testSingleCombinedResult()
    {
        /* Given */
        $dug = new Dug();
        $source = Source::build(['me'], function (array $path) {
            return [
                Data::build(['me'], ['id' => 123]),
                Data::build(['me'], ['name' => 'Joris'])
            ];
        });

        $dug->register($source);

        /* When */
        $result = $dug->fetch(['me']);

        /* Then */
        assertThat($result, is([['id' => 123, 'name' => 'Joris']]));
    }

    public function testMultipleResult()
    {
        /* Given */
        $dug = new Dug();
        $source = Source::build(['users', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['users', 123], ['id' => 123, 'name' => 'Joris']),
                Data::build(['users', 321], ['id' => 321, 'name' => 'Jisca'])
            ];
        });
        $dug->register($source);

        /* When */
        $result = $dug->fetch(['users', [123, 321]]);

        /* Then */
        assertThat($result, is([
            ['id' => 123, 'name' => 'Joris'],
            ['id' => 321, 'name' => 'Jisca']
        ]));

    }

    public function testMultipleCombinedResult()
    {
        /* Given */
        $dug = new Dug();
        $source = Source::build(['users', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['users', 123], ['id' => 123]),
                Data::build(['users', 321], ['id' => 321]),
                Data::build(['users', 123], ['name' => 'Joris']),
                Data::build(['users', 321], ['name' => 'Jisca'])
            ];
        });
        $dug->register($source);

        /* When */
        $result = $dug->fetch(['users', [123, 321]]);

        /* Then */
        assertThat($result, is([
            ['id' => 123, 'name' => 'Joris'],
            ['id' => 321, 'name' => 'Jisca']
        ]));
    }

    public function testReferenceToSingle()
    {
        /* Given */
        $dug = new Dug();
        $bag = new Bag();
        $bag->set('count', 0);
        $source = Source::build(['petForUser', '/[0-9]+/'], function (array $path) use ($bag) {
            $bag->set('count', $bag->get('count') + 1);

            return [
                Data::build(['petForUser', 123], ['name' => 'Fluffy']),
                Data::build(['petForUser', 321], ['name' => 'Loesje'])
            ];
        });
        $dug->register($source);
        $source = Source::build(['users', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['users', 123], ['id' => 123, 'pet' => new ReferenceToSingle(['petForUser', 123])]),
                Data::build(['users', 321], ['id' => 321, 'pet' => new ReferenceToSingle(['petForUser', 321])])
            ];
        });
        $dug->register($source);

        /* When */
        $result = $dug->fetch(['users', [123, 321]]);

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
        $dug = new Dug();
        $source = Source::build(['pets', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['pets', 1], ['name' => 'Fluffy']),
                Data::build(['pets', 2], ['name' => 'Loesje'])
            ];
        });
        $dug->register($source);
        $source = Source::build(['users', '/[0-9]+/', 'pets'], function (array $path) {
            return [
                Data::build(['users', 123, 'pets'], new ReferenceToSingle(['pets', 1])),
                Data::build(['users', 123, 'pets'], new ReferenceToSingle(['pets', 2])),
            ];
        });
        $dug->register($source);

        /* When */
        $result = $dug->fetchSingle(['users', [123], 'pets']);

        /* Then */
        assertThat($result, is([
            ['name' => 'Fluffy'],
            ['name' => 'Loesje']
        ]));
    }

    public function testReferenceListIntermediate()
    {
        /* Given */
        $dug = new Dug();
        $source = Source::build(['pets', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['pets', 1], ['name' => 'Fluffy']),
                Data::build(['pets', 2], ['name' => 'Loesje'])
            ];
        });
        $dug->register($source);
        $source = Source::build(['users', '/[0-9]+/', 'pets'], function (array $path) {
            return [
                Data::build(['users', 123, 'pets'], new ReferenceToSingle(['pets', 1])),
                Data::build(['users', 123, 'pets'], new ReferenceToSingle(['pets', 2])),
            ];
        });
        $dug->register($source);
        $source = Source::build(['users', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['users', 123], [
                    'id' => 123,
                    'pets' => new ReferenceToSingle(['users', 123, 'pets'])
                ])
            ];
        });
        $dug->register($source);

        /* When */
        $result = $dug->fetch(['users', [123]]);

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
        $dug = new Dug();
        $source = Source::build(['pets', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['pets', 1], ['name' => 'Fluffy']),
                Data::build(['pets', 2], ['name' => 'Loesje'])
            ];
        });
        $dug->register($source);
        $source = Source::build(['users', '/[0-9]+/'], function (array $path) {
            return [
                Data::build(['users', 123], [
                    'id' => 123,
                    'pets' => new Reference(['pets', [1, 2]])
                ])
            ];
        });
        $dug->register($source);

        /* When */
        $result = $dug->fetch(['users', [123]]);

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