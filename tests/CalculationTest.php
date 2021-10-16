<?php

namespace Eskimi\CommissionTask\Tests;

use Eskimi\CommissionTask\Main;
use PHPUnit\Framework\TestCase;

class CalculationTest extends TestCase
{
    protected $inputs,$output = [];

    public function setUp():void
    {
        parent::setUp();
        $this->inputs = [
            '2014-12-31,4,private,withdraw,1200.00,EUR'
        ];

        $this->output = [0.6];
    }

    public function testCommissionCalculation()
    {
        $mainClass = new Main();
        $result = $mainClass->run($this->inputs, Main::TEST_ENV);
        $this->assertEqualsCanonicalizing($this->output, $result);
    }

}