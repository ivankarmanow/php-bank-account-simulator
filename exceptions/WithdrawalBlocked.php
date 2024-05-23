<?php

namespace exceptions;

class WithdrawalBlocked extends \Exception
{
    public function __construct(public int $seconds = 0) {
        parent::__construct();
    }
}