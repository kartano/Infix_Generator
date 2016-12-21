<?php
/**
 * Infix to postfix expression convertor.
 *
 * @author          Simon Mitchell <simon.mitchell@evgo.com>
 * @version         1.0.0               2016-12-20 14:39:13 SM:  Prototype
 * @version         1.1.0               2016-12-21 17:29:51 SM:  Fixed problem with unbounded loops.
 */

$txtExpr="17.3*(1+2)-(173.23+5*(23+11.2312))+26";
$txtOriginal=$txtExpr;
$lngCounter=0;

// SM:  Operator precedence:  Lowest to highest.
$arrOperatorPrecedence=array('-','+','*','/');

$arrOutputStack=array();
$arrOperatorStack=array();

$txtExpr=preg_replace('/\s/','',$txtExpr);
while (true)
{
    if ($txtExpr=='')
        break;    
    elseif (@preg_match('/^(\d*\.\d+)/',$txtExpr,$arrMatches))
    {
        echo "<p>Float: {$arrMatches[0]}";
        $txtExpr=substr($txtExpr,strlen($arrMatches[0])-strlen($txtExpr));
        $arrOutputStack[]=$arrMatches[0];
        if (strlen($txtExpr)==strlen($arrMatches[0]))
            break;        
    }    
    elseif (@preg_match('/^[0-9]+/',$txtExpr,$arrMatches))
    {
        echo "<p>Integer: {$arrMatches[0]}";
        $txtExpr=substr($txtExpr,strlen($arrMatches[0])-strlen($txtExpr));
        $arrOutputStack[]=$arrMatches[0];
        if (strlen($txtExpr)==strlen($arrMatches[0]))
            break;        
    }
    elseif (@preg_match('/^[+-\/*]/',$txtExpr,$arrMatches))
    {
        echo "<p>Operator: {$arrMatches[0]}";
        $txtExpr=substr($txtExpr,strlen($arrMatches[0])-strlen($txtExpr));
        if (count($arrOperatorStack==0))
            $arrOperatorStack[]=$arrMatches[0];
        else
        {
            $txtTopOp=$arrOperatorStack[count($arrOperatorStack)-1];
            // If the current op has a HIGHER priority that top op, push the current op onto the operator stack.
            $txtCurrentOpPrecedence=array_search($arrMatches[0],$arrOperatorPrecedence);
            $txtTopOpPrecedence=array_search($txtTopOp,$arrOperatorPrecedence);
            if ($txtCurrentOpPrecedence > $txtTopOpPrecedence)
                $arrOperatorStack[]=$arrMatches[0];
            else
            {
                while (true)
                {
                    if (count($arrOperatorStack)==0)
                        break;
                    else
                        $arrOutputStack[]=array_pop($arrOperatorStack);
                }
                $arrOperatorStack[]=$arrMatches[0];
            }
        }
        if (strlen($txtExpr)==strlen($arrMatches[0]))
            break;
    }
    elseif (@preg_match('/^(\(){1}/',$txtExpr,$arrMatches))
    {
        echo "<p>Opening Bracket: {$arrMatches[0]}";
        $txtExpr=substr($txtExpr,1,strlen($txtExpr)-1);
        $arrOperatorStack[]=$arrMatches[0];
        if (strlen($txtExpr)==strlen($arrMatches[0]))
            break;        
    }
    elseif (@preg_match('/^(\)){1}/',$txtExpr,$arrMatches))
    {
        echo "<p>Closing Bracket: {$arrMatches[0]}";
        $txtExpr=substr($txtExpr,1,strlen($txtExpr)-1);
        $blnFound=false;
        while (!$blnFound)
        {
            $txtOp=array_pop($arrOperatorStack);
            if ($txtOp=='(')
                $blnFound=true;
            else
                $arrOutputStack[]=$txtOp;
        }
        if (strlen($txtExpr)==strlen($arrMatches[0]))
            break;        
    }
    else
        break;
}
while (true)
{
    if (count($arrOperatorStack)==0)
        break;
    else
        $arrOutputStack[]=array_pop($arrOperatorStack);
}
echo "<pre>";
print_r($arrOutputStack);
echo "</pre>";
echo "<p>The postfix (or 'Polish') notation for {$txtOriginal} is: <b><code>".implode(' ',$arrOutputStack)."</code></b>";
?>
