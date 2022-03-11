# PlayKids Heimdall SSO SDK

## Standards

PHP - [PSR-12](https://www.php-fig.org/psr/psr-12/)

Commits - [Conventional Commits](https://www.conventionalcommits.org/)

## Setup

Add to your composer.json:

**require**: _"playkids/playkids-heimdall-sso-sdk" : "^0.1.0"_

**repositories**: _[{"type":"vcs","url":"https://github.com/PlayKids/playkids-heimdall-sso-sdk"}]_
 
Now run **composer update playkids/playkids-heimdall-sso-sdk**

## Usage classes 

```php
use Heimdall\Service\HeimdallService;

$heimdallApplicationClient = getenv('HEIMDALL_APP_CLIENT');
$heimdallService = new HeimdallService($heimdallApplicationClient);

try {
    $loginResponse = $heimdallService->attemptUserLogin([
        'username' => '',
        'password' => '',
    ]);

    $heimdallService->setAccessToken($loginResponse->access_token);
} catch (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
}

echo print_r($heimdallService->decodeAccessToken(), true) . PHP_EOL;
```
