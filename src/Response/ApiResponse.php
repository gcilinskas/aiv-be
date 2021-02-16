<?php

namespace App\Response;

/**
 * Class ApiResponse
 */
class ApiResponse
{
    /**
     * @param array $data
     * @param array $meta
     *
     * @return array|array[]
     */
    public static function format($data = [], $meta = []): array
    {
        return [
            'data' => $data,
            'meta' => $meta
        ];
    }
}
