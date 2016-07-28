<?php
require('vendor/autoload.php');

$html = '<style>@import("custom.css");</style>';

$neaty = new NeatyHTML($html);
echo $neaty->html();
