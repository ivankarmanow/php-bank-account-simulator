<?php

namespace accounts;

use exceptions\NotEnoughMoney;

class BankAccount
{
    private float $balance;
    public function __construct() {
        $this->balance = 0;
    }

    public function in(float $amount): void
    {
        $this->balance += $amount;
    }

    public function out(float $amount): void
    {
        if ($amount > $this->balance) {
            throw new NotEnoughMoney();
        } else {
            $this->balance -= $amount;
        }
    }

    public function amount(): float
    {
        return $this->balance;
    }
}