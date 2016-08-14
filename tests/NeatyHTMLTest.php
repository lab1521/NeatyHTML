<?php

namespace Lab1521\NeatyHTML;

use PHPUnit\Framework\TestCase;

class NeatyHTMLTest extends TestCase
{
    public function testUnsupportedHTML5()
    {
        $goodImage = '<img src="images/good.gif" alt="good image">';
        $html5 = '<figure>'.$goodImage.'</figure>';

        try {
            $neaty = new NeatyHTML($html5);
        } catch (NeatyXMLError $error) {
            $this->assertContains('Tag figure invalid', $error->getMessage());
        }

        $this->expectException(NeatyXMLError::class);
        $neaty = new NeatyHTML($html5);
    }

    public function testBadXSSMarkup()
    {
        $goodImage = '<img src="images/good.gif" alt="good image">';
        $badXSS = <<<'HTML'
<Img src = x onerror = "javascript: window.onerror = alert; throw XSS">
<applet code="javascript:confirm(document.cookie);"></applet>
<isindex x="javascript:" onmouseover="alert(XSS)">
<SCRIPT>alert(String.fromCharCode(88,83,83))</SCRIPT>
<img src="x:x" onerror="alert(XSS)">
<iframe src="javascript:alert(XSS)"></iframe>
<object data="javascript:alert(XSS)"></object>
<isindex type=image src=1 onerror=alert(XSS)>
<img src=x:alert(alt) onerror=eval(src) alt=0>
<img  src="x:gif" onerror="window['al\u0065rt'](0)">
HTML;

        $neaty = new NeatyHTML($badXSS.$goodImage);

        //Further restrictions with source images
        $neaty->blockedTags(['img']);
        //Adds exceptions for img tag previously blocked
        $neaty->tagOverrides([
            'img' => [
                [
                    'attribute' => 'src',
                    'values' => ['images/'], //restricts to local folder
                ],
            ],
        ]);

        //Outputs $goodImage only
        $this->assertEquals($goodImage, trim($neaty->tidyUp()));
    }

    public function testMultipeImages()
    {
        //Goal: Remove onerror attribute which prevents eval to alert
        $badImage = '<img src=x:alert(window) onerror=eval(src) alt="bad image">';
        $goodImage = '<img src="images/good.gif" alt="good image">';

        $neaty = new NeatyHTML();

        //Outputs <img src="x:alert(window)" alt="bad image"><img src="images/good.gif" alt="good image">
        $this->assertEquals(
            '<img src="x:alert(window)" alt="bad image"><img src="images/good.gif" alt="good image">',
            trim($neaty->tidyUp($badImage.$goodImage))
        );

        //Further restrictions with source images
        $neaty->blockedTags(['img']);

        //All images are now blocked
        $this->assertEquals('', trim($neaty->tidyUp()));

        //Adds exceptions for img tag previously blocked
        $neaty->tagOverrides([
            'img' => [
                [
                    'attribute' => 'src',
                    'values' => ['images/'], //restricts to local folder
                ],
            ],
        ]);

        //Goal: Remove $badImage
        $neaty->loadHtml($badImage.$goodImage);

        //Outputs $goodImage only
        $this->assertEquals($goodImage, trim($neaty->tidyUp()));
    }

    public function testImageWhitelisting()
    {
        $transparentImage = '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7">';

        $neaty = new NeatyHTML($transparentImage);
        $neaty->blockedTags(['img']);

        //This will be blocked
        $this->assertEquals('', trim($neaty->tidyUp()));

        //This will allow tag overrides
        $neaty->tagOverrides([
            'img' => [
                [
                    'attribute' => 'src',
                    'values' => [
                        'data:image/gif',    //Inline data images
                        'images/',           //Relative local images
                        '//lorempixel.com/', //Absolute location
                    ],
                ],
            ],
        ]);

        $this->assertEquals($transparentImage, trim($neaty->tidyUp()));

        $remoteImage = '<img src="http://lorempixel.com/100/100/">';
        $neaty->loadHtml($remoteImage);
        $this->assertEquals($remoteImage, trim($neaty->tidyUp()));

        $localImage = '<img src="images/logo.gif">';
        $neaty->loadHtml($localImage);
        $this->assertEquals($localImage, trim($neaty->tidyUp()));
    }

    public function testMalformedHTML()
    {
        $malform = '"><img src="x:x" onerror="alert(document.cookie)">';

        $neaty = new NeatyHTML($malform);
        $this->assertEquals('"&gt;<img src="x:x">', trim($neaty->tidyUp()));
    }

    public function testEventAttributes()
    {
        $linkTag = '<a onmouseover=alert(document.cookie)>COOKIE</a>';
        $neaty = new NeatyHTML($linkTag);
        $this->assertEquals('<a>COOKIE</a>', trim($neaty->tidyUp()));

        $imageTag = '<img src=x:alert(window) onerror=eval(src) alt=0>';
        $neaty = new NeatyHTML($imageTag);
        $this->assertEquals('<img src="x:alert(window)" alt="0">', trim($neaty->tidyUp()));
    }

    public function testHeadChildTags()
    {
        $styleTag = '<style>@import("http://127.0.0.1/xss.css");</style>';
        $scriptTag = '<script type="text/javascript">alert(document.cookie);</script>';
        $acceptedTag = '<p>This is neat!</p>';

        $neaty = new NeatyHTML($styleTag);
        $this->assertEquals('', $neaty->html());
        $this->assertEquals('', trim($neaty->tidyUp()));

        $neaty = new NeatyHTML($scriptTag.$acceptedTag);
        $this->assertEquals($acceptedTag, $neaty->html());
        $this->assertEquals($acceptedTag, preg_replace('/\s\s+/', '', trim($neaty->tidyUp())));
    }

    public function testIFrameTag()
    {
        $iframeTag = '<iframe src="javascript:alert(document.cookie);" frameborder="0"></iframe>';
        $paragraph = '<p> <a href="https://github.com/marczhermo/NeatyHTML">NeatyHTML Demo</a> </p>';

        $neaty = new NeatyHTML();
        $cleanHTML = preg_replace('/\n/', '', trim($neaty->tidyUp($iframeTag.$paragraph)));
        $cleanHTML = preg_replace('/\s\s+/', ' ', $cleanHTML);
        $this->assertEquals($paragraph, $cleanHTML);
    }
}
