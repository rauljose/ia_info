<?php
/**
 * @author Informática Asocaida SA de CV
 * @version 1.0.0
 * @copyright 2017
 */

function is64bitPHP() {
    return PHP_INT_SIZE == 8;
}
echo "<h1>is64=".is64bitPHP()."=".(strlen(decbin(~0)) == 64)."=".(intval("9223372036854775807") == 9223372036854775807)."=</h1>";

?>