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
use Heimdall\Object\Heimdall;
use Heimdall\Service\HeimdallService;

$heimdallAccessToken = "";
$heimdallApplicationClient = getenv('HEIMDALL_APP_CLIENT');

$heimdallService = new HeimdallService($heimdallApplicationClient);
$heimdallService->setAccessToken($heimdallAccessToken);

echo print_r($heimdallService->decodeAccessToken(), true) . PHP_EOL;
```
