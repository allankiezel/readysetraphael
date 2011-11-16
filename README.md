<p><b>SAMPLE:</b></p>

<p> &lt;?php</p>

<p>require("class_SVGToRaphael.php");</p>

<p>$xml = file_get_contents("map.svg");<br />
$string = <<<XML<br />
{$xml}<br />
XML;</p>

<p>$svg = new SVGToRaphael( $string );</p>

<p>$svg->generateJs();</p>
 
<p>?&gt;</p>