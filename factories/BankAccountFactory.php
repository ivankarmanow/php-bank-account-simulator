<?php

namespace factories;
use ArrayAccess;
use Exception;
use exceptions\ResourceNotFound;

class BankAccountFactory implements ArrayAccess
{
    private array $fabric;
    private array $fabric_num;

    public function __construct()
    {
        $this->fabric = array();
        $this->fabric_num = array();
    }

    public function register(string $resource, string $class): void
    {
        $this->fabric[$resource] = $class;
        $this->fabric_num[] = $class;
    }

    public function iter(): array {
        return $this->fabric;
    }
    
    public function build(string | int $resource): object
    {
        try {
            if (is_string($resource)) {
                $class = $this->fabric[$resource];
            } else {
                $class = $this->fabric_num[$resource-1];
            }
            return new $class;
        } catch (Exception) {
            throw new ResourceNotFound();
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return in_array($offset, $this->fabric);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->fabric[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->fabric[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->fabric[$offset]);
    }
}