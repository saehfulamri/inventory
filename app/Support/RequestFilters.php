<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Utilitas bersama untuk membangun array filter dari request HTTP.
 * Menggantikan boilerplate `array_filter([...], fn ($v) => $v !== null)`
 * yang berulang di banyak controller.
 */
final class RequestFilters
{
    /**
     * Kunci cast 'trim': nilai di-trim lalu dibuang bila string kosong.
     */
    public const CAST_TRIM = 'trim';

    /**
     * Kunci cast 'int': nilai di-cast integer bila input filled.
     */
    public const CAST_INT = 'int';

    /**
     * Kunci cast 'boolean': true hanya bila input bernilai truthy, selain itu dibuang.
     */
    public const CAST_BOOLEAN = 'boolean';

    /**
     * Kunci cast 'string' (default): nilai mentah bila input filled.
     */
    public const CAST_STRING = 'string';

    /**
     * @param  array<string, string>  $fields  nama input => salah satu CAST_*
     * @return array<string, mixed>
     */
    public static function from(Request $request, array $fields): array
    {
        $filters = [];

        foreach ($fields as $input => $cast) {
            $value = match ($cast) {
                self::CAST_TRIM => trim((string) $request->input($input)),
                self::CAST_INT => $request->filled($input) ? (int) $request->input($input) : null,
                self::CAST_BOOLEAN => $request->boolean($input) ? true : null,
                default => $request->filled($input) ? $request->input($input) : null,
            };

            if ($value !== null && $value !== '') {
                $filters[$input] = $value;
            }
        }

        return $filters;
    }
}
