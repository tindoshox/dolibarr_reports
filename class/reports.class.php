<?php

require_once DOL_DOCUMENT_ROOT . '/vendor/autoload.php';


use PHPJasper\PHPJasper;

Class ReportGen {

static public function get_report($params, $reportid, $passkey, $format=['pdf'])
{
	
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
			'driver' => 'postgres',
			'username' => $passkey,
			'password' => $passkey,
			'host' => '127.0.0.1',
			'database' => $passkey,
			'port' => '5432'
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