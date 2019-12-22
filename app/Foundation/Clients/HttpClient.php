<?php

namespace App\Foundation\Clients;

use JsonRPC\Exception\ConnectionFailureException;
use JsonRPC\HttpClient as JsonRPCHttpClient;

/**
 * Class HttpClient
 *
 * @package JsonRPC
 * @author  Frederic Guillot
 */
class HttpClient extends JsonRPCHttpClient
{
    /**
     * Throw an exception according the HTTP response
     *
     * @param array $headers
     *
     * @throws AccessDeniedException
     * @throws ConnectionFailureException
     * @throws ServerErrorException
     */
    public function handleExceptions(array $headers)
    {
        $exceptions = [
            '401' => '\JsonRPC\Exception\AccessDeniedException',
            '403' => '\JsonRPC\Exception\AccessDeniedException',
            '404' => '\JsonRPC\Exception\ConnectionFailureException',
            //'500' => '\JsonRPC\Exception\ServerErrorException',
        ];

        foreach ($headers as $header) {
            foreach ($exceptions as $code => $exception) {
                if (strpos($header, 'HTTP/1.0 '.$code) !== false || strpos($header, 'HTTP/1.1 '.$code) !== false) {
                    throw new $exception('Response: '.$header,);
                }
            }
        }
    }
}
