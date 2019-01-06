<?php

namespace Bloomkit\Core\Database\PbxQl;

/**
 * Class for parsing PbxQl Queries.
 */
class Parser
{
    /**
     * @var array
     */
    private $backticks = ["'", '"', '`'];

    /**
     * @var array
     */
    private $delimiters = ["'", '"', ',', '\\', '&&', '>', '<', '|', '=', "\r\n", '!=', '>=', '<=', '<>', '^', '(', ')', "\t", "\n", '@', ' ', '+', '-', '*', '/', ';'];

    /**
     * @var int
     */
    private $maxDelimiterSize;

    /**
     * Constructor.
     *
     * @param string $query Query to parse
     */
    public function __construct($query = false)
    {
        $this->delimiters = array_flip($this->delimiters);
        $this->maxDelimiterSize = 1;

        foreach ($this->delimiters as $item) {
            $tmpLen = strlen($item);
            if ($tmpLen > $this->maxDelimiterSize) {
                $this->maxDelimiterSize = $tmpLen;
            }
        }

        if ($query) {
            $this->parse($query);
        }
    }

    /**
     * Set elements in braces back in place.
     *
     * @param array $tokens Array with exploded tokens
     *
     * @return array Array with merged tokens
     */
    private function mergeBraces($tokens)
    {
        $cnt = count($tokens);
        $i = 0;
        while ($i < $cnt) {
            if ('(' !== $tokens[$i]) {
                ++$i;
                continue;
            }
            $count = 1;

            for ($j = $i + 1; $j < $cnt; ++$j) {
                $token = $tokens[$j];
                if ('(' === $token) {
                    ++$count;
                } elseif (')' === $token) {
                    --$count;
                }

                $tokens[$i] .= $token;
                unset($tokens[$j]);

                if (0 === $count) {
                    ++$j;
                    break;
                }
            }
            $i = $j;
        }

        return array_values($tokens);
    }

    /**
     * Set elements in quotation-marks back in place.
     *
     * @param array $tokens Array with exploded tokens
     *
     * @return array Array with merged tokens
     */
    private function mergeQuotations($tokens)
    {
        $cnt = count($tokens);
        $i = 0;
        while ($i < $cnt) {
            if (!isset($tokens[$i])) {
                ++$i;
                continue;
            }
            $token = $tokens[$i];
            if (false !== array_search($token, $this->backticks, true)) {
                $tmpTokens = $tokens;
                $tmpCnt = count($tmpTokens);
                $j = $i + 1;
                while ($j < $tmpCnt) {
                    if (!isset($tmpTokens[$j])) {
                        ++$j;
                        continue;
                    }
                    $tmpToken = $tmpTokens[$j];
                    $tmpTokens[$i] .= $tmpToken;
                    unset($tmpTokens[$j]);

                    if ($tmpToken === $token) {
                        break;
                    }
                    ++$j;
                }
                $tokens = array_values($tmpTokens);
            }
            ++$i;
        }

        return array_values($tokens);
    }

    /**
     * Parse a PbxQl query.
     *
     * @param string $query Query to parse
     *
     * @return array Expression tree
     */
    public function parse($query)
    {
        $splitted = $this->tokenize($query);
        if (!empty($splitted)) {
            return $this->parseExpressions($splitted);
        }
    }

    /**
     * Parse a tokenized PbxQl expression.
     *
     * @param array $tokens Tokens to parse
     *
     * @return array Expression tree
     */
    private function parseExpressions($tokens)
    {
        $expr = array();
        $isColRefSet = false;

        foreach ($tokens as $token) {
            $trimmedToken = trim($token);
            if ('' === $trimmedToken) {
                continue;
            }

            $upperCaseToken = strtoupper($trimmedToken);
            $tokenType = false;
            $processed = false;

            if ('(' !== $upperCaseToken[0] && ')' !== substr($upperCaseToken, -1)) {
                switch ($upperCaseToken) {
                    case 'AND':
                    case 'OR':
                    case 'LIKE':
                    case 'ISNULL':
                    case 'ISNOTNULL':
                    case '=':
                    case '!=':
                    case '>=':
                    case '>':
                    case '<=':
                    case '<':
                        $processed = false;
                        $tokenType = 'operator';
                        break;

                    default:
                        if (true == $isColRefSet) {
                            $tokenType = 'value';
                            $isColRefSet = false;
                        } else {
                            $tokenType = 'colRef';
                            $isColRefSet = true;
                        }
                        $processed = false;
                }
            }

            if (!$tokenType) {
                if ('(' == $upperCaseToken[0]) {
                    $local_expr = substr($trimmedToken, 1, -1);
                } else {
                    $local_expr = $trimmedToken;
                }
                $tmp = $this->tokenize($local_expr);
                $processed = $this->parseExpressions($tmp);
                $tokenType = 'expression';
            }

            $expr[] = array(
                'exprType' => $tokenType,
                'expression' => $token,
                'subExpr' => $processed,
            );
        }

        return $expr;
    }

    /**
     * Split a query into parts.
     *
     * @param string $query Query to split
     *
     * @return array Array with query parts
     */
    public function tokenize($query)
    {
        if (!is_string($query)) {
            throw new \InvalidArgumentException('query param is not a string');
        }

        $tokens = [];
        $token = '';
        $actPos = 0;
        $found = false;
        $len = strlen($query);

        // Explode query by delimiters
        while ($actPos < $len) {
            for ($i = $this->maxDelimiterSize; $i > 0; --$i) {
                $substr = substr($query, $actPos, $i);
                if (isset($this->delimiters[$substr])) {
                    if ('' !== $token) {
                        $tokens[] = $token;
                        $token = '';
                    }

                    $tokens[] = $substr;
                    $actPos = $actPos + $i;
                    continue 2;
                }
            }

            $token .= $query[$actPos];
            ++$actPos;
        }

        if ('' !== $token) {
            $tokens[] = $token;
        }

        $tokens = $this->mergeQuotations($tokens);
        $tokens = $this->mergeBraces($tokens);

        return $tokens;
    }
}
