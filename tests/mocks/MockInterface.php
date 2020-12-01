<?php

namespace CodeGeneratorMocks;

interface MockInterface extends \CodeGeneratorMocks\MockInterfaceTwo {

    public function withTypeHinting(\Countable $countable, array $array, callable $callable);

    public function defaultValues($defaultValue = null, $defaultArray = array());

    public function withReferenceParam(&$param);

    public function withReturnType(): string;

    public function withNullableReturnType(): ?int;
}
