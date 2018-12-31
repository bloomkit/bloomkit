<?php

namespace Bloomkit\Core\Security\OpenId;

/**
 * Representation of a Json Web Token (RFC7519).
 */
class JsonWebToken
{
    /**
     * Claims defined as registered by RFC7519.
     *
     * @var array
     */
    const REGISTERED_CLAIMS = ['iss', 'sub', 'aud', 'exp', 'nbf', 'iat', 'jti'];

    /**
     * Authentication Context Class Reference OPTIONAL.
     *
     * @var string
     */
    private $acr;

    /**
     * Algorithm for signature creation/validation.
     *
     * @var string
     */
    private $algorithm;

    /**
     * Authentication Methods References.
     * OPTIONAL
     * Array Identifiers for authentication methods used in the authentication.
     *
     * @var array
     */
    private $amr;

    /**
     * Audience(s) (Client-ID) REQUIRED.
     *
     * @var string
     */
    private $aud;

    /**
     * Time when the End-User authentication occurred UTC
     * When a max_age request is made or when auth_time is requested
     * as an Essential Claim, then this Claim is REQUIRED;.
     *
     * @var int
     */
    private $auth_time;

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
     * Expiration time of the ID-Token REQUIRED UTC.
     *
     * @var int
     */
    private $exp;

    /**
     * Encoded header.
     *
     * @var string
     */
    private $headerRaw;

    /**
     * Issuing Timestamp REQUIRED UTC.
     *
     * @var int
     */
    private $iat;

    /**
     * Issuer Identifier (Auth-Server-URL) REQUIRED.
     *
     * @var string
     */
    private $iss;

    /**
     * Used to associate a Client session with an ID Token, and to mitigate
     * replay attacks The value is passed through unmodified from the
     * Authentication Request to the ID Token.
     *
     * @var string
     */
    private $nonce;

    /**
     * Encoded payload (claims.
     *
     * @var string
     */
    private $payloadRaw;

    /**
     * Signature of a signed token.
     *
     * @var string
     */
    private $signature;

    /**
     * Subject Identifier (Unique End-User ID) REQUIRED Max 255Chars.
     *
     * @var string
     */
    private $sub;

    /**
     * Supported algorithms for signing.
     *
     * @var array
     */
    private static $supportedAlgorithms = [
            'HS256' => ['hash_hmac', 'SHA256'],
            'HS512' => ['hash_hmac', 'SHA512'],
            'HS384' => ['hash_hmac', 'SHA384'],
            'RS256' => ['openssl', 'SHA256'],
            'RS384' => ['openssl', 'SHA384'],
            'RS512' => ['openssl', 'SHA512'],
    ];

    /**
     * Timestamp to check tokens (for testing).
     *
     * @var int | null
     */
    private $timestamp = null;

    /**
     * Constructor.
     *
     * @param string $iss       Issuer Identifier (Auth-Server-URL)
     * @param string $sub       Subject Identifier (Unique End-User ID)
     * @param string $aud       Audience(s) (Client-ID)
     * @param int    $exp       Expiration time of the ID-Token
     * @param int    $iat       Issuing Timestamp
     * @param string $algorithm Algorithm used for signing/verifying
     */
    public function __construct($iss, $sub, $aud, $exp, $iat, $algorithm = 'HS256')
    {
        $this->iss = $iss;
        $this->sub = $sub;
        $this->aud = $aud;
        $this->exp = $exp;
        $this->iat = $iat;
        $this->algorithm = $algorithm;
        $this->cstmClaims = [];
    }

    public static function decode($jwt)
    {
        $parts = explode('.', $jwt);
        if (count($parts) != 3) {
            throw new \UnexpectedValueException('Wrong number of segments');
        }

        $headRaw = $parts[0];
        $payloadRaw = $parts[1];
        $sigRaw = $parts[2];

        $header = static::rawDecode($headRaw);
        $payload = static::rawDecode($payloadRaw);
        $sig = static::rawDecode($sigRaw, false);

        if (is_null($header)) {
            throw new \Exception('Invalid or missing header');
        }
        if (is_null($payload)) {
            throw new \Exception('Invalid or missing payload');
        }
        if (is_null($sig)) {
            throw new \Exception('Invalid or missing signature');
        }
        if (empty($header->alg)) {
            throw new \UnexpectedValueException('Empty algorithm');
        }
        if (empty(static::$supportedAlgorithms[$header->alg])) {
            throw new \UnexpectedValueException('Algorithm not supported');
        }
        $jwt = new JsonWebToken($payload->iss, $payload->sub, $payload->aud, $payload->exp, $payload->iat, $header->alg);
        $jwt->signature = $sig;
        $jwt->headerRaw = $headRaw;
        $jwt->payloadRaw = $payloadRaw;

        $claims = get_object_vars($payload);
        foreach ($claims as $claim => $value) {
            if (!in_array($claim, static::REGISTERED_CLAIMS)) {
                $jwt->setCustomClaim($claim, $value);
            }
        }

        return $jwt;
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
    public function getTokenString($signKey)
    {
        if (!array_key_exists($this->algorithm, static::$supportedAlgorithms)) {
            throw new \DomainException('Algorithm not supported: '.$this->algorithm);
        }
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

        $header = ['alg' => $this->algorithm, 'typ' => 'JWT'];
        $parts = [];
        $parts[] = str_replace('=', '', strtr(base64_encode(json_encode($header)), '+/', '-_'));
        $parts[] = str_replace('=', '', strtr(base64_encode(json_encode($token)), '+/', '-_'));
        $toSign = implode('.', $parts);
        $signature = self::sign($toSign, $signKey, $this->algorithm);
        $parts[] = str_replace('=', '', strtr(base64_encode($signature), '+/', '-_'));

        return implode('.', $parts);
    }

    /**
     * Descodes a base64 encoded token part.
     *
     * @param string $input  The value to decode
     * @param bool   $asJson True to output as Json object, false to output as string
     *
     * @return string|object
     */
    private static function rawDecode($input, $asJson = true)
    {
        $remainder = strlen($input) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $input .= str_repeat('=', $padlen);
        }
        $input = base64_decode(strtr($input, '-_', '+/'));

        if ($asJson) {
            $result = json_decode($input, false, 512, JSON_BIGINT_AS_STRING);
        } else {
            $result = $input;
        }

        return $result;
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

    /**
     * Sign a token with a specific key and algorithm.
     *
     * @param string $msg The text to sign
     * @param string $key The key to use for signing
     * @param mixed  $alg The algorithm to use for signing
     */
    private static function sign($msg, $key, $alg)
    {
        if (!array_key_exists($alg, static::$supportedAlgorithms)) {
            throw new \DomainException('Algorithm not supported: '.$alg);
        }
        $type = static::$supportedAlgorithms[$alg][0];
        $algorithm = static::$supportedAlgorithms[$alg][1];

        if ($type === 'hash_hmac') {
            return hash_hmac($algorithm, $msg, $key, true);
        } elseif ($type === 'openssl') {
            $signature = '';
            $success = @openssl_sign($msg, $signature, $key, $algorithm);
            if (!$success) {
                throw new \DomainException('Signing failed - openSSL error: '.openssl_error_string());
            }

            return $signature;
        }
    }

    /**
     * Verify a token with a specific key and algorithm.
     *
     * @param string $key The key to use
     */
    public function verify($key)
    {
        $timestamp = $this->timestamp;
        if (is_null($timestamp)) {
            $timestamp = time();
        }

        if (empty($key)) {
            throw new \InvalidArgumentException('Key may not be empty');
        }

        if (isset($this->payload->iat) && $this->payload->iat > ($timestamp)) {
            throw new \Exception('Cannot handle token prior to '.date(DateTime::ISO8601, $payload->iat));
        }
        if (isset($this->payload->exp) && ($timestamp) >= $this->payload->exp) {
            throw new \Exception('Expired token');
        }
        $msg = $this->headerRaw.$this->payloadRaw;
        $signature = $this->signature;
        $alg = $this->algorithm;

        $type = static::$supportedAlgorithms[$alg][0];
        $algorithm = static::$supportedAlgorithms[$alg][1];

        if ($type === 'hash_hmac') {
            $hash = hash_hmac($algorithm, $msg, $key, true);
            if (function_exists('hash_equals')) {
                return hash_equals($signature, $hash);
            }
            $len = min(static::safeStrlen($signature), static::safeStrlen($hash));
            $status = 0;
            for ($i = 0; $i < $len; ++$i) {
                $status |= (ord($signature[$i]) ^ ord($hash[$i]));
            }
            $status |= (static::safeStrlen($signature) ^ static::safeStrlen($hash));

            return $status === 0;
        } elseif ($type === 'openssl') {
            $success = @openssl_verify($msg, $signature, $key, $algorithm);
            if ($success === 1) {
                return true;
            } elseif ($success === 0) {
                return false;
            }
            throw new \DomainException('Verifying failed - openSSL error: '.openssl_error_string());
        }
    }
}
