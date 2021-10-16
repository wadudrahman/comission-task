<?php
require_once 'Main.php';

use Eskimi\CommissionTask\Main;

$mainClass = new Main();
$result = $mainClass->run($argv);

foreach ($result as $r) {
    $mainClass->printOutput($r);
}