<?php

namespace App\Actions\Links;

use App\Models\Link;

class ResolveShortCodeFromInput
{
    public function extractCode(string $input): ?string
    {
        $input = trim($input);

        if ($input === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z0-9]{8}$/', $input) === 1) {
            return $input;
        }

        $path = parse_url($input, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        $segment = basename(rtrim($path, '/'));

        if (preg_match('/^[A-Za-z0-9]{8}$/', $segment) !== 1) {
            return null;
        }

        return $segment;
    }

    public function findLink(string $input): ?Link
    {
        $code = $this->extractCode($input);

        if ($code === null) {
            return null;
        }

        return Link::query()->where('short_code', $code)->first();
    }
}
