<?php
require('vendor/autoload.php');
use Lab1521\NeatyHTML;

//Removes onerror attribute which prevents eval to alert
$html = '<img src=x:alert(window) onerror=eval(src) alt=0>';
$neaty = new NeatyHTML($html);
//Outputs <img src="x:alert(window)" alt="0">
echo $neaty->tidyUp()->html();
