<?php
use PHPUnit\Framework\TestCase;
use Lab1521\NeatyHTML;

class NeatyHTMLTest extends TestCase
{

    public $suspects = [
        '<style>@import("custom.css");</style>',
        '<strong>bold <b>text</b> here</strong>',
        '<link rel="stylesheet" href="#"/>',
        '<a href="#" onclick="alert(\'testing\');" class="my-link" id="mylink">link</a>',
        '<script type="text/javascript">console.log("2");</script>',
        '<span class="my-span font10" style="font-size: 10px;">Some content goes here.</span>',
        '<img src="blank.gif" class="responsive" onload="alert(\'loaded\')" />',
        '<script type="text/javascript" />',
        '<IMG SRC=JaVaScRiPt:alert(\'XSS\')>',
        '<a onmouseover=alert(document.cookie)>xxs link</a>',
        '<IMG SRC=&#106;&#97;&#118;&#97;&#115;&#99;&#114;&#105;&#112;&#116;&#58;&#97;&#108;&#101;&#114;&#116;&#40;&#39;&#88;&#83;&#83;&#39;&#41;>',
        '<IMG SRC="jav&#x09;ascript:alert(\'XSS\');">',
        '<IMG SRC="jav&#x0A;ascript:alert(\'XSS\');">',
        '<IMG SRC="jav&#x0D;ascript:alert(\'XSS\');">',
        '<SCRIPT SRC=http://xss.rocks/xss.js?< B >',
        '<SCRIPT SRC=//xss.rocks/.j>',
        '<INPUT TYPE="IMAGE" SRC="javascript:alert(\'XSS\');">',
        '<IMG DYNSRC="javascript:alert(\'XSS\')">',
        '<IMG LOWSRC="javascript:alert(\'XSS\')">',
        '<IMG SRC=\'vbscript:msgbox("XSS")\'>',
        '<STYLE>BODY{-moz-binding:url("http://xss.rocks/xssmoz.xml#xss")}</STYLE>',
        '<STYLE>@im\port\'\ja\vasc\ript:alert("XSS")\';</STYLE>',
        '<IMG STYLE="xss:expr/*XSS*/ession(alert(\'XSS\'))">',
        '<IFRAME SRC="javascript:alert(\'XSS\');"></IFRAME>',
        '<TABLE BACKGROUND="javascript:alert(\'XSS\')">',
        '<DIV STYLE="background-image: url(javascript:alert(\'XSS\'))">',
        '<DIV STYLE="background-image:\0075\0072\006C\0028\'\006a\0061\0076\0061\0073\0063\0072\0069\0070\0074\003a\0061\006c\0065\0072\0074\0028.1027\0058.1053\0053\0027\0029\'\0029">',
        '<DIV STYLE="width: expression(alert(\'XSS\'));">',
        '<OBJECT TYPE="text/x-scriptlet" DATA="http://xss.rocks/scriptlet.html"></OBJECT>',
        '<EMBED SRC="data:image/svg+xml;base64,PHN2ZyB4bWxuczpzdmc9Imh0dH A6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcv MjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hs aW5rIiB2ZXJzaW9uPSIxLjAiIHg9IjAiIHk9IjAiIHdpZHRoPSIxOTQiIGhlaWdodD0iMjAw IiBpZD0ieHNzIj48c2NyaXB0IHR5cGU9InRleHQvZWNtYXNjcmlwdCI+YWxlcnQoIlh TUyIpOzwvc2NyaXB0Pjwvc3ZnPg==" type="image/svg+xml" AllowScriptAccess="always"></EMBED>',
        '<SCRIPT a=">" SRC="httx://xss.rocks/xss.js"></SCRIPT>',
        '<SCRIPT a=`>` SRC="httx://xss.rocks/xss.js"></SCRIPT>',
        '<SCRIPT a=">\'>" SRC="httx://xss.rocks/xss.js"></SCRIPT>',
        '<Img src = x onerror = "javascript: window.onerror = alert; throw XSS">',
        '<Input value = "XSS" type = text>',
        '<applet code="javascript:confirm(document.cookie);">',
        '<isindex x="javascript:" onmouseover="alert(XSS)">',
        '"><img src="x:x" onerror="alert(XSS)">',
        '"><iframe src="javascript:alert(XSS)">',
        '<object data="javascript:alert(XSS)">',
        '<isindex type=image src=1 onerror=alert(XSS)>',
        '<img src=x:alert(alt) onerror=eval(src) alt=0>',
    ];

    public function testIFrameTag()
    {
        $iframeTag = '<iframe src="https://player.vimeo.com/video/2941443" width="640" height="360" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
        $paragraph = '<p><a href="https://vimeo.com/2941443">HDR Time Lapse</a> from <a href="https://vimeo.com/pererik">Per Erik Sviland</a> on <a href="https://vimeo.com">Vimeo</a>.</p>';

        $neaty = new NeatyHTML($iframeTag . $paragraph);
        $this->assertEquals($paragraph, $neaty->tidyUp()->html());
    }

    public function testHeadChildTags()
    {
        $styleTag = '<style>@import("xss.css");</style>';
        $scriptTag = '<script type="text/javascript">alert("xss");</script>';
        $acceptedTag = '<p>This is neat!</p>';

        $neaty = new NeatyHTML($styleTag);
        $this->assertEquals('', $neaty->html());

        $neaty = new NeatyHTML($scriptTag . $acceptedTag);
        $this->assertEquals($acceptedTag, $neaty->html());
    }

    public function testEventAttributes()
    {
        $linkTag = '<a onmouseover=alert(document.cookie)>xxs</a>';

        $neaty = new NeatyHTML($linkTag);
        $this->assertEquals('<a>xxs</a>', $neaty->tidyUp()->html());
    }

    // public function testUnClean()
    // {
    //     foreach($this->suspects as $index => $markup) {
    //         $neaty = new NeatyHTML($markup);
    //         $this->assertEquals($markup, $neaty->html());
    //     }
    // }

    /**
     * @depends testUnClean
     */
    // public function testTwo()
    // {
    //     $this->assertEquals($this->html, $neaty->html());
    //     $this->assertTrue(true);
    // }

    // preg_replace('/<span class="it">(.*?)<\/span>/', '{it}$1{/it}', $text)
}
