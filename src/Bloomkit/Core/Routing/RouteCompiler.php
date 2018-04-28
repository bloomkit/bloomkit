<?php

namespace Bloomkit\Core\Routing;

/**
 * Class for compiling a route to a set of regex.
 */
class RouteCompiler
{
    /**
     * Compiling the route parameters to a set of regexp.
     *
     * @param Route $route The route to compile
     *
     * @return CompiledRoute A CompiledRoute object containing the regexp
     */
    public static function compile(Route $route)
    {
        $hostVariables = [];
        $variables = [];
        $hostRegex = null;
        $hostTokens = [];

        $host = $route->getHost();

        //Compile host pattern
        if ($host !== '') {
            $result = self::compilePattern($route, $host, true);
            $hostVariables = $result['variables'];
            $variables = array_merge($variables, $hostVariables);
            $hostTokens = $result['tokens'];
            $hostRegex = $result['regex'];
        }

        //Compile path pattern
        $result = self::compilePattern($route, $route->getPath(), false);
        $staticPrefix = $result['staticPrefix'];
        $pathVariables = $result['variables'];
        $variables = array_merge($variables, $pathVariables);
        $tokens = $result['tokens'];
        $regex = $result['regex'];

        return new CompiledRoute($staticPrefix, $regex, $tokens, $pathVariables, $hostRegex, $hostTokens, $hostVariables, array_unique($variables));
    }

    private static function compilePattern(Route $route, $pattern, $isHost)
    {
        $tokens = [];
        $variables = [];
        $matches = [];
        $pos = 0;
        $staticPrefix = '';

        //Get all route params and handle them
        preg_match_all('#\{\w+\}#', $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($matches as $match) {
            self::getParamPattern($route, $match, $pattern, $pos, $variables, $isHost, $tokens);
        }

        if ($pos < strlen($pattern)) {
            $tokens[] = ['text', substr($pattern, $pos)];
        }

        $firstOptional = PHP_INT_MAX;
        $tokenCnt = count($tokens);

        if (!$isHost) {
            for ($i = $tokenCnt - 1; $i >= 0; --$i) {
                $token = $tokens[$i];
                if ('variable' === $token[0] && $route->hasAttribute($token[3])) {
                    $firstOptional = $i;
                } else {
                    break;
                }
            }
        }

        $regexp = '';
        for ($i = 0; $i < $tokenCnt; ++$i) {
            $regexp .= self::computeRegexp($tokens, $i, $firstOptional);
        }
        $regexp = '#^'.$regexp.'$#sD';

        if ($tokens[0][0] === 'text') {
            $staticPrefix = $tokens[0][1];
        }

        return array(
            'staticPrefix' => $staticPrefix,
            'regex' => $regexp,
            'tokens' => array_reverse($tokens),
            'variables' => $variables,
        );
    }

    private static function computeRegexp(array $tokens, $index, $firstOptional)
    {
        $token = $tokens[$index];

        if ($token[0] === 'text') {
            return preg_quote($token[1], '#');
        } else {
            if (0 === $index && 0 === $firstOptional) {
                return sprintf('%s(?P<%s>%s)?', preg_quote($token[1], '#'), $token[3], $token[2]);
            } else {
                $regexp = sprintf('%s(?P<%s>%s)', preg_quote($token[1], '#'), $token[3], $token[2]);
                if ($index >= $firstOptional) {
                    $regexp = "(?:$regexp";
                    $nbTokens = count($tokens);
                    if ($nbTokens - 1 == $index) {
                        $regexp .= str_repeat(')?', $nbTokens - $firstOptional - (0 === $firstOptional ? 1 : 0));
                    }
                }

                return $regexp;
            }
        }
    }

    private static function getParamPattern($route, array $match, $pattern, &$pos, &$variables, $isHost, &$tokens)
    {
        $separators = ['/', ',', ';', '.', ':', '-', '_', '~', '+', '*', '=', '@', '|'];

        $paramName = substr($match[0][0], 1, -1);
        $paramPos = $match[0][1];
        $paramRegexp = $route->getRequirement($paramName);

        $prevText = substr($pattern, $pos, $paramPos - $pos);
        $pos = $paramPos + strlen($paramName) + 2;

        $prevChar = '';
        if (strlen($prevText) > 0) {
            $prevChar = substr($prevText, -1);
        }

        $isSeparator = in_array($prevChar, $separators);

        if (is_numeric($paramName)) {
            throw new \DomainException(sprintf('Variable name "%s" cannot be numeric in route pattern "%s". Please use a different name.', $paramName, $pattern));
        }
        if (in_array($paramName, $variables)) {
            throw new \LogicException(sprintf('Route pattern "%s" cannot reference variable name "%s" more than once.', $pattern, $paramName));
        }
        if ($isSeparator && strlen($prevText) > 1) {
            $tokens[] = ['text', substr($prevText, 0, -1)];
        } elseif (!$isSeparator && strlen($prevText) > 0) {
            $tokens[] = ['text', $prevText];
        }

        if (is_null($paramRegexp)) {
            $followingPattern = (string) substr($pattern, $pos);

            if ('' == $followingPattern) {
                $nextSeparator = '';
            } else {
                $pattern = preg_replace('#\{\w+\}#', '', $pattern);

                if (isset($pattern[0]) && (in_array($pattern[0], $separators))) {
                    $nextSeparator = $pattern[0];
                } else {
                    $nextSeparator = '';
                }
            }

            if ($isHost) {
                $defaultSeparator = '.';
            } else {
                $defaultSeparator = '/';
            }

            if (('' != $nextSeparator) && ($nextSeparator !== $defaultSeparator)) {
                $bla = preg_quote($nextSeparator, '#');
            } else {
                $bla = '';
            }

            $paramRegexp = sprintf('[^%s%s]+', preg_quote($defaultSeparator, '#'), $bla);

            if (('' !== $nextSeparator && !preg_match('#^\{\w+\}#', $followingPattern)) || '' === $followingPattern) {
                $paramRegexp .= '+';
            }
        }

        $tokens[] = array(
            'variable',
            $isSeparator ? $prevChar : '',
            $paramRegexp,
            $paramName,
        );

        $variables[] = $paramName;
    }
}
