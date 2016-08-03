# NeatyHTML
Cleans up your HTML. Useful for writing content or blogs that accepts HTML markup.
Requires the PHP tidy class. Please install this extension first.
```php
<?php
require('vendor/autoload.php');
use Lab1521\NeatyHTML;

//Out of the box, removes onerror event which prevents eval to alert xss hack
$badImage = '<img src=x:alert(window) onerror=eval(src) alt="bad image">';
$goodImage = '<img src="images/good.gif" alt="good image">';

$neaty = new NeatyHTML($badImage . $goodImage);

//Outputs <img src="x:alert(window)" alt="bad image"><img src="images/good.gif" alt="good image">
echo $neaty->tidyUp()->html();

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

//Goal: Remove $badImage
$neaty->loadHtml($badImage . $goodImage);

//Outputs $goodImage only <img src="images/good.gif" alt="good image">
echo $neaty->tidyUp()->html();
```
