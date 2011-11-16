<p><b>SAMPLE:</b></p>

<p> &lt;?php</p>

<p>require("class_SVGToRaphael.php");</p>

<p>$xml = file_get_contents("map.svg");<br />
$string = &lt;&lt;<XML<br />
{$xml}<br />
XML;</p>

<p>$svg = new SVGToRaphael( $string );</p>

<p>$svg->generateJs();</p>
 
<p>?&gt;</p>

<p>
<a rel="license" href="http://creativecommons.org/licenses/by/3.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by/3.0/80x15.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" href="http://purl.org/dc/dcmitype/Text" property="dct:title" rel="dct:type">Ready.Set.Raphael</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://www.thinkbi.gr" property="cc:attributionName" rel="cc:attributionURL">http://www.thinkbi.gr</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 Unported License</a>.<br />Based on a work at <a xmlns:dct="http://purl.org/dc/terms/" href="http://www.readysetraphael.com" rel="dct:source">www.readysetraphael.com</a>.
</p>