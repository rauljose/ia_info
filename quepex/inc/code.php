<?php
/**
 * ini show all o sobresaliente?
 * try composer outded path?
 * try composer outdated este programa?
 * composer security check?
 * break code
 * // https://www.owasp.org/index.php/PHP_Configuration_Cheat_Sheet#php.ini ?
 */
ini_set("date.timezone", "America/Mexico_City");
error_reporting(E_ALL ^ E_DEPRECATED);
ini_set('display_errors',1);
ini_set('memory_limit', 1*1024*1024*1024);
set_time_limit(60*3);
require_once(__DIR__ .'/../../vendor/autoload.php');
require_once(__DIR__ .'/ScanWebIni.php');

function batAnalysis($dataSource, $checkPhpVersion=null, $showUnused=false, $showDetails=false, $iniAllRules=false) {

    $client = new \Bartlett\Reflect\Client();
    $api = $client->api('analyser');
    $metrics = $api->run($dataSource, ['compatibility','structure']); // 'loc'
    $analisis = $metrics['Bartlett\CompatInfo\Analyser\CompatibilityAnalyser'];
    ksort($analisis['extensions'],  SORT_NATURAL | SORT_FLAG_CASE);

    $summary['Need'] = [];
    if(!empty($checkPhpVersion)) {
        $checkPhpVersion = trim($checkPhpVersion);
        $labelPhpVersion = 'Check php '.$checkPhpVersion;
        $summary[$labelPhpVersion] = [];
    }
    $summary['Current'] = [];

///// Need
    $summary['Need']['php version'] = versionPHPDisplay($analisis['versions']);
    $summary['Need']['php exstensions'] = implode(', ', array_keys($analisis['extensions']) );

/// Info
    $deprecated = deprecated($analisis['extensions']);
    if(!empty($deprecated)) {
        $summary['Info']['* Deprecated']['* extensions'] = implode(', ', $deprecated );
    }
    $deprecated = deprecated($analisis['classes']);
    if(!empty($deprecated)) {
        $summary['Info']['* Deprecated']['* classes'] = implode(', ', $deprecated );
    }
    $deprecated = deprecated($analisis['functions']);
    if(!empty($deprecated)) {
        $summary['Info']['* Deprecated']['* functions'] = implode(', ', $deprecated );
    }

    if($showUnused) {
        $unused = unused($analisis['classes']);
        if(!empty($unused)) {
            $summary['Info']['Unused']['Unused classes ('.count($unused).')'] = implode(', ', $unused );
        }
        $unused = unused($analisis['functions']);
        if(!empty($unused)) {
            $summary['Info']['Unused']['Unused functions ('.count($unused).')'] = implode(', ', $unused );
        }
    }

    $report = phpOrExtension($analisis['classes']);
    $summary['Info']['php']['php classes ('.count($report).')'] = implode(', ', $report);
    $report = phpOrExtension($analisis['functions']);
    $summary['Info']['php']['php functions ('.count($report).')'] = implode(', ', $report );

    if($showDetails) {
        $report = userConstructs($analisis['classes']);
        $summary['Info']['Sistema']['Sistema classes ('.count($report).')'] = implode(', ', $report );
        $report = userConstructs($analisis['functions']);
        $summary['Info']['Sistema']['Sistema functions ('.count($report).')'] = implode(', ', $report );
    }

/// for php version
    if(!empty($checkPhpVersion)) {

        $starVersion = versionPHPOk($analisis['versions'], $checkPhpVersion) ? '' : '* ';
        $summary[$labelPhpVersion][$starVersion . 'php version'] = $checkPhpVersion;

        $extensionsBadVersion = versionMissing($checkPhpVersion, $analisis['extensions']);
        if(!empty($extensionsBadVersion)) {
            $summary[$labelPhpVersion]['* Version issues']['* extensions'] = implode(', ', $extensionsBadVersion);
        }
        $classesBadVersion = versionMissing($checkPhpVersion, $analisis['classes']);
        if(!empty($classesBadVersion)) {
            $summary[$labelPhpVersion]['* Version issues']['* classes'] = implode(', ', $classesBadVersion);
        }
        $functionsBadVersion = versionMissing($checkPhpVersion, $analisis['functions']);
        if(!empty($functionsBadVersion)) {
            $summary[$labelPhpVersion]['* Version issues']['* functions'] = implode(', ', $functionsBadVersion);
        }
    }

/// Current server
    $starVersion = versionPHPOk($analisis['versions'], PHP_VERSION) ? '' : '* ';
    $summary['Current'][$starVersion . 'php version'] = PHP_VERSION;

    $extensionsBadVersion = versionMissing(PHP_VERSION, $analisis['extensions']);
    if(!empty($extensionsBadVersion)) {
        $summary['Current']['* Version issues']['* extensions'] = implode(', ', $extensionsBadVersion);
    }
    $classesBadVersion = versionMissing(PHP_VERSION, $analisis['classes']);
    if(!empty($classesBadVersion)) {
        $summary['Current']['* Version issues']['* classes'] = implode(', ', $classesBadVersion);
    }
    $functionsBadVersion = versionMissing(PHP_VERSION, $analisis['functions']);
    if(!empty($functionsBadVersion)) {
        $summary['Current']['* Version issues']['* functions'] = implode(', ', $functionsBadVersion);
    }

    $loadedExtensions = get_loaded_extensions();
    asort($loadedExtensions, SORT_NATURAL | SORT_FLAG_CASE);
    $summary['Current']['php exstensions']['Loaded'] = implode(', ', $loadedExtensions );
    $neededExtensions = array_diff_key(
        array_change_key_case( $analisis['extensions'] ),
        array_change_key_case( array_change_key_case(array_flip($loadedExtensions)) )
    );
    if(!empty($neededExtensions)) {
        $summary['Current']['php exstensions']['* Missing php exstensions'] = implode(', ', array_keys($neededExtensions));
    }

    $notNeededExtensions = array_diff_key(
        array_change_key_case( array_change_key_case(array_flip($loadedExtensions)) ),
        array_change_key_case( $analisis['extensions'] )

    );
    if(!empty($notNeededExtensions)) {
        $summary['Current']['php exstensions']['Check if exstensions are needed'] = implode(', ', array_keys($notNeededExtensions));
    }

    $summary['Current']['classes']['Disabled'] = ini_get('disable_classes');
    $neededClassesDisabled = array_intersect_key(
        array_flip(explode(',', removeAllSpaces(strtolower(ini_get('disable_classes'))))),
        array_change_key_case($analisis['classes'])
    );
    if(!empty($neededClassesDisabled)) {
        $summary['Current']['classes']['* Disabled classes, but required'] = implode(', ', array_keys($neededClassesDisabled));
    }

    $summary['Current']['functions']['Disabled'] = ini_get('disable_functions');
    $neededFunctionsDisabled = array_intersect_key(
        array_flip(explode(',', removeAllSpaces(strtolower(ini_get('disable_functions'))))),
        array_change_key_case($analisis['functions'])
    );
    if(!empty($neededFunctionsDisabled)) {
        $summary['Current']['functions']['* Disabled functions, but required'] = implode(', ', array_keys($neededFunctionsDisabled));
    }

    $summary['Current']['Host']['uname'] = php_uname();
    $summary['Current']['Host']['sapi'] = php_sapi_name();
    $summary['Current']['Host']['include_path'] = get_include_path();

    $summary['Current']['php.ini']['loaded'] = php_ini_loaded_file();
    $summary['Current']['php.ini']['scanned'] = php_ini_scanned_files();
    $summary['Current']['php.ini']['review'] = currentIniSettingsReport($iniAllRules);

    return $summary;
}

// versions
    function versionOk($version, $item) {
        // $item =    [ [ext.min] => [ext.max] => [ext.all] =>, [php.min] => 7.3.0alpha4, [php.max] => , [php.all] => 7.3.0alpha4 ]
        if(!empty($item['php.min']) && version_compare($version, strtolower($item['php.min'])) < 0 ) {
            return false;
        }
        if(!empty($item['php.max']) && version_compare($version, strtolower($item['php.max'])) > 0 ) {
            return false;
        }

        if(!empty($item['ext.min']) && version_compare($version, strtolower($item['ext.min'])) < 0 ) {
            return false;
        }
        if(!empty($item['ext.max']) && version_compare($version, strtolower($item['ext.max'])) > 0 ) {
            return false;
        }

        return true;
    }

    function versionMissing($version, $array) {
        $used = [];
        foreach($array as $name=>$a) {
            if(!versionOk($version, $a)) {
                $used[] = $name;
            }
        }
        asort($used, SORT_NATURAL | SORT_FLAG_CASE);
        return $used;
    }

    function versionPHPOk($versions, $phpVersion) {
        $phpVersion = strtolower($phpVersion);
        if(version_compare($phpVersion, $versions['php.min']) < 0 ) {
            return false;
        }
        if(!empty($versions['php.max']) && version_compare($phpVersion, $versions['php.max']) > 0 ) {
            return false;
        }
        return true;
    }
    function versionPHPDisplay($versions) {
        if(empty($versions['php.max'])) {
            return $versions['php.min'];
        }
        if(version_compare($versions['php.min'], $versions['php.max'])>0) {
            return "<span class='red'>".print_r($versions, true)."</span>";
        }
        return $versions['php.min'] . ' => ' . $versions['php.max'];
    }

/////////////////
    function deprecated($items) {
        $used = [];
        foreach($items as $name => $d) {
            if(!empty($d['deprecated']) ) {
                $used[] = $name;
            }
        }
        asort($used, SORT_NATURAL | SORT_FLAG_CASE);
        return $used;
    }

    function unused($items) {
        $unUsed = [];
        foreach($items as $name => $f) {
            if(isset($f['matches']) && empty($f['matches'])) {
                $unUsed[] = $name;
            }
        }
        asort($unUsed, SORT_NATURAL | SORT_FLAG_CASE);
        return $unUsed;
    }

    /**
     * filters out non php, that is user functions, clases, etc
     *
     * @param array $items
     * @return array ['floor','ceil']
     */
    function phpOrExtension($items) {
        $used = [];
        foreach($items as $fun => $d) {
            if($d['ext.name']!=='user') {
                $used[] = $fun;
            }
        }
        asort($used, SORT_NATURAL | SORT_FLAG_CASE);
        return $used;
    }

    /**
     * returns user functions, clases, etc
     *
     * @param array $items
     * @return array ['floor','ceil']
     */
    function userConstructs($items) {
        $used = [];
        foreach($items as $fun => $d) {
            if($d['ext.name']==='user') {
                $used[] = $fun;
            }
        }
        asort($used, SORT_NATURAL | SORT_FLAG_CASE);
        return $used;
    }

//////////////
    /**
     * Nested array to nested ol, key starts with * adds class red to li
     *
     * @param array $array
     * @return void
     */
    function array_prettyPrint($array) {
        echo "<ol>";
        foreach($array as $k=>$v) {
            $class = substr($k, 0, 1) === '*' && !empty($v) ? 'red' : 'normal';
            echo "<li class='$class'><b>$k</b>: ";
            if(is_array($v)) {
                array_prettyprint($v);
            } else {
                echo $v;
            }
        }
        echo "</ol>";
    }
    function removeAllSpaces($s) {
        if($s === null || $s === false)
            return '';
        return \preg_replace('/\s+/', '',  \trim($s) );
    }

/////////////
    function currentIniSettingsReport($iniAllRules) {
        $ini = new ScanWebIni(null); // ($path = null, array $context = array(), $threshold = null, $version = null)
        return $ini->report( $ini->execute(), $iniAllRules );
    }
