<?php

//Dates 

use PhpOffice\PhpSpreadsheet\Writer\Xlsx\Rels;

function dateInput($form, $conf)
{
	print '<tr>';
	print '<td>From Date</td>';
	print '<td>';
	$from_date = strtotime(format_date(GETPOST('from_date')));
	$from_date = ($from_date == '' ? (!empty($conf->global->MAIN_AUTOFILL_DATE) ? $from_date : '') : $from_date);
	print $form->selectDate($from_date, 'from_date', '', '', 0, "", 1, 1,);
	print '</td>';
	print '</tr>';

	//To Date
	print '<tr>';
	print '<td>To Date</td>';
	print '<td>';
	$to_date = strtotime(format_date(GETPOST('to_date')));
	$to_date = ($to_date == '' ? (!empty($conf->global->MAIN_AUTOFILL_DATE) ? $to_date : '') : $to_date);
	print $form->selectDate($to_date, 'to_date', '', '', 0, "", 1, 1,);
	print '</td>';
	print '</tr>';
}

// Group Select
function group_select($formcompany, $selected_group)
{
	print '<tr>';
	print '<td>Group</td>';
	print '<td>' . $formcompany->select_state('' . $selected_group . '', 205, 'state_id') . '</td>';
	print '</td></tr>';
}

// Salesperson select
function select_salesperson($selected_salesperson, $formother, $user)
{
	print '<tr>';
	print '<td>Salesperson</td>';
	print '<td>' . $formother->select_salesrepresentatives($selected_salesperson, 'userid', $user, 1) . '</td>';
	print '</tr>';
}

// Submit Button
function submit_button($params, $reportid, $format, $validation)
{
	//Submit Button
	print '<tr><td>';
	print '<input type="submit" value="submit" name="submit" class="Submit butAction">';
	print '</td>';
	if(isset($_POST['submit'])){
		if($validation == false){
			//error message
		}
		ReportGen::get_report($params, $reportid, $format);
	}
	

}

// Change date format
function format_date($originalDate)
{
	$date = str_replace('/', '-', $originalDate);
	return $originalDate == '' ? '' : strval(date("Y-m-d", strtotime($date)));
}
