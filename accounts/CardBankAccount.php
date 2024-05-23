<?php

namespace accounts;

class CardBankAccount extends BankAccount
{
    public function out(float $amount): void
    {
        parent::out($amount * 1.01);
        $coms = $amount * 0.01;
        echo "Комиссия составила $coms рублей" . PHP_EOL;
    }
}