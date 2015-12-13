# phramework/validate
> phramework's validation library [https://phramework.github.io/validate/](https://phramework.github.io/validate/)

[![Build Status](https://travis-ci.org/phramework/validate.svg?branch=master)](https://travis-ci.org/phramework/validate)

## Usage
Require package using composer

```
composer require phramework/validate
```

### Parse an integer value

```php
$validationModel = new IntegerValidator(-1, 1);

$value = $validationModel->parse('0');

var_dump($value);
```

The above example will output:

```
int(0)
```

### Parse an object for strings

```php
$validationModel = new ObjectValidator(
    [
        'name' => new StringValidator(2, 30),
        'city' => new StringValidator(2, 30)
    ],
    ['name', 'city'], //required properties
    false //no additional properties
);

$value = $validationModel->parse(
    'name' => 'Xenofon',
    'city' => 'Thessaloniki'
);

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

## Development
### Install dependencies

```bash
composer update
```

### Test and lint code

```bash
composer lint
composer test
```

## License
Copyright 2015 Xenofon Spafaridis

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

```
http://www.apache.org/licenses/LICENSE-2.0
```

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
