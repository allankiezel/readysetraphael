Ready. Set. Raphael.
=========

Raphaël JavaScript Library Converter

Ready. Set. Raphael. will convert contents from an SVG file to Raphaël JavaScript code.

Online Conversion Tool
----

Please visit [www.readysetraphael.com](http://www.readysetraphael.com/ "Ready Set Raphael") to use our automatic converter for your projects.

Usage
--------------

```
<?php

try {

    $xml =  file_get_contents([path_to_svg]);

    SVG::init($xml, 'rsr');

    $parser = new Parser(SVG::getSVG());
    $parser->init();

} catch (Exception $e) {

    echo $e->getMessage();

}

?>
```

License
----

<p>
<a rel="license" href="http://creativecommons.org/licenses/by/3.0/"><img alt="Creative Commons License" style="border-width:0" src="http://i.creativecommons.org/l/by/3.0/80x15.png" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" href="http://purl.org/dc/dcmitype/Text" property="dct:title" rel="dct:type">Ready. Set. Raphael.</span> by <a xmlns:cc="http://creativecommons.org/ns#" href="http://www.twitter.com/allankiezel" property="cc:attributionName" rel="cc:attributionURL">@allankiezel</a> is licensed under a <a rel="license" href="http://creativecommons.org/licenses/by/3.0/">Creative Commons Attribution 3.0 Unported License</a>.<br />Based on a work at <a xmlns:dct="http://purl.org/dc/terms/" href="http://www.readysetraphael.com" rel="dct:source">www.readysetraphael.com</a>.
</p>

