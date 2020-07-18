<?php

if (!function_exists('array_build')) {
    function array_build(...$arrays)
    {
        return new Jgile\ArrayBuilder\ArrayBuilder(...$arrays);
    }
}
