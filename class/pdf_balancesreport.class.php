<?php

require_once DOL_DOCUMENT_ROOT . '/includes/tecnickcom/tcpdf/tcpdf.php';


class BalancesReportPDF extends TCPDF
{
	public $currentTown = '';

	public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
	{
		parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);

		// Set equal left and right margins

		$this->SetMargins(15, 10, 15);
		$this->SetHeaderMargin(5);

		$this->SetAutoPageBreak(true, 15);
	}

	// Page Header : Town
	public function Header()
	{
		$this->SetFont('helvetica', 'B', 14);
		$this->Cell(0, 10, $this->currentTown, 0, 1, 'C');
		$this->Ln(2);
	}

	// Footer displays page count, Town and Print time
	public function Footer()
	{
		$this->SetY(-15);
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10,
			'Page ' . $this->getGroupPageNo() . ' of ' . $this->getPageGroupAlias() .
			' | ' . $this->currentTown .
			' | Printed: ' . date('d-m-Y H:i')
		);
	}

	public function renderReport($data, $payments = [], $agenda=[])
	{
		if (empty($data)) {
			$this->AddPage();
			$this->SetFont('helvetica', 'B', 14);
			$this->SetTextColor(150, 0, 0);
			$this->Cell(0, 10, 'No records found for the selected filters.', 0, 1, 'C');
			return;
		}
		$grouped = [];

		foreach ($data as $row) {
			$town = strtoupper(trim($row['town']));
			$addr = strtoupper(trim($row['address']));
			if (!isset($grouped[$town])) $grouped[$town] = [];
			if (!isset($grouped[$town][$addr])) $grouped[$town][$addr] = [];
			$grouped[$town][$addr][] = $row;
		}

		foreach ($grouped as $town => $addresses) {
			$this->currentTown = $town; // Set the current state/town for the header
			$this->startPageGroup(); // resets numbering for this group
			$this->AddPage();


			foreach ($addresses as $address => $invoices) {
				$this->Ln(2);
				$this->SetFont('helvetica', 'B', 12);
				$this->SetTextColor(0,0,0);
				$this->Cell(0, 8, strtoupper($address), 0, 1, 'C'); // Centered, no label
				foreach ($invoices as $inv) {
					// Estimate space needed per invoice block (you can tweak this)
					$blockHeight = 22; // ~3 rows * 6–7mm each

					if ($this->GetY() + $blockHeight > ($this->getPageHeight() - $this->getBreakMargin())) {
						$this->AddPage();
						$this->SetFont('helvetica', 'B', 12);
						$this->Cell(0, 8, strtoupper($address), 0, 1, 'C');
					}

					// Row One main invoice info
					$this->SetFont('helvetica', '', 10);
					if(strtotime($inv['due_date']) < dol_now()){
						$this->SetTextColor(182,0,22);
					}

					$this->Cell(25, 6, $inv['invoice_num'], 0, 0); // Ref
					$this->Cell(60, 6, substr($inv['nom'],0,21), 0, 0);         // Name (wider)
					$this->Cell(25, 6, $inv['phone'], 0, 0);       // Phone
					$this->Cell(25, 6, substr($inv['prod'],0,10), 0, 0);        // Product
					$this->Cell(20, 6, number_format($inv['balance'], 2), 0, 0, 'R'); // Balance
					$this->Cell(0, 6, $inv['due_date'].'('.date('D',strtotime($inv['due_date'])).')', 0, 1, 'R'); // Due Date (fills remaining)

					//Row Two
					$this->SetFont('helvetica', '', 8);
					$this->SetTextColor(0,0,0);
					// Invoice Date
					$this->Cell(40, 5, "INVOICE DATE: ".$inv['invoice_date'], 0, 0);
					// Delivery Note (label + value)
					$this->Cell(45, 5, "DELIVERY NOTE: ".$inv['deliver_num'], 0, 0);
					// Fax under phone
					$this->Cell(30, 5, $inv['fax'], 0, 0);
					//Agenda
					$agendaText = $agenda[$inv['rowid']] ?? '';
					if ($agendaText) {
						$this->Cell(0, 5, $agendaText, 0, 1, 'R');
					} else {
						$this->Ln();
					}

					//Row 3 Print actual payments
					$this->SetTextColor(0,40,159);
					$this->SetFont('helvetica', 'I', 8);
					$paycells = array_reverse($payments[$inv['rowid']] ?? []); // newest to oldest
					$paycells = array_slice($paycells, 0, 6); // max 6
					$paycells = array_reverse($paycells); // display oldest to newest (left to right)

					$cellWidth = round(($this->getPageWidth() - $this->lMargin - $this->rMargin) / 6, 2);

					if (count($paycells) > 0) {
						foreach ($paycells as $pay) {
							$line = date('d-m-y', strtotime($pay['datep'])) . " " . $pay['num_paiement'] . " " . number_format($pay['tot'], 0).';';
							$this->Cell($cellWidth, 5, $line, 0, 0);
						}
						$this->Ln();
						$this->SetTextColor(0,0,0);
					} else {
						$this->SetTextColor(0,0,0);
						$this->Cell(0, 5, "No recent payments", 0, 1);
					}

					$this->Ln(1);
					$this->Line($this->GetX(), $this->GetY(), $this->getPageWidth() - $this->rMargin, $this->GetY());
					$this->Ln(2);
				}

			}

			$this->SetTextColor(0,0,0);
			$this->SetFont('helvetica', 'B', 12);
			$this->Cell(0, 6, "End of report for $town", 0, 1, 'C');
		}
	}
}
