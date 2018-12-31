<?php

namespace Bloomkit\Core\Http;

/**
 * Helper functions for Http stuff.
 */
class HttpUtils
{
    /**
     * Creates a redirect Response.
     *
     * @param Request $request A Request instance
     * @param string  $path    An absolute path or an absolute URL
     * @param int     $status  The status code
     * @param array   $params  An array with url-params (optional)
     *
     * @return RedirectResponse A RedirectResponse instance
     */
    public static function createRedirectResponse(HttpRequest $request, $path, $status = 302, $params = [])
    {
        $paramStr = '';
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                if (!empty($params)) {
                    $paramStr .= '&';
                }
                $paramStr .= $key.'='.urlencode($value);
            }
        }
        if (!empty($paramStr)) {
            $path .= '?'.$paramStr;
        }

        return new HttpRedirectResponse(self::generateUri($request, $path), $status);
    }

    /**
     * Generates a URI, based on the given path or URL.
     *
     * @param Request $request A Request instance
     * @param string  $path    An absolute path or an absolute URL
     *
     * @return string An absolute URL
     */
    public static function generateUri(HttpRequest $request, $path)
    {
        if ((strpos($path, 'http') === 0) || (!$path)) {
            return $path;
        }

        if ($path[0] == '/') {
            return $request->getBaseUrl().$path;
        }

        return $path;
    }
}
