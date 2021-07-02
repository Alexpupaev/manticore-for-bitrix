<?php

declare(strict_types=1);

namespace BitrixManticore\Services\Routing;

class Response
{
    /**
     * Morph data into JSON
     *
     * @param boolean $success
     * @param mixed $data
     * @param array $errors
     * @return string
     */
    public static function Json(bool $success, $data = null, array $errors = null)
    {
        return json_encode(
            [
                'success' => $success,
                'data' => $data,
                'errors' => $errors ?? false
            ]
        );
    }
}