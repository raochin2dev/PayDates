<?php

require 'vendor/autoload.php';

use MyApp\MyPaydateCalculator;

$calc = new MyPaydateCalculator;

print_r($calc->calculateNextPaydates('WEEKLY','2014-02-10','10'));
