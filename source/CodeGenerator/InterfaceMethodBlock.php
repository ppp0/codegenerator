<?php

namespace CodeGenerator;

class InterfaceMethodBlock extends FunctionBlock {

    /**
     * @param callable|null|string $name
     */
    public function __construct($name) {
        $this->setName($name);
        parent::__construct();
    }

    /**
     * @return string
     */
    protected function _dumpHeader(): string {
        return 'public ' . parent::_dumpHeader();
    }

    /**
     * @return string
     */
    protected function _dumpBody(): string {
        return ';';
    }

    /**
     * @param \ReflectionMethod $reflection
     * @return InterfaceMethodBlock
     */
    public static function buildFromReflection(\ReflectionMethod $reflection): InterfaceMethodBlock {
        $method = new self($reflection->getName());
        $method->extractFromReflection($reflection);

        return $method;
    }
}
