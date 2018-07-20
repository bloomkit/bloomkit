<?php
namespace Bloomkit\Core\Http\Utils;

use Bloomkit\Core\Http\HttpRequest;
use Bloomkit\Core\Http\HttpRedirectResponse;

/**
 * Helper functions for Http stuff
 */
class HttpUtils
{
    /**
     * Creates a redirect Response.
     *
     * @param Request $request A Request instance
     * @param string $path An absolute path or an absolute URL
     * @param int $status The status code
     *            
     * @return RedirectResponse A RedirectResponse instance
     */
    public static function createRedirectResponse(HttpRequest $request, $path, $status = 302)
    {
        return new HttpRedirectResponse(self::generateUri($request, $path), $status);
    }


    /**
     * Generates a URI, based on the given path or URL.
     *
     * @param Request $request A Request instance
     * @param string $path An absolute path or an absolute URL
     *            
     * @return string An absolute URL
     */
    public static function generateUri(HttpRequest $request, $path)
    {
        if ((strpos($path, 'http') === 0) || (!$path))
            return $path;
        
        if ($path[0]== '/')
            return $request->getBaseUrl() . $path;
        
        return $path;       
    }
}
