<?php

declare(strict_types=1);

namespace HyperfTest\Mock\CustomFunction;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class SubstringFunction extends FunctionNode
{
    public $stringExpression;

    public $startPosExpression;

    public $lengthExpression;

    public function getSql(SqlWalker $sqlWalker)
    {
        return 'SUBSTRING(' .
            $sqlWalker->walkArithmeticPrimary($this->stringExpression) . ', ' .
            $sqlWalker->walkArithmeticPrimary($this->startPosExpression) .
            ($this->lengthExpression !== null ? ', ' . $sqlWalker->walkArithmeticPrimary($this->lengthExpression) : '') .
            ')';
    }

    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER); // SUBSTRING
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->stringExpression = $parser->ArithmeticPrimary();
        $parser->match(Lexer::T_COMMA);
        $this->startPosExpression = $parser->ArithmeticPrimary();

        if ($parser->getLexer()->isNextToken(Lexer::T_COMMA)) {
            $parser->match(Lexer::T_COMMA);
            $this->lengthExpression = $parser->ArithmeticPrimary();
        }

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}
