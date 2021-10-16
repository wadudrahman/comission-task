<?php

declare(strict_types=1);

namespace Eskimi\CommissionTask\Service;

require_once 'ApiConsumer.php';

class Transaction
{
    public $date;
    public $userId;
    public $accountType;
    public $transactionType;
    public $amount;
    public $currency;
    public $amountInEuro;
    public $conversionRate;

    const DEPOSIT_COMMISSION_PERCENTAGE = 0.03;
    const PRIVATE_COMMISSION_FREE_WITHDRAW_LIMIT = 1000;
    const PRIVATE_COMMISSION_FREE_WITHDRAW_COUNT = 3;
    const PRIVATE_WITHDRAW_COMMISSION_PERCENTAGE = 0.3;
    const BUSINESS_WITHDRAW_COMMISSION_PERCENTAGE = 0.5;

    /**
     * @param $date
     * @param $userId
     * @param $accountType
     * @param $transactionType
     * @param $amount
     * @param $currency
     */
    public function __construct($data)
    {
        $this->date = $data[0];
        $this->userId = $data[1];
        $this->accountType = $data[2];
        $this->transactionType = $data[3];
        $this->amount = $data[4];
        $this->currency = $data[5];
        if (!in_array($this->currency, ['EUR', 'eur'])) {
            $conversionResponse = ApiConsumer::convertCurrency((string)$this->currency, (float)$this->amount);
            $this->amountInEuro = $conversionResponse['amount'];
            $this->conversionRate = $conversionResponse['rate'];
        } else {
            $this->amountInEuro = $this->amount;
            $this->conversionRate = 1;
        }
    }

    public function calculateCommission($commissionApplicableAmount = null): float
    {
        if (is_null($commissionApplicableAmount)) {
            $commissionApplicableAmount = $this->amountInEuro;
            $commissionRate = self::DEPOSIT_COMMISSION_PERCENTAGE;
        } else {
            $commissionRate = (strtolower($this->accountType) === 'private') ?
                self::PRIVATE_WITHDRAW_COMMISSION_PERCENTAGE :
                self::BUSINESS_WITHDRAW_COMMISSION_PERCENTAGE;
        }

        $commissionInEuro = ($commissionApplicableAmount * $commissionRate) / 100;

        return round($commissionInEuro * $this->conversionRate, 2);
    }

}