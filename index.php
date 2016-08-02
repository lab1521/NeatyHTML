<?php
require('vendor/autoload.php');
use Lab1521\NeatyHTML;

//Goal: Remove onerror attribute which prevents eval to alert
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

//Outputs $goodImage only
echo $neaty->tidyUp()->html();
