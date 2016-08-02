<?php


namespace Tests\Business;


use Dug\Business\ReferenceResolver;
use Dug\Objects\Data;
use Dug\Objects\Reference;
use Dug\Objects\ReferenceToSingle;
use Dug\Objects\Source;
use Dug\Dug;
use Tests\TestCase;

class ReferenceResolverTest extends TestCase
{

    public function testNoReference()
    {
        /* Given */
        $dug = \Mockery::mock(Dug::class);

        $data = Data::build(['foo', 1], ['foo' => 'bar']);
        $input = [
            $data
        ];

        /* When */
        $referenceResolver = new ReferenceResolver($dug);
        $result = $referenceResolver->process($input);

        /* Then */
        assertThat(count($result), is(1));
        assertThat($result[0], is($data));
    }

    public function testSingleReference()
    {
        /* Given */
        $dug = \Mockery::mock(Dug::class);
        $source = Source::build(['users', '/[0-9]+/'], function () {
        });
        $dug->shouldReceive('sourceForPath')->once()->andReturn($source);

        $user = Data::build(['users', 1], ['id' => 1, 'name' => 'Joris']);
        $dug->shouldReceive('data')->once()->andReturn([$user]);

        $input = [
            Data::build(['foo', 1], ['user' => new Reference(['users', 1])])
        ];

        /* When */
        $referenceResolver = new ReferenceResolver($dug);
        $result = $referenceResolver->process($input);

        /* Then */
        assertThat(count($result), is(1));
        assertThat($result[0]->getValue()['user'], is([$user]));
    }

    public function testSingleReferenceToSingle()
    {
        /* Given */
        $dug = \Mockery::mock(Dug::class);
        $source = Source::build(['users', '/[0-9]+/'], function () {
        });
        $dug->shouldReceive('sourceForPath')->once()->andReturn($source);

        $user = Data::build(['users', 1], ['id' => 1, 'name' => 'Joris']);
        $dug->shouldReceive('data')->once()->andReturn([$user]);

        $input = [
            Data::build(['foo', 1], ['user' => new ReferenceToSingle(['users', 1])])
        ];

        /* When */
        $referenceResolver = new ReferenceResolver($dug);
        $result = $referenceResolver->process($input);

        /* Then */
        assertThat(count($result), is(1));
        assertThat($result[0]->getValue()['user'], is($user));
    }

    public function testMultipleReferencesShouldCallOnce()
    {
        /* Given */
        $dug = \Mockery::mock(Dug::class);
        $source = Source::build(['users', '/[0-9]+/'], function () {
        });
        $dug->shouldReceive('sourceForPath')->once()->andReturn($source);

        $user1 = Data::build(['users', 1], ['id' => 1, 'name' => 'Joris']);
        $user2 = Data::build(['users', 2], ['id' => 2, 'name' => 'Jisca']);
        $dug->shouldReceive('data')->once()->andReturn([$user1, $user2]);

        $input = [
            Data::build(['foo', 1], ['user' => new Reference(['users', 1])]),
            Data::build(['foo', 2], ['user' => new Reference(['users', 2])])
        ];

        /* When */
        $referenceResolver = new ReferenceResolver($dug);
        $result = $referenceResolver->process($input);

        /* Then */
        assertThat(count($result), is(2));
        assertThat($result[0]->getValue()['user'], is([$user1]));
        assertThat($result[1]->getValue()['user'], is([$user2]));
    }
}
