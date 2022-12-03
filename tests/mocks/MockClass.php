<?php

namespace CodeGeneratorMocks;

class MockClass extends \CodeGeneratorMocks\MockAbstractClass {

    const FOO = 1;

    /** @var array */
    public $foo = array(1, 2);

    /** @var array  */
    public static $bar = array(1, 2);

    /** @var int */
    protected $_bar = 1;

    private $_foo;

    /**
     * @return int
     */
    public function count(): int {
        return count($this->foo);
    }

    public function withTypeHinting(\Countable $countable, array $array, callable $callable) {
    }

    public function defaultValues($defaultValue = null, $defaultArray = array()) {
    }

    public function defaultValueMandatoryArgument($mandatoryArgument, $defaultValue = true) {
    }

    public function nullableMandatoryArgument($mandatoryArgument, $nullableValue = null) {
    }

    public function withReferenceParam(&$param) {
    }

    public function withReturnType(): int {
    }

    public function withNullableReturnType(): ?array {
    }

    public function withReturnTypeVoid(): void {
    }

    protected function abstractMethod() {
    }

    private function _foo() {
        // comment
        // indentation
        // back
    }

    public static function staticMethod() {
    }
}
