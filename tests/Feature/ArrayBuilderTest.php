<?php

it('returns an array when passing an array', function () {
    $builder = new Jgile\ArrayBuilder\ArrayBuilder(['foo']);
    assertTrue(is_array($builder->toArray()));
});

it('returns an array when passing a callable', function () {
    $builder = new Jgile\ArrayBuilder\ArrayBuilder(fn () => ['foo']);
    assertTrue(is_array($builder->toArray()));
});

test('helper creates new builder', function () {
    assertTrue(is_array(array_build(['foo'])->toArray()));
});

test('merges values when true', function () {
    assertEquals(['foo', 'bar'], array_build(fn ($build) => [
        'foo',
        $build->mergeWhen(true, ['bar']),
    ])->toArray());
});
test('does not merge values when false', function () {
    assertEquals(['foo'], array_build(fn ($build) => [
        'foo',
        $build->mergeWhen(false, ['bar']),
    ])->toArray());
});

test('merge merges multiple arrays', function () {
    assertEquals(['foo', 'bar', 'baz'], array_build(fn ($build) => [
        'foo',
        $build->merge(['bar']),
        $build->merge(['baz']),
    ])->toArray());
});
