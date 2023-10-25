<?php

/**
 * Infix to postfix generator and evaluator
 *
 * @author          https://www.rune-server.ee/members/funke/
 * @author          Simon Mitchell <simon.mitchell@evgo.com>
 * @see             https://www.rune-server.ee/programming/website-development/446325-php-calculator-infix-postfix-eval.html
 */

/**
 * Association enumerator
 */
enum Association
{
    case NONE;
    case LEFT;
    case RIGHT;
}

/**
 * Postfix Utilities Class
 */
final class PostfixUtils
{
    /**
     * @var array[] Array of operators with attributes.
     * Each array item MUST have three attributes:
     * <code>
     *      [
     *          (integer operator precedence - bigger number, higher precedence),
     *          Association,
     *          (bool flag indicating if operator is unary)
     *      ]
     *      ...
     * </code>
     */
    private static array $operators = [
        '^' => [9, Association::RIGHT, false],
        '*' => [8, Association::LEFT, false],
        '/' => [8, Association::LEFT, false],
        '%' => [8, Association::LEFT, false],
        '+' => [5, Association::LEFT, false],
        '-' => [5, Association::LEFT, false],
        '(' => [0, Association::NONE, false],
        ')' => [0, Association::NONE, false],
    ] ;

    /**
     * Get precedence for specific operator
     * @param string $opchar Operator character
     * @return int Operator precedence
     */
    private static function precedence(string $opchar): int
    {
        return self::$operators[$opchar][0];
    }

    /**
     * Get associativity for specific operator
     * @param string $opchar Operator character
     * @return Association Operator associativity
     */
    private static function associativity(string $opchar): Association
    {
        return self::$operators[$opchar][1];
    }

    /**
     * Determine if operator is unary
     * @param string $opchar Operator character
     * @return bool Unary flag
     */
    private static function unary(string $opchar): bool
    {
        return self::$operators[$opchar][2];
    }

    /**
     * Determine if character is an operator
     * @param string $char
     * @return bool
     */
    private static function isOperator(string $char): bool
    {
        return array_key_exists($char, self::$operators);
    }

    private static function startsWith(string $haystack, string $needle): bool
    {
        return !strncmp($haystack, $needle, strlen($needle));
    }

    private static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    private static function arrayPeek(array $stack): string
    {
        return $stack[count($stack) - 1];
    }

    /**
     * Convert a given expression into Postfix
     * @param string $expression An expression to parse into postfix format
     * @return string
     */
    public static function postfix(string $expression): string
    {
        // SM:  Remove any white space from the expression.
        $expression = preg_replace('/\s+/', '', $expression);

        // SM:  Make sure out expression is encapsulated within parenthesis.
        if (!self::startsWith($expression, '(')) {
            $expression = '(' . $expression;
        }
        if (!self::endsWith($expression, ')')) {
            $expression .= ')';
        }

        $stack = [];
        $output = '';
        $previous = true;

        for ($i = 0; $i < strlen($expression); $i++) {
            $char = $expression[$i];
            if (self::isOperator($char)) {
                if ($char == '(') {
                    $stack[] = $char;
                } elseif ($char == ')') {
                    while (count($stack) > 0 && ($top = self::arrayPeek($stack)) != '(') {
                        $output .= ' ' . $top;
                        array_pop($stack);
                    }
                    array_pop($stack);
                } else {
                    while (count($stack) > 0) {
                        $peek = self::arrayPeek($stack);
                        if (
                            (
                                self::associativity($char) == Association::LEFT
                                && self::precedence($char) <= self::precedence($peek)
                            )
                            ||
                            (
                                self::associativity($char) == Association::RIGHT
                                && self::precedence($char) < self::precedence($peek)
                            )
                        ) {
                            $output .= ' ' . self::arrayPeek($stack);
                            array_pop($stack);
                        } else {
                            break;
                        }
                    }
                    $stack[] = $char;
                }
                $previous = true;
            } else {
                $output .= ($previous ? ' ' : '') . $char;
                $previous = false;
            }
        }

        while (count($stack) > 0) {
            if (self::arrayPeek($stack) == '(') {
                array_pop($stack);
            } else {
                $output .= ' ' . array_pop($stack);
            }
        }

        return $output;
    }

    /**
     * @param string $postfix Postfix expression to evaluate.
     * @return string
     */
    public static function postfixEval(string $postfix): string
    {
        $stack = [];
        $num = '';
        for ($i = 0; $i < strlen($postfix); $i++) {
            $char = $postfix[$i];
            if (self::isOperator($char)) {
                $second = array_pop($stack);
                if ($char == '^') {
                    $stack[] = pow(array_pop($stack), $second);
                } else {
                    $stack[] = eval("return " . array_pop($stack) . " $char $second;");
                }
            } else {
                if ($char == ' ') {
                    if (strlen($num) > 0) {
                        $stack[] = $num;
                    }
                    $num = '';
                } else {
                    $num .= $char;
                }
            }
        }

        return array_pop($stack);
    }
}

$expression = '(32*3)+(4/5-(6^9/(4%8)))';

echo('Expression: ' . $expression . PHP_EOL);
$postfix = PostfixUtils::postfix($expression);
echo('Postfix: ' . $postfix . PHP_EOL);
echo('Evaluation: ' . PostfixUtils::postfixEval($postfix) . PHP_EOL);
