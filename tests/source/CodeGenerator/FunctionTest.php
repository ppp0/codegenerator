<?php

namespace TestsCodeGenerator;

use CodeGenerator\FunctionBlock;
use PHPUnit\Framework\TestCase;

class CG_FunctionTest extends TestCase {

    public function testExtractFromClosure() {
        $closure = function ($a, $b) {
            return $a * $b;
        };
        $function = new FunctionBlock($closure);
        eval('$multiply = ' . $function->dump() . ';');
        /** @var $multiply \Closure */
        $this->assertSame(12, $multiply(3, 4));
    }

    public function testSetCodeString() {
        $function = new FunctionBlock('return true;');
        eval('$true = ' . $function->dump() . ';');
        /** @var $true \Closure */
        $this->assertTrue($true());
    }
}
