<?php

/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace Tangible\ScssPhp\Ast\Sass\Expression;

use Tangible\ScssPhp\Ast\Sass\Expression;
use Tangible\ScssPhp\SourceSpan\FileSpan;
use Tangible\ScssPhp\Visitor\ExpressionVisitor;

/**
 * A null literal.
 *
 * @internal
 */
final class NullExpression implements Expression
{
    private readonly FileSpan $span;

    public function __construct(FileSpan $span)
    {
        $this->span = $span;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function accept(ExpressionVisitor $visitor)
    {
        return $visitor->visitNullExpression($this);
    }

    public function __toString(): string
    {
        return 'null';
    }
}
