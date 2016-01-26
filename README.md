# phramework/validate
> phramework's validation library [https://phramework.github.io/validate/](https://phramework.github.io/validate/)

[![Coverage Status](https://coveralls.io/repos/phramework/validate/badge.svg?branch=master&service=github)](https://coveralls.io/github/phramework/validate?branch=master) [![Build Status](https://travis-ci.org/phramework/validate.svg?branch=master)](https://travis-ci.org/phramework/validate)
[![Stories in Ready](https://badge.waffle.io/phramework/validate.svg?label=ready&title=Ready)](http://waffle.io/phramework/validate)

## Usage
Require package using composer

```bash
composer require phramework/validate
```

### Parse an integer value

```php
require './vendor/autoload.php';

use \Phramework\Validate\IntegerValidator;

$validationModel = new IntegerValidator(-1, 1);

$value = $validationModel->parse('0');

var_dump($value);
```

The above example will output:

```
int(0)
```

### Parse an object of strings

```php
$validationModel = new ObjectValidator(
    [
        'name' => new StringValidator(2, 30),
        'city' => new StringValidator(2, 30)
    ],
    ['name', 'city'], //required properties
    false //no additional properties allowed
);

$value = $validationModel->parse((object) [
    'name' => 'Xenofon',
    'city' => 'Thessaloniki'
]);

print_r($value);
```

The above example will output:

```
stdClass Object
(
    [name] => Xenofon
    [city] => Thessaloniki
)
```

Check [wiki](https://github.com/phramework/validate/wiki) for more examples.

## Development
### Install dependencies

```bash
composer update
```

### Test and lint code

```bash
composer test
composer lint
```
### Generate documentation

```bash
composer doc
```

## License
Copyright 2015 - 2016 Xenofon Spafaridis

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

```
http://www.apache.org/licenses/LICENSE-2.0
```

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
