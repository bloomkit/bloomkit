<?php

namespace Bloomkit\Core\Rest;

use Bloomkit\Core\Http\HttpResponse;

/**
 * Representation of a REST response.
 */
class RestResponse extends HttpResponse
{
    /**
     * Constructor.
     * 
     * {@inheritdoc}
     */
    public function __construct($content = '', $statusCode = 200, $headers = [], $cookies = [])
    {
        $headers['Content-type'] = 'application/json; charset=utf-8';
        parent::__construct($content, $statusCode, $headers, $cookies);
    }

    /**
     * Create a REST response with an error.
     *
     * @param int $statusCode The HTTP status-code to return
     * @param string $message The REST error message
     * @param int $faultCode The REST error code
     * 
     * @return RestResponse The created response
     */
    public static function createFault($statusCode, $message, $faultCode = 0)
    {
        $error = array();
        $error['error'] = array();
        $error['error']['message'] = $message;
        $error['error']['faultCode'] = $faultCode;

        return new RestResponse(json_encode($error), $statusCode);
    }

    /**
     * Create a REST response.
     *
     * @param int $statusCode The HTTP status-code to return
     * @param array $data The data to return
     * 
     * @return RestResponse The created response  
     */
    public static function createResponse($statusCode, array $data)
    {
        return new RestResponse(json_encode($data), $statusCode);
    }
}
