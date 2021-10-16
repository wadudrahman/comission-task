<?php

declare(strict_types=1);

namespace Eskimi\CommissionTask;

require_once 'Service/DataProcessor.php';
require_once 'Service/Transaction.php';

use Eskimi\CommissionTask\Service\DataProcessor;
use Eskimi\CommissionTask\Service\Transaction;
use stdClass;

class Main
{
    protected $startTime;
    protected $endTime;

    const TEST_ENV = 'test';
    const PROD_ENV = 'production';

    public $commission = [];

    /**
     * @return float
     */
    public function getStartTime(): float
    {
        return $this->startTime;
    }

    /**
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param mixed $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    public function run($argv, $env = self::PROD_ENV): array
    {
        try {
            if (self::TEST_ENV === $env) {
                $dataObject = new stdClass();
                $dataObject->data = $argv;
            } else {
                $dataObject = new DataProcessor($argv);
                $dataObject->checkArguments()->loadDataFromFile();
            }
            $transactionHistory = [];

            foreach ($dataObject->data as $data) {
                $explodedData = explode(',', $data);
                $transactionObject = new Transaction($explodedData);

                if (strtolower($transactionObject->transactionType) === 'deposit') {
                    $this->commission[] = $transactionObject->calculateCommission();
                } else {
                    if (strtolower($transactionObject->accountType) === 'business') {
                        $commissionApplicableAmount = $transactionObject->amountInEuro;
                    } else {
                        if (!array_key_exists($transactionObject->userId, $transactionHistory)) {
                            if ($transactionObject->amountInEuro > Transaction::PRIVATE_COMMISSION_FREE_WITHDRAW_LIMIT) {
                                $commissionApplicableAmount = $transactionObject->amountInEuro - Transaction::PRIVATE_COMMISSION_FREE_WITHDRAW_LIMIT;
                            } else {
                                $commissionApplicableAmount = 0;
                            }
                        } else {
                            $previousTotalTransactionAmount = 0;
                            $previousTransactionCount = 0;
                            $firstDate = strtotime($transactionObject->date);
                            foreach ($transactionHistory[$transactionObject->userId] as $historyData) {
                                $secondDate = strtotime($historyData->date);

                                if (date('oW', $firstDate) === date('oW', $secondDate)) {
                                    $previousTransactionCount++;
                                    $previousTotalTransactionAmount += $historyData->amountInEuro;
                                }

                                if ($previousTransactionCount > Transaction::PRIVATE_COMMISSION_FREE_WITHDRAW_COUNT) {
                                    $commissionApplicableAmount = $transactionObject->amountInEuro;
                                } else {
                                    if ($previousTotalTransactionAmount >= Transaction::PRIVATE_COMMISSION_FREE_WITHDRAW_LIMIT) {
                                        $commissionApplicableAmount = $transactionObject->amountInEuro;
                                    } else {
                                        $totalAmount = $previousTotalTransactionAmount + $transactionObject->amountInEuro;
                                        $remainingQuota = $totalAmount - Transaction::PRIVATE_COMMISSION_FREE_WITHDRAW_LIMIT;
                                        $commissionApplicableAmount = ($remainingQuota >= 0) ? $remainingQuota : 0;
                                    }
                                }
                            }
                        }

                        $transactionHistory[$transactionObject->userId][] = $transactionObject;
                    }
                    $this->commission[] = $transactionObject->calculateCommission($commissionApplicableAmount);
                }
            }
        } catch
        (\Exception $exception) {
            self::printOutput('message : ' . $exception->getMessage());
        }

        return $this->commission;
    }

    private function executionTime()
    {
        echo PHP_EOL . 'Total Execution Time: ' . (self::getEndTime() - self::getStartTime()) . ' Micro Seconds.';
    }

    public function printOutput($message)
    {
        echo $message . PHP_EOL;
    }
}