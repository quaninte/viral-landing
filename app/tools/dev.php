<?php
function vd($var = false, $trace = 1, $showHtml = false, $showFrom = true) {
    if ($showFrom) {
        $calledFrom = debug_backtrace();
        for ($i = 0; $i < $trace; $i++) {
            if (!isset($calledFrom[$i]['file'])) {
                break;
            }
            echo substr($calledFrom[$i]['file'], 1);
            echo "\n" . ' (line <strong>' . $calledFrom[$i]['line'] . '</strong>)';
            echo "<br />";
        }
    }
    echo "<pre class=\"cake-debug\">\n";

    $var = var_dump($var);
    if ($showHtml) {
        $var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
    }
    echo $var . "\n</pre>\n";
}
function vdd($var = false, $trace = 1, $showHtml = false, $showFrom = true) {
    if ($showFrom) {
        $calledFrom = debug_backtrace();
        for ($i = 0; $i < $trace; $i++) {
            if (!isset($calledFrom[$i]['file'])) {
                break;
            }
            echo substr($calledFrom[$i]['file'], 1);
            echo "\n" . ' (line <strong>' . $calledFrom[$i]['line'] . '</strong>)';
            echo "<br />";
        }
    }
    echo "<pre class=\"cake-debug\">\n";

    $var = var_dump($var);
    if ($showHtml) {
        $var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
    }
    echo $var . "\n</pre>\n";
    die;
}