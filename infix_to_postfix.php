<?php
/**
 * Infix to postfix generator and evaluator
 *
 * @author          https://www.rune-server.ee/members/funke/
 * @author          Simon Mitchell <simon.mitchell@evgo.com>
 * @see             https://www.rune-server.ee/programming/website-development/446325-php-calculator-infix-postfix-eval.html
 * @version         2.0.0               2016-12-29 16:39:18 SM:  Prototype
 */

define('ASSOC_NONE', 0);
define('ASSOC_LEFT', 1);
define('ASSOC_RIGHT', 2);

/**
 * Postfix Utilities Class
 */
final class PostfixUtils
{
    protected static $operators = array('^' => array(9, ASSOC_RIGHT, false),
					'*' => array(8, ASSOC_LEFT, false),
					'/' => array(8, ASSOC_LEFT, false),
					'%' => array(8, ASSOC_LEFT, false),
					'+' => array(5, ASSOC_LEFT, false),
					'-' => array(5, ASSOC_LEFT, false),
					'(' => array(0, ASSOC_NONE, false),
					')' => array(0, ASSOC_NONE, false));

    private static function precedence($opchar)
    {
	    return self::$operators[$opchar][0];
    }

    private static function associativity($opchar)
    {
    	return self::$operators[$opchar][1];
    }

    private static function unary($opchar)
    {
	    return self::$operators[$opchar][2];
    }

    private static function is_operator($char)
    {
	    return array_key_exists($char, self::$operators);
    }

    private static function starts_with($haystack, $needle)
    {
	    return !strncmp($haystack, $needle, strlen($needle));
    }

    private static function ends_with($haystack, $needle)
    {
	    return substr($haystack, -strlen($needle)) === $needle;
    }

    private static function array_peek(array $stack)
    {
	    return $stack[count($stack) - 1];
    }

    public static function postfix($expression)
    {
        //---------------------------------------------------------------------------------------------
        // SM:  Remove any white space from the expression.
        //---------------------------------------------------------------------------------------------
        
	    $expression = preg_replace('/\s+/', '', $expression);

        //---------------------------------------------------------------------------------------------
        // SM:  Make sure out expression is encapsulated within parenthesis.
        //---------------------------------------------------------------------------------------------
	
    	if (!self::starts_with($expression, '('))
		    $expression = '('.$expression;
	    if (!self::ends_with($expression, ')'))
		    $expression .= ')';
	
    	$stack = array();
    	$output = '';
    	$previous = true;
	
    	for ($i = 0; $i < strlen($expression); $i++)
    	{
    		$char = $expression[$i];
    		if (self::is_operator($char))
    		{
    			if ($char == '(')
    				array_push($stack, $char);
    			else if ($char == ')')
    			{
    				while (count($stack) > 0 && ($top = self::array_peek($stack)) != '(')
    				{
    					$output .= ' '.$top;
    					array_pop($stack);
    				}    				
    				array_pop($stack);
    			}
    			else
    			{
    				while (count($stack) > 0)
    				{
    					$peek = self::array_peek($stack);
    					if (self::associativity($char) == ASSOC_LEFT && self::precedence($char) <= self::precedence($peek) 
    					|| self::associativity($char) == ASSOC_RIGHT && self::precedence($char) < self::precedence($peek))
    					{
    						$output .= ' '.self::array_peek($stack);
    						array_pop($stack);
    					}
    					else
    						break;
    				}
    				array_push($stack, $char);
    			}
    			$previous = true;
    		}
    		else
    		{
    			$output .= ($previous ? ' ' : '').$char;
    			$previous = false;
    		}
    	}
	
    	while (count($stack) > 0)
    	{
    		if (self::array_peek($stack) == '(')
    			array_pop($stack);
    		else
    			$output .= ' '.array_pop($stack);
    	}	
    	return $output;
    }

    public static function postfix_eval($postfix)
    {
    	$stack = array();
    	$num = '';
    	for ($i = 0; $i < strlen($postfix); $i++)
    	{
    		$char = $postfix[$i];
    		if (self::is_operator($char))
    		{
    			$second = array_pop($stack);
    			if ($char == '^')
    				array_push($stack, pow(array_pop($stack), $second));
    			else
    				array_push($stack, eval("return ".array_pop($stack)." $char $second;"));
    		}
    		else
    		{
    			if ($char == ' ')
    			{
    				if (strlen($num) > 0)
    					array_push($stack, $num);    				
    				$num = '';
    			}
    			else
    				$num .= $char;
    		}
    	}
    	return array_pop($stack);
    }
}
$expression = '(32*3)+(4/5-(6^9/(4%8)))';
echo('Expression: '.$expression.'<br />');
$postfix=PostfixUtils::postfix($expression);
echo('Postfix: '.$postfix.'<br />');
echo('Evaluation: '.PostfixUtils::postfix_eval($postfix));
?>
