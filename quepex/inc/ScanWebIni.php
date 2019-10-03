<?php

class ScanWebIni extends \Psecio\Iniscan\Scan {

    /**
	 * Parse the configuration (php.ini) file
	 *
	 * @param string $path Path to the string to parse
	 * @return array Ini settings
	 */
	public function parseConfig($path = null)
	{
        $iniArray = ini_get_all(null, false);
        $this->setConfig($iniArray);
        return $iniArray;


		$ini = parse_ini_file( (!is_null($path) ? $path : $this->path));
		// pull in settings from other scanned INI files
		$scannedIniList = php_ini_scanned_files();
		if ($scannedIniList !== false) {
			foreach(explode(',', $scannedIniList) as $scannedFile) {
				$scannedIni = parse_ini_file(trim($scannedFile));
				$ini = array_merge($ini, $scannedIni);
			}
		}
		$this->setConfig($ini);
		return $ini;
	}

    public function report($results, $iniAllRules = false) {
        $report = "<table class='initable'>
            <caption>ini settings</caption>
            <thead>
            <tr><th>Status<th>Section<th>Key<th>Current<br>Value<th>Description<th>Note
            </thead>
            <tbody>";
        foreach($results as $k)
            if($iniAllRules || $k->getStatus()!=1)
            {
            if($k->getStatus() == 1)
                $status = "<span class='green'>Ok</span>";
            elseif($k->getStatus() === 0)
                $status = "<span class='red'>Error</span>";
            else
                $status = "<span class='warn'>Checkme</span>";
            $report .=  "<tr>".
                "<td>".$status.
                "<td>".htmlentities($k->getSection()).
                "<td>".htmlentities($k->getTestKey()).
                "<td>".htmlentities($k->getValue()).
                "<td>".htmlentities($k->getName()).
                "<td>".htmlentities($k->getDescription()).
                "";

        }
        $deprecated = $this->getMarked();
        if(!empty($deprecated)) {
            foreach($results as $k) {
                $status = "<span class='red'>Depreceated</span>";
                $report .=  "<tr>".
                    "<td>".$status.
                    "<td>".htmlentities($k).
                    "<td>".
                    "<td>".
                    "<td>".
                    "<td>".
                    "";
            }
        }
        $report .=  "</tbody></table>";
        return $report;
    }

}
