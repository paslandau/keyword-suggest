#keyword-suggest

Library to query keyword suggest systems

##Description

Coming soon...

##Requirements

- PHP >= 5.5
- Guzzle >= 5.0.3
- GuzzleRotatingProxySubscriber": "~0",

##Installation

The recommended way to install keyword-suggest is through [Composer](http://getcomposer.org/).

    curl -sS https://getcomposer.org/installer | php

Next, update your project's composer.json file to include keyword-suggest:

    {
        "repositories": [
            {
                "type": "git",
                "url": "https://github.com/paslandau/keyword-suggest.git"
            }
        ],
        "require": {
             "paslandau/keyword-suggest": "~0"
        }
    }

After installing, you need to require Composer's autoloader:
```php

require 'vendor/autoload.php';
```