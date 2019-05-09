# phramework/validate
> phramework's validation library [https://phramework.github.io/validate/](https://phramework.github.io/validate/)

[![Coverage Status](https://coveralls.io/repos/phramework/validate/badge.svg?branch=master&service=github)](https://coveralls.io/github/phramework/validate?branch=master) [![Build Status](https://travis-ci.org/phramework/validate.svg?branch=master)](https://travis-ci.org/phramework/validate)
[![StyleCI](https://styleci.io/repos/46938331/shield)](https://styleci.io/repos/46938331)
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

$validator = new IntegerValidator(-1, 1);

$value = $validator->parse('0');

var_dump($value);
```

The above example will output:

```
int(0)
```

### Parse an object of strings

```php
$personalInformationValidator = new ObjectValidator(
    (object) [
        'name' => new StringValidator(2, 30),
        'city' => new StringValidator(2, 30),
        'age' => new IntegerValidator(1, 200),
    ],
    ['name', 'city', 'age'], //required properties
    false //no additional properties allowed
);

$personalInformation = $validationModel->parse((object) [
    'name' => 'Jane Doe',
    'city' => 'Athens',
    'age' => 28
]);

print_r($personalInformation);
```

The above example will output:

```
stdClass Object
(
    [name] => Jane Doe
    [city] => Athens,
    [age] => 28
)
```

### Validating an array of enum strings

```php
    /*
     * A validator that allows you to pick one or two colors between blue, green and red
     */
    $colorsValidator = new ArrayValidator(
        1, //minItems
        2, //maxItems
        (new StringValidator()) //items
            ->setEnum([
                'blue',
                'green',
                'red',
            ]),
        true //unique items
    );

    /*
     * $parsedOneItem will be validated successfully
     */
    $parsedOneItem = $colorsValidator->parse(['blue']); //will be [blue]


    /*
     * $parsedTwoItems will be validated successfully
     */
    $parsedTwoItems = $colorsValidator->parse(['blue', 'red']); //will be [blue, red]

    /*
     * $resultOfZeroItemsStatus cannot be validated true the validator requires minItems of 1
     */
    $resultOfZeroItemsStatus = $colorsValidator->validate([]);
    $resultOfZeroItemsStatus->getStatus(); // will be false because validation failed
    /** @var \Phramework\Exceptions\IncorrectParameterException $exception in this case */
    $exception = $resultOfZeroItemsStatus->getException();
    $exception->getFailure(); // will be minItems

    /*
     * $resultOfIncorrectItemsStatus cannot be validated true because "yellow" is not an allowed item
     */
    $resultOfIncorrectItemsStatus = $colorsValidator->validate(['yellow']);
    $resultOfIncorrectItemsStatus->getStatus(); // will be false because validation failed
    /** @var \Phramework\Exceptions\IncorrectParameterException $exception in this case */
    $exception = $resultOfIncorrectItemsStatus->getException();
    $exception->getFailure(); // will be items

    /*
     * Following will throw \Phramework\Exceptions\IncorrectParameterException
     * with failure maxItems because validator requires maxItems 2
     */
    $colorsValidator
        ->parse([
            'blue',
            'green',
            'red'
        ]);
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
Copyright 2015-2019 Xenofon Spafaridis

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the License at

```
http://www.apache.org/licenses/LICENSE-2.0
```

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
