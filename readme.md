#KeywordSuggest

Coming soon...

##Description

##Requirements

- PHP >= 5.5
- Guzzle >= 5.0.3
- GuzzleRotatingProxySubscriber": "~0",

##Installation

The recommended way to install KeywordSuggest is through [Composer](http://getcomposer.org/).

    curl -sS https://getcomposer.org/installer | php

Next, update your project's composer.json file to include IOUtility:

    {
        "repositories": [
            {
                "type": "git",
                "url": "https://github.com/paslandau/KeywordSuggest.git"
            }
        ],
        "require": {
             "paslandau/KeywordSuggest": "~0"
        }
    }

After installing, you need to require Composer's autoloader:
```php

require 'vendor/autoload.php';
```