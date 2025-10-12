<?php

require_once DOL_DOCUMENT_ROOT . '/includes/tecnickcom/tcpdf/tcpdf.php';

class ReceiptBookPDF extends TCPDF
{


	public $startreceipt;
	public $endreceipt;

	public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
	{
		parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);

		// Set equal left and right margins
		$this->SetMargins(5, 10, 5);
		//$this->SetHeaderMargin(5);

		$this->SetAutoPageBreak(true, 20);
	}

	public function Header()
	{

		$this->SetFont('helvetica', 'B', 12);
		$this->Cell(0, 8, "RECEIPT BOOK FROM : {$this->startreceipt} TO {$this->endreceipt}", 0, 0, 'C');
		$this->Ln(4);

	}

	public function Footer()
	{
		$this->SetY(-15);
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages() . ' | Printed: ' . date('d-m-Y H:i'), 0, 0, 'C');
	}

	public function renderReport(array $data)
	{
		// Group by date and collector name
		$rowHeight = 6;
		$grouped = [];
		foreach ($data as $row) {
			$key = date('d M Y', strtotime($row['datep'])) . '|' . strtoupper($row['firstname']);
			$grouped[$key][] = $row;
		}

		$this->AddPage();
		$this->resetColumns();
		$this->setEqualColumns(2, 120);  // 2 columns, 5mm margin between them


		$this->SetFont('helvetica', '', 9);

		foreach ($grouped as $groupKey => $entries) {
			$groupTotal = 0;

			// Parse header
			list($dateStr, $collector) = explode('|', $groupKey);


			$this->SetFont('helvetica', 'B', 10);
			$this->Cell(0, 6, "$dateStr    $collector", 0, 1, 'L');
			$this->Ln(1);
			$this->SetFont('helvetica', '', 9);

			foreach ($entries as $row) {


				$this->Cell(12, $rowHeight, $row['receipt'], 0, 0);
				$this->Cell(22, $rowHeight, $row['ref'], 0, 0);
				$this->Cell(40, $rowHeight, strtoupper(substr($row['name'], 0, 18)), 0, 0);
				$this->Cell(8, $rowHeight, number_format($row['amount']), 0, 0);
				$this->Cell(25, $rowHeight, substr($row['town'], 0, 6), 0, 1);
				$groupTotal += $row['amount'];
			}


			$this->Ln(1);
			$this->SetFont('helvetica', 'B', 9);
			$this->Cell(82, 6, "Total: " . number_format($groupTotal), 0, 1, 'R');
			$this->SetFont('helvetica', '', 9);

			$x = $this->GetX();
			$y = $this->GetY();
			$this->Line($x, $y, $x + 100, $y);

			$this->Ln(4); // Space after group
		}
	}
}
