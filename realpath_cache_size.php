<!DOCTYPE HTML>
<html>
<head>
    <meta charset="UTF-8">
    <title>Real path cache</title>
    <style>
        BODY {line-height:1.5em; margin: 1em 2em 4em 0.5em;}
    </style>
</head>
<body>
    <?php
        echo "<ul>";
        echo "<li>Real path Cache size: ". bytes2units(realpath_cache_size())." (".realpath_cache_size().") ";
        echo "<li>realpath_cache_ttl: ".ini_get('realpath_cache_ttl');
        echo "<li>real path entries: ".number_format( count(realpath_cache_get()), 0, "", ",");
        echo "</ul>";
        function bytes2units($bytes) {
        if(empty($bytes) || !is_numeric($bytes))
            return $bytes;
        if($bytes < 0) {
            $signo = '-';
            $bytes *= -1;
        } else
            $signo = '';
        if($bytes < 1024) {
            $decs = 0;
            $punto = '';
        } else {
            $decs = 2;
            $punto = '.';
        }
        $unit=array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb');
        return $signo.number_format($bytes/pow(1024,($i=floor(log($bytes,1024)))),$decs,$punto,',').' '.$unit[$i];
    }
    ?>
    <pre>
        ini settings:
            realpath_cache_size = 64k
            realpath_cache_ttl = 360
        <i>* ttl in seconds defaults to 120, size defaults to 16k</i>
    </pre>
</body>
</html>