<?php

namespace JLexPHP;

class Token
{
    use \PHPDataGen\DataClassTrait;
    private const FIELDS = ['type' => 'Type', 'value' => 'Value', 'line' => 'Line', 'col' => 'Col'];
    private $type = null;
    private $value = '';
    private $line = 0;
    private $col = 0;
    public function __construct(array $init = [])
    {
        foreach ($init as $field => $value) {
            $this->{$field} = $this->{'validate' . self::FIELDS[$field]}($value);
        }
    }
    public function &getType()
    {
        return $this->type;
    }
    protected function validateType($value)
    {
        return $value;
    }
    public function setType($value)
    {
        $oldValue = $this->type;
        $this->type = $this->validateType($value);
        return $oldValue;
    }
    public function &getValue() : string
    {
        return $this->value;
    }
    protected function validateValue($value) : string
    {
        return $value;
    }
    public function setValue($value) : string
    {
        $oldValue = $this->value;
        $this->value = $this->validateValue($value);
        return $oldValue;
    }
    public function &getLine() : int
    {
        return $this->line;
    }
    protected function validateLine($value) : int
    {
        return $value;
    }
    public function setLine($value) : int
    {
        $oldValue = $this->line;
        $this->line = $this->validateLine($value);
        return $oldValue;
    }
    public function &getCol() : int
    {
        return $this->col;
    }
    protected function validateCol($value) : int
    {
        return $value;
    }
    public function setCol($value) : int
    {
        $oldValue = $this->col;
        $this->col = $this->validateCol($value);
        return $oldValue;
    }
}
