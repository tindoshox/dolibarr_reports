<?php

require_once DOL_DOCUMENT_ROOT . '/vendor/autoload.php';


use PHPJasper\PHPJasper;

Class ReportGen {

static public function get_report($params, $reportid, $passkey, $format=['pdf'])
{
	global $dolibarr_main_db_name, $dolibarr_main_db_host, $dolibarr_main_db_user, $dolibarr_main_db_port, $dolibarr_main_db_pass, $dolibarr_main_db_type;

	if (!file_exists(DOL_DATA_ROOT . '/reports/' . $reportid)) {
		if (!mkdir($concurrentDirectory = DOL_DATA_ROOT . '/reports/' . $reportid, 0777, true) && !is_dir($concurrentDirectory)) {
			throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
		}
	}


	$input = __DIR__ . '/../jasper/' . $reportid . '/main.jrxml';
	$output = DOL_DATA_ROOT . '/reports/' . $reportid . '/' . $reportid;

	if (!file_exists(__DIR__ . '/../jasper/' . $reportid . '/main.jasper')) {
		$jasper = new PHPJasper;
		$jasper->compile($input)->execute();
	}

	if (filemtime(__DIR__ . '/../jasper/' . $reportid . '/main.jasper') < filemtime(__DIR__ . '/../jasper/' . $reportid . '/main.jrxml')) {
		$jasper = new PHPJasper;
		$jasper->compile($input)->execute();
	}


	$options = [
		'format' => $format,
		'locale' => 'en',
		'params' => $params,
		'db_connection' => [
			'driver' => $dolibarr_main_db_type === 'pgsql'?'postgres':'mysql',
			'username' => $dolibarr_main_db_user,
			'password' => $dolibarr_main_db_pass,
			'host' => $dolibarr_main_db_host,
			'database' => $dolibarr_main_db_name,
			'port' => $dolibarr_main_db_port
		]
	];

	$jasper = new PHPJasper;

	$jasper->process(
		$input,
		$output,
		$options
	)->execute();  

	return 1;
}

}
