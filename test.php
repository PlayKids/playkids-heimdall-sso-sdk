<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require "vendor/autoload.php";

use Heimdall\Object\Heimdall;
use Heimdall\Service\HeimdallService;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

$heimdallService = new HeimdallService();
$heimdallService->setClient(new Heimdall());

echo $heimdallService->getClient() instanceof Heimdall;