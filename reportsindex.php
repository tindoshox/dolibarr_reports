<?php

/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2015 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (php -i | grep "Loaded Configuration File"C) 2015      Jean-François Ferry	<jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *	\file       reports/reportsindex.php
 *	\ingroup    reports
 *	\brief      Home page of reports top menu
 */

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
  $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"] . "/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
  $i--;
  $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)) . "/main.inc.php")) {
  $res = @include substr($tmp, 0, ($i + 1)) . "/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php")) {
  $res = @include dirname(substr($tmp, 0, ($i + 1))) . "/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
  $res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
  $res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
  $res = @include "../../../main.inc.php";
}
if (!$res) {
  die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT . '/core/class/html.formfile.class.php';

// Load translation files required by the page
$langs->loadLangs(array("reports@reports"));

$action = GETPOST('action', 'aZ09');


// Security check
if (! $user->rights->reports->myobject_list->read) {
	accessforbidden();
}

$socid = GETPOST('socid', 'int');

if (isset($user->socid) && $user->socid > 0) {
  $action = '';
  $socid = $user->socid;
}

$max = 5;
$now = dol_now();
$arrayHist = [];
$arrayLabels = [];
$usercandelete = $user->rights->reports->myobject_list->delete;


//Fetch Today's Money
$daymoney =0;
$daycount=0;
$sqlday = 'select round(sum(pf.amount),0) sum, count(pf.amount) count from ' . MAIN_DB_PREFIX . 'paiement_facture pf';
$sqlday .= ' join ' . MAIN_DB_PREFIX . 'paiement p on p.rowid  = pf.fk_paiement ';
$sqlday .= ' where date(p.datep) = date(now())';
$sqlday .= $usercandelete ? '' : ' and p.fk_user_creat = '.$user->id.'';
$resday = $db->query($sqlday);
if($resday){
$objday = $db->fetch_object($resday);
$daymoney = $objday->sum;
$daycount = $objday->count;
}
//Fetch Current Month Money
$monthmoney=0;
$monthcount=0;
$sqlmonth = 'SELECT round(sum(pf.amount),0) sum, count(pf.amount) count from ' . MAIN_DB_PREFIX . 'paiement_facture pf';
$sqlmonth .= ' join ' . MAIN_DB_PREFIX . 'paiement p on p.rowid  = pf.fk_paiement ';
$sqlmonth .= " where to_char(p.datep, 'MONTH YYYY') =  to_char(current_date, 'MONTH YYYY')";
$sqlmonth .= $usercandelete ? '' : ' and p.fk_user_creat = '.$user->id.'';
$resmonth = $db->query($sqlmonth);
if($resmonth){
$objmonth = $db->fetch_object($resmonth);
$monthmoney = $objmonth->sum;
$monthcount = $objmonth->count;
}
//Fetch Previous Month Money
$prevmoney=0;
$prevcount=0;
$sqlprev = "SELECT round(sum(pf.amount),0) sum, count(pf.amount) count from " . MAIN_DB_PREFIX . "paiement_facture pf";
$sqlprev .= " join " . MAIN_DB_PREFIX . "paiement p on p.rowid  = pf.fk_paiement ";
$sqlprev .= " where to_char(p.datep, 'MONTH YYYY') =  to_char(current_date - interval '1 month', 'MONTH YYYY')";
$sqlprev .= $usercandelete ? '' : ' and p.fk_user_creat = '.$user->id.'';
$resprev = $db->query($sqlprev);
if($resprev){
$objprev = $db->fetch_object($resprev);
$prevmoney = $objprev->sum;
$prevcount = $objprev->count;
}
//Fetch Sales Totals
$value=0;
$invoices=0;
$salesqsl = 'select count(rowid), sum(total_ttc) from ' . MAIN_DB_PREFIX . 'facture';
$salesqsl .= " where type = 0 and to_char(datef, 'MONTH YYYY') =  to_char(current_date, 'MONTH YYYY') and paye=0";
$salesqsl .= $usercandelete ? '' : ' and fk_user_author = '.$user->id.'';
$ressales = $db->query($salesqsl);
if($ressales){
$objsales = $db->fetch_object($ressales);
$invoices = $objsales->count;
$value = $objsales->sum;}

echo '<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
<link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
<link href="dashboard/css/nucleo-icons.css" rel="stylesheet" />
<link href="dashboard/css/nucleo-svg.css" rel="stylesheet" />
<script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
<link href="dashboard/css/nucleo-svg.css" rel="stylesheet" />
<link id="pagestyle" href="dashboard/css/soft-ui-dashboard.css?v=1.0.3" rel="stylesheet" />';

llxHeader("", $langs->trans("Reports"));

print load_fiche_titre($langs->trans("Overview"), '');

echo '<main class="main-content position-relative max-height-vh-100 h-100 mt-1 border-radius-lg ">
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Today\'s Total</p>
                    <h5 class="font-weight-bolder mb-0">
                      ' . number_format($daymoney ?? 0, 2, ".", ",") . '
                      <span class="text-success text-sm font-weight-bolder">
                      ' . number_format($daycount ?? 0, 0, ".", ",") . '
                      </span>
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                    <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-capitalize font-weight-bold">' . date("F") . ' Total</p>
                    <h5 class="font-weight-bolder mb-0">
                      ' . number_format($monthmoney ?? 0, 2, ".", ",") . '
                      <span class="text-success text-sm font-weight-bolder">
                      ' . number_format($monthcount ?? 0, 0, ".", ",") . '
                      </span>
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                    <i class="ni ni-world text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
		<div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="row">
                <div class="col-8">
                  <div class="numbers">
                    <p class="text-sm mb-0 text-capitalize font-weight-bold">' . date('F', strtotime('last month')) . ' Total</p>
                    <h5 class="font-weight-bolder mb-0">
                      ' . number_format($prevmoney ?? 0, 2, ".", ",") . '
                      <span class="text-success text-sm font-weight-bolder">
                      ' . number_format($prevcount ?? 0, 0, ".", ",") . '
                      </span>
                    </h5>
                  </div>
                </div>
                <div class="col-4 text-end">
                  <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                    <i class="ni ni-world text-lg opacity-10" aria-hidden="true"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
	  <div class="row">
	  <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
	  <div class="card">
		<div class="card-body p-3">
		  <div class="row">
			<div class="col-8">
			  <div class="numbers">
				<p class="text-sm mb-0 text-capitalize font-weight-bold">' . date("F") . ' Invoices</p>
				<h5 class="font-weight-bolder mb-0">
				' . number_format($invoices ?? 0, 0, ".", ",") . '
				  <span class="text-danger text-sm font-weight-bolder"></span>
				</h5>
			  </div>
			</div>
			<div class="col-4 text-end">
			  <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
				<i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i>
			  </div>
			</div>
		  </div>
		</div>
	  </div>
	</div>
	<div class="col-xl-3 col-sm-6">
	  <div class="card">
		<div class="card-body p-3">
		  <div class="row">
			<div class="col-8">
			  <div class="numbers">
				<p class="text-sm mb-0 text-capitalize font-weight-bold">' . date("F") . ' Sales Value</p>
				<h5 class="font-weight-bolder mb-0">
				' . number_format($value ?? 0, 2, ".", ",") . '
				  <span class="text-success text-sm font-weight-bolder"></span>
				</h5>
			  </div>
			</div>
			<div class="col-4 text-end">
			  <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
				<i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
			  </div>
			</div>
		  </div>
		</div>
	  </div>
	</div>
  <div class="row mt-4">
        <div class="col-lg-5 mb-lg-0 mb-4">
          <div class="card z-index-2">
            <div class="card-body p-3">
              <div class="bg-gradient-dark border-radius-lg py-3 pe-1 mb-3">
                <div class="chart">
                  <canvas id="chart-bars" class="chart-canvas" height="170"></canvas>
                </div>
              </div>
              <div class="row">
                <div class="col-3 py-3 ps-0">
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>';



$sql = strval("SELECT to_char(generate_series(now()- INTERVAL '1 year', now(), '1 month'), 'MON-YYYY') AS \"period\"");
$resql = $db->query($sql);
$num = $db->num_rows($resql);
$arrayp = pg_fetch_all($resql);

if ($num > 0) {
  foreach ($arrayp  as  $array) {
    $sqlhist = "SELECT round(sum(pf.amount),0) sum from " . MAIN_DB_PREFIX . "paiement_facture pf";
    $sqlhist .= " join " . MAIN_DB_PREFIX . "paiement p on p.rowid  = pf.fk_paiement ";
    $sqlhist .= " where to_char(p.datep, 'MON-YYYY') =  '" . $array['period'] . "'";
    $sqlhist .= $usercandelete ? '' : ' and p.fk_user_creat = '.$user->id.'';
    $reshist = $db->query($sqlhist);
    $arrayhist = pg_fetch_all($reshist);
    array_push($arrayLabels, $array['period']);
    array_push($arrayHist, $arrayhist[0]['sum']);
  }
}

echo '  <script src="dashboard/js/plugins/chartjs.min.js"></script>';
echo '  <script>
    var ctx = document.getElementById("chart-bars").getContext("2d");

    new Chart(ctx, {
      type: "bar",
      data: {
        labels: ' . json_encode($arrayLabels) . ',
        datasets: [{
          label: "Collections",
          tension: 0.4,
          borderWidth: 0,
          borderRadius: 4,
          borderSkipped: false,
          backgroundColor: "#fff",
          data: ' . json_encode($arrayHist) . ',
          maxBarThickness: 6
        },],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          }
        },
        interaction: {
          intersect: false,
          mode: \'index\',
        },
        scales: {
          y: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false,
            },
            ticks: {
              suggestedMin: 500,
              suggestedMax: 1000,
              beginAtZero: false,
              padding: 15,
              font: {
                size: 14,
                family: "Open Sans",
                style: \'normal\',
                lineHeight: 2
              },
              color: "#fff"
            },
          },
          x: {
            grid: {
              drawBorder: false,
              display: false,
              drawOnChartArea: false,
              drawTicks: false
            },
            ticks: {
              display: false
            },
          },
        },
      },
    });
  </script>';

llxFooter();
$db->close();
