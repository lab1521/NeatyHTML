<?php
use PHPUnit\Framework\TestCase;
use Lab1521\NeatyHTML;

class NeatyHTMLTest extends TestCase
{
    public function testMultipeImages()
    {
        //Goal: Remove onerror attribute which prevents eval to alert
        $badImage = '<img src=x:alert(window) onerror=eval(src) alt="bad image">';
        $goodImage = '<img src="images/good.gif" alt="good image">';

        $neaty = new NeatyHTML($badImage . $goodImage);

        //Outputs <img src="x:alert(window)" alt="bad image"><img src="images/good.gif" alt="good image">
        $this->assertEquals(
            '<img src="x:alert(window)" alt="bad image"><img src="images/good.gif" alt="good image">',
            trim($neaty->tidyUp()->html())
        );

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
        $this->assertEquals($goodImage, trim($neaty->tidyUp()->html()));
    }

    public function testImageWhitelisting()
    {
        $transparentImage = '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';

        $neaty = new NeatyHTML($transparentImage);
        $neaty->blockedTags(['img']);

        //This will be blocked
        $this->assertEquals('', trim($neaty->tidyUp()->html()));

        //This will allow tag overrides
        $neaty->tagOverrides([
            'img' => [
                [
                    'attribute' => 'src',
                    'values' => [
                        'data:image/gif',    //Inline data images
                        'images/',           //Relative local images
                        '//lorempixel.com/', //Absolute location
                    ]
                ],
            ]
        ]);

        $this->assertEquals($transparentImage, trim($neaty->tidyUp()->html()));

        $remoteImage = '<img src="http://lorempixel.com/100/100/">';
        $neaty->loadHtml($remoteImage);
        $this->assertEquals($remoteImage, trim($neaty->tidyUp()->html()));

        $localImage = '<img src="images/logo.gif">';
        $neaty->loadHtml($localImage);
        $this->assertEquals($localImage, trim($neaty->tidyUp()->html()));
    }

    public function testMalformedHTML()
    {
        $malform = '"><img src="x:x" onerror="alert(document.cookie)">';

        $neaty = new NeatyHTML($malform);
        $this->assertEquals('"&gt;<img src="x:x">', trim($neaty->tidyUp()->html()));
    }

    public function testEventAttributes()
    {
        $linkTag = '<a onmouseover=alert(document.cookie)>COOKIE</a>';
        $neaty = new NeatyHTML($linkTag);
        $this->assertEquals('<a>COOKIE</a>', trim($neaty->tidyUp()->html()));

        $imageTag = '<img src=x:alert(window) onerror=eval(src) alt=0>';
        $neaty = new NeatyHTML($imageTag);
        $this->assertEquals('<img src="x:alert(window)" alt="0">', trim($neaty->tidyUp()->html()));
    }

    public function testHeadChildTags()
    {
        $styleTag = '<style>@import("http://127.0.0.1/xss.css");</style>';
        $scriptTag = '<script type="text/javascript">alert(document.cookie);</script>';
        $acceptedTag = '<p>This is neat!</p>';

        $neaty = new NeatyHTML($styleTag);
        $this->assertEquals('', $neaty->html());

        $neaty = new NeatyHTML($scriptTag . $acceptedTag);
        $this->assertEquals($acceptedTag, $neaty->html());
    }

    public function testIFrameTag()
    {
        $iframeTag = '<iframe src="javascript:alert(document.cookie);" frameborder="0"></iframe>';
        $paragraph = '<p> <a href="https://github.com/marczhermo/NeatyHTML">NeatyHTML Demo</a> </p>';

        $neaty = new NeatyHTML($iframeTag . $paragraph);
        $cleanHTML = preg_replace('/\n/', '', trim($neaty->tidyUp()->html()));
        $cleanHTML = trim(preg_replace('/\s\s+/', ' ', $cleanHTML));
        $this->assertEquals($paragraph, $cleanHTML);
    }
}
