<?php

namespace ShabuShabu\Tightrope;

use Illuminate\Support\Str;

/**
 * Hash a password
 *
 * @param string $value
 * @param array  $options
 * @return string
 */
function hash_value(string $value, array $options = []): string
{
    return app('hash')->driver(config('hashing.driver'))->make($value, $options);
}

/**
 * Transform array keys to camelCase
 *
 * @param array $data
 * @return array
 */
function to_camel_case(array $data): array
{
    $out = [];
    foreach ($data as $key => $sub) {
        $out[Str::camel($key)] = is_array($sub) ? to_camel_case($sub) : $sub;
    }

    return $out;
}
