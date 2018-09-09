<?php

namespace Bloomkit\Core\Security\Policy;

use Bloomkit\Core\Security\Policy\Exceptions\PolicyInvalidException;

/**
 * A policy contains a list of statements what action is allowed on which resource.
 */
class Policy
{
    const VERSION = '2017-01-01';

    /**
     * @var array
     */
    private $statements = [];

    /**
     * @var array
     */
    private $supportedVersions = [self::VERSION];

    /**
     * @var string
     */
    private $version = self::VERSION;

    /**
     * Constructor.
     *
     * @param string $version The policy version to use
     */
    public function __construct($version = self::VERSION)
    {
        $this->setVersion($version);
    }
    
    /**
     * Check if an action is allowed by the policy.
     *
     * @param string $actionCheck    The action to check
     * @param string $actionPolicy   The action pattern set by the policy
     * @param bool   $allowWildcards If true (default) wildcards are allowed by the check
     *
     * @return bool Returns true if access is allowed, false if not
     */
    private function actionMatchCheck($actionCheck, $actionPolicy, $allowWildcards = true)
    {
        $actNsChk = null;
        $actNsPol = null;

        $partsChk = explode(':', $actionCheck);
        if (count($partsChk) > 1) {
            $actNsChk = array_shift($partsChk);
            $actNameChk = implode(':', $partsChk);
        } else {
            $actNameChk = $partsChk[0];
        }

        $partsPol = explode(':', $actionPolicy);
        if (count($partsPol) > 1) {
            $actNsPol = array_shift($partsPol);
            $actNamePol = implode(':', $partsPol);
        } else {
            $actNamePol = $partsPol[0];
        }

        if (($actNsChk !== $actNsPol)) {
            return false;
        }

        foreach ($partsPol as $partPol) {
            if ($partPol == '*') {
                return true;
            }
            if (count($partsChk) == 0) {
                return false;
            }
            $partChk = array_shift($partsChk);
            if ($partChk !== $partPol) {
                return false;
            }
        }
        if (count($partsChk) > 0) {
            return false;
        }

        return true;
    }

    /**
     * Add a statement to this policy.
     *
     * @param Statement $statement The statement to add
     */
    public function addStatement(Statement $statement)
    {
        $this->statements[] = $statement;
    }

    /**
     * Add a policy version to the supported versions
     *
     * @param string $version The version to support
     */
    public function addSupportedVersion($version)
    {
        $this->supportedVersions[] = $version;
    }
    
    /**
     * Clears all statements of this policy.
     */
    public function clearStatements()
    {
        $this->statements = [];
    }

    /**
     * Returns the capabilities provided by this policy.
     *
     * @return array The capabilities of this policy
     */
    public function getCapabilities()
    {
        $caps = [];
        foreach ($this->statements as $statement) {
            $stmtActions = $statement->getActions();
            foreach ($stmtActions as $stmtAction) {
                $caps[] = $stmtAction;
            }
        }

        return $caps;
    }

    /**
     * Returns the statements of this policy.
     *
     * @return array The statements of this policy
     */
    public function getStatements()
    {
        return $this->statements;
    }

    /**
     * Check if an action on a resource is allowed by this policy.
     *
     * @param string $action   The action to check
     * @param string $resource The resource to check
     *
     * @return bool Returns true if access is allowed, false if not
     */
    public function isAllowed($action, $resource)
    {
        $actionAllowed = false;
        foreach ($this->statements as $statement) {
            $stmtActions = $statement->getActions();
            foreach ($stmtActions as $stmtAction) {
                if ($this->actionMatchCheck($action, $stmtAction, true) === true) {
                    $actionAllowed = true;
                    break;
                }
            }
            if ($actionAllowed) {
                $stmtResources = $statement->getResources();
                foreach ($stmtResources as $stmtRes) {
                    if ($this->resourceMatchCheck($resource, $stmtRes, true) === true) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if the access to a resource is allowed by the policy.
     *
     * @param string $resCheck       The resource to check
     * @param string $resPolicy      The resource pattern set by the policy
     * @param bool   $allowWildcards If true (default) wildcards are allowed by the check
     *
     * @return bool Returns true if access is allowed, false if not
     */
    private function resourceMatchCheck($resCheck, $resPolicy, $allowWildcards = true)
    {
        $resNsChk = null;
        $resNsPol = null;

        $partsChk = explode(':', $resCheck);
        if (count($partsChk) > 1) {
            $resNsChk = array_shift($partsChk);
            $resNameChk = implode(':', $partsChk);
        } else {
            $resNameChk = $partsChk[0];
        }

        $partsPol = explode(':', $resPolicy);
        if (count($partsPol) > 1) {
            $resNsPol = array_shift($partsPol);
            $resNamePol = implode(':', $partsPol);
        } else {
            $resNamePol = $partsPol[0];
        }

        if (($resNsChk === $resNsPol) &&
            (($resNameChk === $resNamePol) || (($resNamePol == '*') && ($allowWildcards == true)))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set the version of the policy.
     *
     * @param string $version The policy version to use
     */
    public function setVersion($version)
    {
        if (!in_array($version, $this->supportedVersions)) {
            throw new PolicyInvalidException('Policy version is not supported: '.$version);
        }
        $this->version = $version;
    }
}
