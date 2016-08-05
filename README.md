# NeatyHTML

[![Build Status](https://travis-ci.org/lab1521/NeatyHTML.svg?branch=master)](https://travis-ci.org/lab1521/NeatyHTML)
[![Latest Stable Version](https://poser.pugx.org/lab1521/neaty-html/v/stable)](https://packagist.org/packages/lab1521/neaty-html)
[![License](https://poser.pugx.org/lab1521/neaty-html/license)](https://packagist.org/packages/lab1521/neaty-html)

[![SensioLabsInsight](https://insight.sensiolabs.com/projects/ed413d25-656d-48df-a607-58bf0f513049/big.png)](https://insight.sensiolabs.com/projects/ed413d25-656d-48df-a607-58bf0f513049)

Cleans up your HTML. Useful for writing content or blogs that accepts HTML markup. Requires the PHP tidy class. Please install this extension first.

## How to use

```php
<?php
require('vendor/autoload.php');
use Lab1521\NeatyHTML;

//Goal: Remove onerror attribute which prevents eval to alert
$badImage = '<img src=x:alert(window) onerror=eval(src) alt="bad image">';
$goodImage = '<img src="images/good.gif" alt="good image">';

$neaty = NeatyHTML::loadHtml($badImage . $goodImage);

//Outputs <img src="x:alert(window)" alt="bad image"><img src="images/good.gif" alt="good image">
echo $neaty->tidyUp();

//Further restrictions with source images
$neaty->blockedTags(['img']);
$neaty->tagOverrides([
    'img' => [
        [
            'attribute' => 'src',
            'values' => ['images/'] //restricts to local folder
        ],
    ]
]);

//Outputs $goodImage only
echo $neaty->tidyUp();
```

## How to use
