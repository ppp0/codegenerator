<?php

namespace CodeGenerator;

class FunctionDocBlock extends DocBlock {

    /** @var array */
    private $_parameters;

    /** @var string|null */
    private $_returnType;

    /** @var boolean|null */
    private $_isNullableReturnType;

    public function __construct() {
        $this->_parameters = [];
        parent::__construct();
    }

    /**
     * @param string               $name
     * @param string|string[]|null $types
     * @param string|null          $description
     */
    public function addParameter(string $name, $types = null, $description = null): void {
        $this->_parameters[] = ['name' => $name, 'type' => (array) $types, 'description' => $description];
    }

    /**
     * @param string|null $type
     */
    public function setReturnType(string $type = null): void {
        $this->_returnType = $type;
    }

    /**
     * @return string|null
     */
    protected function _getReturnType(): ?string {
        return $this->_returnType;
    }

    /**
     * @return bool|null
     */
    public function _getIsNullableReturnType(): ?bool {
        return $this->_isNullableReturnType;
    }

    /**
     * @return array
     */
    protected function _getParameters(): array {
        return $this->_parameters;
    }

    protected function _getEntries() {
        $entries = parent::_getEntries();
        foreach ($this->_getParameters() as $parameter) {
            $typesString = join('|', $parameter['type']);
            $entries[] = "@param {$typesString} {$parameter['name']} {$parameter['description']}";
        }

        if (null !== $this->_getReturnType()) {
            $entries[] = "@return {$this->_getReturnType()}" . ((true === $this->_getIsNullableReturnType()) ? '|null' : '');
        }
        return $entries;
    }
}
