<?php

enum States {
    case SelectAccountType;
    case OnlineLoop;
}

class FiniteStateMachine implements ArrayAccess
{
    public array $state_data;
    public States | null $state;

    public function __construct()
    {
        $this->state_data = array();
        $this->state = null;
    }

    public function setData(array $data): void
    {
        $this->state_data = $data;
    }

    public function offsetExists(mixed $offset): bool
    {
        return in_array($offset, $this->state_data);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->state_data[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->state_data[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->state_data[$offset]);
    }
}