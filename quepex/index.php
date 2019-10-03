<!DOCTYPE HTML>
<html>
<head>
    <meta charset="UTF-8">
    <title>¿Que Pex?</title>
    <style>
        BODY {line-height:1.5em; margin: 1em 2em 4em 0.5em;}
        OL {margin-top:0.6em}
        LI {margin-bottom:0.6em}
        .red {color: red;}
        .green {color:darkgreen;}
        .warn{color:orange}
        .normal {color:black}
        LABEL.box {padding:0.5em;margin-right:2em;border:1px silver solid;white-space: nowrap;}
        TABLE.initable {border:1px silver solid;border-collapse:collapse}
        TABLE.initable CAPTION {border:1px silver solid;font-weight:bold;font-size:1.4em}
        TABLE.initable TH {border:1px silver solid;padding:0.5em}
        TABLE.initable TD {border:1px silver solid;padding:0.5em;vertical-align: top;}
    </style>
</head>
<body>
    <h1 style="text-align: center;">¿Que Pex?</h1><hr />
    <?php
    // https://blog.martinhujer.cz/have-you-tried-composer-scripts/

        require_once(__DIR__ . '/inc/code.php');
        $input['path'] = !empty($_POST['path']) ? trim($_POST['path']) : __DIR__;
        $pathHTML = htmlentities($input['path'], ENT_QUOTES);
        $input['forVersion'] = isset($_POST['phpVersion']) ? trim($_POST['phpVersion']) : null;
        $forVersionHTML = $input['forVersion'] == null ? '' : htmlentities($input['forVersion'], ENT_QUOTES);
        $input['showUnused'] = !empty($_POST['showUnused']);
        $showUnusedSelected = $input['showUnused'] ? ' checked=checked ' : '';
        $input['showDetails'] = !empty($_POST['showDetails']);
        $showDetailsSelected = $input['showDetails'] ? ' checked=checked ' : '';
        $input['iniAllRules'] = !empty($_POST['iniAllRules']);
        $iniAllRulesSelected = $input['iniAllRules'] ? ' checked=checked ' : '';

        if(isset($_POST['path'])) {
            echo "<h1>$pathHTML</h1>";
            $summary = batAnalysis($input['path'], $input['forVersion'], $input['showUnused'], $input['showDetails'], $input['iniAllRules']);
            array_prettyPrint($summary);
            echo "<hr />";
        }

    ?>
    <form method="POST" style="margin: 2em;">
        <label for="path">Path</label><br />
        <input id="path" name="path" type="text" value="<?= $pathHTML; ?>" style="width: 90%;padding:0.5em" />
        <p>
        <label for="phpVersion">Test php Version</label><br />
        <input id="phpVersion" name="phpVersion" type="text" value="<?= $forVersionHTML; ?>" style="width: 8em;padding:0.5em" />
        <p>
            <label class="box"> <input name="showUnused" type="checkbox" value="1" <?= $showUnusedSelected; ?> /> Show PHP Unused </label>
            <label class="box"> <input name="showDetails" type="checkbox" value="1" <?= $showDetailsSelected; ?> /> Show PHP Details</label>
            <label class="box"> <input name="iniAllRules" type="checkbox" value="1" <?= $iniAllRulesSelected; ?> /> Show all ini rules</label>

        <p style="text-align: center;"><input type="submit" value="Reportar" /></p>
    </form>

    <hr /><div style="margin-bottom: 2em;"><i>* Incluye todo lo requerido en vendor/ aunque no lo corrra el sistema.</i></div>

</body>
</html>