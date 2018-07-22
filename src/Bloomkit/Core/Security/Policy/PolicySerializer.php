<?php

namespace Bloomkit\Core\Security\Policy;

use Bloomkit\Core\Security\Policy\Exceptions\PolicyInvalidException;

/**
 * Serializes / Deserializes a Policy to / from json
 */
class PolicySerializer
{
    /**
     * @var Policy
     */
    private $policy;

    /**
     * Constructor.
     *
     * @param Policy $polic The policy to serialize/deserialize
     */
    public function __construct(Policy $policy)
    {
        $this->policy = $policy;
    }

    /**
     * Deserializer
     *
     * @param string $jsonPolicy A policy-json-string to deserialize
     */
    public function deserialize($jsonPolicy)
    {
        $this->policy->clearStatements();
        $data = json_decode($jsonPolicy, true);
        if (is_null($data)) {
            throw new PolicyInvalidException('Invalid policy string');
        }
        $version = null;
        $statements = null;
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            if ($key == 'version') {
                $version = $value;
            } elseif ($key == 'statement') {
                $statements = $value;
            }
        }
        if (is_null($version)) {
            throw new PolicyInvalidException('Invalid policy string: version is missing');
        }
        if ((!is_array($statements)) || (count($statements) == 0)) {
            throw new PolicyInvalidException('Invalid policy string: no statements found');
        }
        $this->policy->setVersion($version);
        foreach ($statements as $statement) {
            $sid = null;
            $effect = null;
            $action = null;
            $resource = null;
            foreach ($statement as $key => $value) {
                $key = strtolower($key);
                if ($key == 'sid') {
                    $sid = $value;
                } elseif ($key == 'effect') {
                    $effect = strtolower($value);
                } elseif ($key == 'action') {
                    $action = $value;
                } elseif ($key == 'resource') {
                    $resource = $value;
                }
            }
            if (is_null($action)) {
                throw new PolicyInvalidException('Invalid policy string: action is missing for statement '.$sid);
            }
            if (is_null($resource)) {
                throw new PolicyInvalidException('Invalid policy string: resource is missing for statement '.$sid);
            }
            if (is_null($effect)) {
                throw new PolicyInvalidException('Invalid policy string: effect is missing for statement '.$sid);
            }
            if ($effect == 'allow') {
                $effect = Statement::EFFECT_ALLOW;
            } elseif ($effect == 'allow') {
                $effect = Statement::EFFECT_DENY;
            } else {
                throw new PolicyInvalidException('Invalid policy string: effect is invalid for statement '.$sid);
            }
            if (is_array($action) == false) {
                $action = array($action);
            }
            if (is_array($resource) == false) {
                $resource = array($resource);
            }
            $statement = new Statement($sid, $effect, $action, $resource);
            $this->policy->addStatement($statement);
        }
    }

    /**
     * Serializer
     *
     * @return string The serialized policy-json-string 
     */
    public function serialize()
    {
        throw new \Exception('not yet implemented');
    }
}
