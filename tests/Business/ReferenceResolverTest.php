<?php


namespace Tests\Business;


use HWai\Business\ReferenceResolver;
use HWai\Objects\Data;
use HWai\Objects\Reference;
use HWai\Objects\ReferenceToSingle;
use HWai\Objects\Route;
use HWai\Router;
use Tests\TestCase;

class ReferenceResolverTest extends TestCase
{

    public function testNoReference()
    {
        /* Given */
        $router = \Mockery::mock(Router::class);

        $data = Data::build(['foo', 1], ['foo' => 'bar']);
        $input = [
            $data
        ];

        /* When */
        $referenceResolver = new ReferenceResolver($router);
        $result = $referenceResolver->process($input);

        /* Then */
        assertThat(count($result), is(1));
        assertThat($result[0], is($data));
    }

    public function testSingleReference()
    {
        /* Given */
        $router = \Mockery::mock(Router::class);
        $route = Route::build(['users', '/[0-9]+/'], function () {
        });
        $router->shouldReceive('routeForPath')->once()->andReturn($route);

        $user = Data::build(['users', 1], ['id' => 1, 'name' => 'Joris']);
        $router->shouldReceive('data')->once()->andReturn([$user]);

        $input = [
            Data::build(['foo', 1], ['user' => new Reference(['users', 1])])
        ];

        /* When */
        $referenceResolver = new ReferenceResolver($router);
        $result = $referenceResolver->process($input);

        /* Then */
        assertThat(count($result), is(1));
        assertThat($result[0]->getValue()['user'], is([$user]));
    }

    public function testSingleReferenceToSingle()
    {
        /* Given */
        $router = \Mockery::mock(Router::class);
        $route = Route::build(['users', '/[0-9]+/'], function () {
        });
        $router->shouldReceive('routeForPath')->once()->andReturn($route);

        $user = Data::build(['users', 1], ['id' => 1, 'name' => 'Joris']);
        $router->shouldReceive('data')->once()->andReturn([$user]);

        $input = [
            Data::build(['foo', 1], ['user' => new ReferenceToSingle(['users', 1])])
        ];

        /* When */
        $referenceResolver = new ReferenceResolver($router);
        $result = $referenceResolver->process($input);

        /* Then */
        assertThat(count($result), is(1));
        assertThat($result[0]->getValue()['user'], is($user));
    }

    public function testMultipleReferencesShouldCallOnce()
    {
        /* Given */
        $router = \Mockery::mock(Router::class);
        $route = Route::build(['users', '/[0-9]+/'], function () {
        });
        $router->shouldReceive('routeForPath')->once()->andReturn($route);

        $user1 = Data::build(['users', 1], ['id' => 1, 'name' => 'Joris']);
        $user2 = Data::build(['users', 2], ['id' => 2, 'name' => 'Jisca']);
        $router->shouldReceive('data')->once()->andReturn([$user1, $user2]);

        $input = [
            Data::build(['foo', 1], ['user' => new Reference(['users', 1])]),
            Data::build(['foo', 2], ['user' => new Reference(['users', 2])])
        ];

        /* When */
        $referenceResolver = new ReferenceResolver($router);
        $result = $referenceResolver->process($input);

        /* Then */
        assertThat(count($result), is(2));
        assertThat($result[0]->getValue()['user'], is([$user1]));
        assertThat($result[1]->getValue()['user'], is([$user2]));
    }
}
