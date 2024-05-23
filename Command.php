<?php

class Command
{
    public function __construct(public string $prompt, public string $command, public array $params)
    {

    }
}