# NeatyHTML

[![Build Status](https://travis-ci.org/lab1521/NeatyHTML.svg?branch=master)](https://travis-ci.org/lab1521/NeatyHTML)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/ed413d25-656d-48df-a607-58bf0f513049/mini.png)](https://insight.sensiolabs.com/projects/ed413d25-656d-48df-a607-58bf0f513049)

Cleans up your HTML. Useful for writing content or blogs that accepts HTML markup. Requires the PHP tidy class. Please install this extension first.

Accepting HTML contents (with markups/tags included etc.) on your website is somewhat a concern regarding the security of your website and its users. The dreaded XSS or Cross Site Scripting is always a threat. However with the power of open source we can keep up with common threats and mitigate the issues for safekeeping our websites or blogs.


## Installation
```
composer require lab1521/neaty-html
```

## How to use

```php
<?php
require('../vendor/autoload.php');
use Lab1521\NeatyHTML\NeatyHTML;

//Goal: Remove onerror attribute which prevents eval to alert
$badImage = '<img src=x:alert(window) onerror=eval(src) alt="bad image">';
$goodImage = '<img src="images/good.gif" alt="good image">';

$neaty = new NeatyHTML($badImage . $goodImage);

//Outputs <img src="x:alert(window)" alt="bad image"><img src="images/good.gif" alt="good image">
echo $neaty->tidyUp();

//Goal: Remove unrecognized images and keep local sources only
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
echo $neaty->tidyUp();
```

### Limitations

Current PHP's DomDocument class does not support HTML5 tags/attributes.
For this reason a NeatyDOMException exception is thrown.

## Laravel Specific Usage
### Service provider
For your Laravel app, open config/app.php and, within the providers array, append:

```php
/*
 * Package Service Providers...
 */

Lab1521\NeatyHTML\NeatyHTMLServiceProvider::class,
```

### Using Facades
Add a new item in the 'aliases' array on the same file, config/app.php
```php
'NeatyHTML' => Lab1521\NeatyHTML\Facades\NeatyHTML::class,
```

### Example Usage
```php
<?php
Route::get('/', function () {
    //Goal: Remove onerror attribute which prevents eval to alert
    $badImage = '<img src=x:alert(window) onerror=eval(src) alt="bad image">';
    $goodImage = '<img src="images/good.gif" alt="good image">';

    $neaty = NeatyHTML::loadHtml($badImage . $goodImage);

    //Goal: Remove unrecognized images and keep local sources only
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
    return $neaty->tidyUp();
    // return view('welcome');
});
```

### Custom Validation Rule
NeatyHTML provides a custom validation rule called 'html' which will check your POST input
containing markups and passes the error message when validation fails.
Please note that NeatyHTMLServiceProvider is deferred by default so we need to inject this class
in the controller method in order to use the custom validation feature.
In this way we only use NeatyHTML when we wanted which is nice to have.
```php
<?php
use Lab1521\NeatyHTML\NeatyHTML;

class PostController extends Controller
{
    public function store(Request $request, NeatyHTML $neaty)
    {
        $this->validate($request, [
            'title'    => 'required',
            'body'     => 'required|html' // <-- custom rule
        ]);

        // Your awesome code here ...
        $body = $neaty->tidyUp($request['body']);
    }
}
```
