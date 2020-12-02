<?php

namespace CodeGenerator;

use CodeGenerator\Exception\Exception;

class FunctionBlock extends Block {

    /** @var string|null */
    protected $_name;

    /** @var ParameterBlock[] */
    private $_parameters = array();

    /** @var string */
    protected $_code;

    /** @var DocBlock|string|null */
    protected $_docBlock;

    /** @var string|null */
    protected $_returnType;

    /** @var boolean|null */
    protected $_isNullableReturnType;

    /**
     * @param callable|string|null $body
     */
    public function __construct($body = null) {
        $this->useDynamicDocBlock();
        if (null !== $body) {
            if ($body instanceof \Closure) {
                $this->extractFromClosure($body);
            } else {
                $this->setCode($body);
            }
        }
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void {
        $this->_name = $name;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string {
        return $this->_name;
    }

    /**
     * @param ParameterBlock $parameter
     * @throws Exception
     */
    public function addParameter(ParameterBlock $parameter): void {
        if (array_key_exists($parameter->getName(), $this->_parameters)) {
            throw new Exception('Parameter `' . $parameter->getName() . '` is already set.');
        }
        $this->_parameters[$parameter->getName()] = $parameter;
    }

    /**
     * @return ParameterBlock[]
     */
    public function getParameters(): array {
        return $this->_parameters;
    }

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): void {
        if (null !== $code) {
            $code = $this->_outdent((string) $code, true);
        }
        $this->_code = $code;
    }

    /**
     * @param DocBlock|string|null $docBlock
     */
    public function setDocBlock($docBlock): void {
        $this->_docBlock = $docBlock;
    }

    public function useDynamicDocBlock(): void {
        $this->setDocBlock(new DynamicFunctionDocBlock($this));
    }

    /**
     * @return DocBlock|null|string
     */
    public function getDocBlock() {
        return $this->_docBlock;
    }

    /**
     * @return string|null
     */
    public function getReturnType(): ?string {
        return $this->_returnType;
    }

    /**
     * @param string|null $returnType
     */
    public function setReturnType(?string $returnType): void {
        $this->_returnType = $returnType;
    }

    /**
     * @return bool|null
     */
    public function getIsNullableReturnType(): ?bool {
        return $this->_isNullableReturnType;
    }

    /**
     * @param bool|null $isNullableReturnType
     */
    public function setIsNullableReturnType(?bool $isNullableReturnType): void {
        $this->_isNullableReturnType = $isNullableReturnType;
    }

    /**
     * @param \ReflectionFunctionAbstract $reflection
     */
    public function setBodyFromReflection(\ReflectionFunctionAbstract $reflection): void {
        /** @var $reflection \ReflectionMethod */
        if (is_a($reflection, '\\ReflectionMethod') && $reflection->isAbstract()) {
            $this->_code = null;
            return;
        }
        $file = new \SplFileObject($reflection->getFileName());
        $file->seek($reflection->getStartLine() - 1);

        $code = '';
        while ($file->key() < $reflection->getEndLine()) {
            $code .= $file->current();
            $file->next();
        }

        $begin = strpos($code, 'function');
        $code = substr($code, $begin);

        $begin = strpos($code, '{');
        $end = strrpos($code, '}');
        $code = substr($code, $begin + 1, $end - $begin - 1);
        $code = preg_replace('/^\s*[\r\n]+/', '', $code);
        $code = preg_replace('/[\r\n]+\s*$/', '', $code);

        if (!trim($code)) {
            $code = null;
        }
        $this->setCode($code);
    }

    /**
     * @param \ReflectionFunctionAbstract $reflection
     */
    public function setParametersFromReflection(\ReflectionFunctionAbstract $reflection): void {
        foreach ($reflection->getParameters() as $reflectionParameter) {
            $parameter = ParameterBlock::buildFromReflection($reflectionParameter);
            $this->addParameter($parameter);
        }
    }

    /**
     * @param \ReflectionFunctionAbstract $reflection
     */
    public function setDocBlockFromReflection(\ReflectionFunctionAbstract $reflection): void {
        $docBlock = $reflection->getDocComment();
        if ($docBlock) {
            $docBlock = preg_replace('/([\n\r])(' . self::$_indentation . ')+/', '$1', $docBlock);
            $this->setDocBlock($docBlock);
        } else {
            $this->setDocBlock(null);
        }
    }

    /**
     * @param \ReflectionFunctionAbstract $reflection
     */
    public function setReturnTypeFromReflection(\ReflectionFunctionAbstract $reflection): void {
        $returnType = $reflection->getReturnType();
        if (!$returnType || !is_a($returnType, \ReflectionNamedType::class)) {
            return;
        }
        $this->_returnType = $returnType->getName();
        $this->_isNullableReturnType = (boolean) $returnType->allowsNull();
    }

    public function dump(): string {
        return $this->_dumpLine(
            $this->_dumpDocBlock(),
            $this->_dumpHeader() . $this->_dumpReturnType() . $this->_dumpBody()
        );
    }

    /**
     * @return string|null
     */
    protected function _dumpDocBlock(): ?string {
        if (null === $this->_docBlock) {
            return null;
        }
        return (string) $this->_docBlock;
    }

    /**
     * @return string
     */
    protected function _dumpHeader(): string {
        $content = 'function';
        if ($this->_name) {
            $content .= ' ' . $this->_name;
        }
        $content .= '(';
        $content .= implode(', ', $this->_parameters);
        $content .= ')';
        return $content;
    }

    /**
     * @return string
     */
    protected function _dumpReturnType(): string {
        if (null === $this->_returnType) {
            return '';
        }
        return ': ' . ($this->_isNullableReturnType ? '?' : '') . $this->_returnType;
    }

    /**
     * @return string
     */
    protected function _dumpBody(): string {
        $code = $this->_code;
        if ($code) {
            $code = $this->_indent($code);
        }
        return $this->_dumpLine(' {', $code, '}');
    }

    /**
     * @param \ReflectionFunctionAbstract $reflection
     */
    public function extractFromReflection(\ReflectionFunctionAbstract $reflection): void {
        $this->setBodyFromReflection($reflection);
        $this->setParametersFromReflection($reflection);
        $this->setDocBlockFromReflection($reflection);
        $this->setReturnTypeFromReflection($reflection);
    }

    /**
     * @param \Closure $closure
     */
    public function extractFromClosure(\Closure $closure): void {
        $this->extractFromReflection(new \ReflectionFunction($closure));
    }
}
