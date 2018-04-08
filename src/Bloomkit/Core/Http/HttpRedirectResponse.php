<?php

namespace Bloomkit\Core\Http;

class HttpRedirectResponse extends HttpResponse
{
    /**
     * @var string
     */
    protected $targetUrl;

    /**
     * Constructor.
     *
     * @param string $url     The URL to redirect to
     * @param int    $status  The status code (302 by default)
     * @param array  $headers The headers
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($url, $status = 302, $headers = array())
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        parent::__construct('', $status, $headers);

        $this->setTargetUrl($url);

        if (!$this->isRedirect()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code is not a redirect ("%s" given).', $status));
        }
    }

    /**
     * Create and return a HttpRedirectResponse with the given redirect-url.
     *
     * @param string $url URL to redirect to
     *
     * @return HttpRedirectResponse
     */
    public static function createResponse($url)
    {
        return new HttpRedirectResponse($url);
    }

    /**
     * Returns the target URL.
     *
     * @return string target URL
     */
    public function getTargetUrl()
    {
        return $this->targetUrl;
    }

    /**
     * Set content and header to target url.
     *
     * @param string $url The URL to redirect to
     *
     * @throws \InvalidArgumentException
     */
    public function setTargetUrl($url)
    {
        if (empty($url)) {
            throw new \InvalidArgumentException('The redirect URL cannot be empty');
        }

        $this->targetUrl = $url;
        $content = '<!DOCTYPE html><html><head><meta charset="UTF-8" /><meta http-equiv="refresh" '.
            'content="1;url=%1$s" /><title>Redirecting to %1$s</title></head><body>Redirecting to '.
            '<a href="%1$s">%1$s</a></body></html>';

        $this->setContent(sprintf($content, htmlspecialchars($url, ENT_QUOTES, 'UTF-8')));

        $this->headers->set('Location', $url);
    }
}
