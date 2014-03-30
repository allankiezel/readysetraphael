<?php
/**
 * Created by PhpStorm.
 * User: allankiezel
 * Date: 3/28/14
 * Time: 3:11 PM
 */

use AllanKiezel\ReadySetRaphael\Parser as Parser;

require __DIR__.'/vendor/autoload.php';

?>

<!DOCTYPE html>
<html>
<head>
    <title></title>
    <script src="https://raw.github.com/DmitryBaranovskiy/raphael/master/raphael-min.js" type="text/javascript" charset="utf-8"></script>
</head>

<body>
<div id="rsr"></div>
<div id="output">
    <script>
        <?php

            try {
                $xml =  file_get_contents(__DIR__ . '/svg/lion.svg');
                $parser = new Parser($xml);
            } catch (Exception $e) {
                echo $e->getMessage() . '<br>';
                echo $e->getTraceAsString();
            }

        ?>
    </script>
</div>
<script><?php $parser->generateJs(); ?></script>
</body>