<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require "vendor/autoload.php";

use Heimdall\Service\HeimdallService;

$dotenv = Dotenv\Dotenv::create(__DIR__);
$env = $dotenv->safeLoad();

$heimdallAccessToken = "";
$heimdallExampleRole = "pcp-manager";
$heimdallApplicationClient = getenv('HEIMDALL_APP_CLIENT');

$heimdallService = new HeimdallService($heimdallApplicationClient);
$heimdallService->setAccessToken($heimdallAccessToken);

try {

    echo print_r($heimdallService->decodeAccessToken(), true) . PHP_EOL;

    echo print_r($heimdallService->isValidAccessToken(), true) . PHP_EOL;

    echo print_r($heimdallService->accessTokenHasRole($heimdallExampleRole), true) . PHP_EOL;

    echo print_r($heimdallService->getRoles($heimdallService->decodeAccessToken()), true) . PHP_EOL;

} catch (Exception $exception) {

    echo $exception->getMessage() . PHP_EOL;

}