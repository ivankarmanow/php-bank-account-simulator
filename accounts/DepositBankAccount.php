<?php

namespace accounts;

use exceptions\WithdrawalBlocked;

class DepositBankAccount extends BankAccount
{
    private int $lastUpdate;
    const COOLDAWN = 28 * 24 * 60 * 60;
    public function __construct() {
        $this->lastUpdate = time();
        parent::__construct();
    }

    public function in(float $amount): void {
        $this->lastUpdate = time();
        parent::in($amount);
    }

    public function out(float $amount): void {
        $delta = time() - $this->lastUpdate;
        if ($delta < self::COOLDAWN) {
            $do = self::COOLDAWN - $delta;
            throw new WithdrawalBlocked($do);
        } else {
            parent::out($amount);
        }
    }
}