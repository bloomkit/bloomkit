<?php

namespace Bloomkit\Core\Http;

class Cookie
{
    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string
     */
    protected $sameSite;

    /**
     * @var int
     */
    protected $expire;

    /**
     * @var bool
     */
    protected $httpOnly;

    /**
     * @var bool
     */
    protected $isRaw;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var bool
     */
    protected $secureOnly;

    /**
     * @var bool
     */
    protected $raw;

    /**
     * @var mixed|null
     */
    protected $value;

    /**
     * Constructor.
     *
     * @param string                        $name       The name of the cookie
     * @param string|null                   $value      The value of the cookie
     * @param int|string|\DateTimeInterface $expire     The time the cookie expires
     * @param string                        $path       The path on the server in which the cookie will be available on
     * @param string|null                   $domain     The domain that the cookie is available to
     * @param bool                          $secureOnly Whether the cookie should only be transmitted over a secure HTTPS connection from the client
     * @param bool                          $httpOnly   Whether the cookie will be made accessible only through the HTTP protocol
     * @param bool                          $raw        Whether the cookie value should be sent with no url encoding
     * @param string|null                   $sameSite   Whether the cookie will be available for cross-site requests
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($name, $value = null, $expire = 0, $path = '/', $domain = null, $secureOnly = false, $httpOnly = true, $raw = false, $sameSite = null)
    {
        // from PHP source code
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }
        if (empty($name)) {
            throw new \InvalidArgumentException('The cookie name cannot be empty.');
        }
        // convert expiration time to a Unix timestamp
        if ($expire instanceof \DateTimeInterface) {
            $expire = $expire->format('U');
        } elseif (!is_numeric($expire)) {
            $expire = strtotime($expire);
            if (false === $expire) {
                throw new \InvalidArgumentException('The cookie expiration time is not valid.');
            }
        }
        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->sameSite = $sameSite;
        $this->expire = 0 < $expire ? (int) $expire : 0;
        $this->path = empty($path) ? '/' : $path;
        $this->secureOnly = (bool) $secureOnly;
        $this->httpOnly = (bool) $httpOnly;
        $this->raw = (bool) $raw;
    }

    /**
     * Gets the domain that the cookie is available to.
     *
     * @return string|null
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Gets the time the cookie expires.
     *
     * @return int
     */
    public function getExpiresTime()
    {
        return $this->expire;
    }

    /**
     * Gets the max-age attribute.
     *
     * @return int
     */
    public function getMaxAge()
    {
        if (0 !== $this->expire) {
            return $this->expire - time();
        }

        return 0;
    }

    /**
     * Gets the name of the cookie.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the path on the server in which the cookie will be available on.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Gets the SameSite attribute.
     *
     * @return string|null
     */
    public function getSameSite()
    {
        return $this->sameSite;
    }

    /**
     * Gets the value of the cookie.
     *
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Whether this cookie is expired.
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->expire < time();
    }

    /**
     * Checks whether the cookie will be made accessible only through the HTTP protocol.
     *
     * @return bool
     */
    public function isHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * Checks if the cookie value should be sent with no url encoding.
     *
     * @return bool
     */
    public function isRaw()
    {
        return $this->raw;
    }

    /**
     * Checks whether the cookie should only be transmitted over a secure HTTPS connection from the client.
     *
     * @return bool
     */
    public function isSecureOnly()
    {
        return $this->secureOnly;
    }
}
