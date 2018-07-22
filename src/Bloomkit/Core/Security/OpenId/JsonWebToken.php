<?php

namespace Bloomkit\Core\Security\OpenId;

class JsonWebToken
{
    /**
     * Issuer Identifier (Auth-Server-URL) REQUIRED.
     *
     * @var string
     */
    private $iss;

    /**
     * Subject Identifier (Unique End-User ID) REQUIRED Max 255Chars.
     *
     * @var string
     */
    private $sub;

    /**
     * Audience(s) (Client-ID) REQUIRED.
     *
     * @var string
     */
    private $aud;

    /**
     * Expiration time of the ID-Token REQUIRED UTC.
     *
     * @var int
     */
    private $exp;

    /**
     * Issuing Timestamp REQUIRED UTC.
     *
     * @var int
     */
    private $iat;

    /**
     * Time when the End-User authentication occurred UTC
     * When a max_age request is made or when auth_time is requested
     * as an Essential Claim, then this Claim is REQUIRED;.
     *
     * @var int
     */
    private $auth_time;

    /**
     * Used to associate a Client session with an ID Token, and to mitigate
     * replay attacks The value is passed through unmodified from the
     * Authentication Request to the ID Token.
     *
     * @var string
     */
    private $nonce;

    /**
     * Authentication Context Class Reference OPTIONAL.
     *
     * @var string
     */
    private $acr;

    /**
     * Authentication Methods References.
     * OPTIONAL
     * Array Identifiers for authentication methods used in the authentication.
     *
     * @var array
     */
    private $amr;

    /**
     * Authorized party - the party to which the ID Token was issued.
     * OPTIONAL
     * If present, it MUST contain the OAuth 2.0 Client ID of this party.
     *
     * @var string
     */
    private $azp;

    /**
     * Array with Custom Claims OPTIONAL.
     *
     * @var array
     */
    private $cstmClaims;

    /**
     * Constructor.
     *
     * @param string $iss Issuer Identifier (Auth-Server-URL)
     * @param string $sub Subject Identifier (Unique End-User ID)
     * @param string $aud Audience(s) (Client-ID)
     * @param int    $exp Expiration time of the ID-Token
     * @param int    $iat Issuing Timestamp
     */
    public function __construct($iss, $sub, $aud, $exp, $iat)
    {
        $this->iss = $iss;
        $this->sub = $sub;
        $this->aud = $aud;
        $this->exp = $exp;
        $this->iat = $iat;
        $this->cstmClaims = [];
    }

    /**
     * Returns the Authentication Context Class Reference.
     *
     * @return string The Authentication Context Class Reference
     */
    public function getAcr()
    {
        return $this->acr;
    }

    /**
     * Returns the Authentication Methods References.
     *
     * @return string Authentication Methods References
     */
    public function getAmr()
    {
        return $this->amr;
    }

    /**
     * Returns the Audience(s) (Client-ID).
     *
     * @return string ClientId
     */
    public function getAud()
    {
        return $this->aud;
    }

    /**
     * Returns the time when the End-User authentication occurred.
     *
     * @return int AuthTime
     */
    public function getAuthTime()
    {
        return $this->auth_time;
    }

    /**
     * Returns the Authorized party.
     *
     * @return string Authorized party
     */
    public function getAzp()
    {
        return $this->azp;
    }

    /**
     * Returns an entry from the CustomClaim list.
     *
     * @param string $key     The key of the entry to return
     * @param mixed  $default Default value to return if the key does not exist
     *
     * @return mixed The value to return
     */
    public function getCustomClaim($key, $default = null)
    {
        if (array_key_exists($key, $this->cstmClaims)) {
            return $this->cstmClaims[$key];
        } else {
            return $default;
        }
    }

    /**
     * Returns the Expiration time of the ID-Token.
     *
     * @return int Expiration time
     */
    public function getExp()
    {
        return $this->exp;
    }

    /**
     * Returns the Issuing Timestamp.
     *
     * @return int The issuing timestamp
     */
    public function getIat()
    {
        return $this->iat;
    }

    /**
     * Returns the Issuer Identifier.
     *
     * @return string Issuer Identifier
     */
    public function getIss()
    {
        return $this->iss;
    }

    /**
     * Returns the Nonce.
     *
     * @return string Nonce
     */
    public function getNonce()
    {
        return $this->nonce;
    }

    /**
     * Returns the Subject Identifier (Unique End-User ID).
     *
     * @return string Subject Identifier
     */
    public function getSub()
    {
        return $this->sub;
    }

    /**
     * Returns the token as string.
     *
     * @return string Token
     */
    public function getToken()
    {
        $token = [];
        $token['iss'] = $this->iss;
        $token['sub'] = $this->sub;
        $token['aud'] = $this->aud;
        $token['exp'] = $this->exp;
        $token['iat'] = $this->iat;
        if (isset($this->auth_time)) {
            $token['auth_time'] = $this->auth_time;
        }
        if (isset($this->nonce)) {
            $token['nonce'] = $this->nonce;
        }
        if (isset($this->acr)) {
            $token['acr'] = $this->acr;
        }
        if (isset($this->amr)) {
            $token['amr'] = $this->amr;
        }
        if (isset($this->azp)) {
            $token['azp'] = $this->azp;
        }
        foreach ($this->cstmClaims as $key => $value) {
            if (array_key_exists($key, $token)) {
                continue;
            }
            $token[$key] = $value;
        }

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $parts = [];
        $parts[] = str_replace('=', '', strtr(base64_encode(json_encode($header)), '+/', '-_'));
        $parts[] = str_replace('=', '', strtr(base64_encode(json_encode($token)), '+/', '-_'));
        $toSign = implode('.', $parts);
        $signature = hash_hmac('sha256', $toSign, 'secret', true);
        $parts[] = str_replace('=', '', strtr(base64_encode($signature), '+/', '-_'));

        return implode('.', $parts);
    }

    /**
     * Removes an entry from the CustomClaim list.
     *
     * @param string $key The key of the entry to remove
     */
    public function removeCustomClaim($key)
    {
        unset($this->cstmClaims[$key]);
    }

    /**
     * Add or replace an entry to the CustomClaim list.
     *
     * @param string $key   The key of the value
     * @param mixed  $value The value
     */
    public function setCustomClaim($key, $value)
    {
        $this->cstmClaims[$key] = $value;
    }
}
