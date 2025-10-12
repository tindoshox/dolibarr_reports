<?php

require_once DOL_DOCUMENT_ROOT . '/includes/tecnickcom/tcpdf/tcpdf.php';

class ReceiptsReportPDF extends TCPDF
{

	public string $startDate;
	public string $endDate;

	public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
	{
		parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);

		// Set equal left and right margins
		//$this->SetMargins(5, 10, 5);
		$this->SetHeaderMargin(5);

		$this->SetAutoPageBreak(true, 15);
	}

	public function Header()
	{
		$this->SetFont('helvetica', 'B', 12);
		$this->Cell(0, 8, "RECEIPTS FOR : {$this->startDate} TO {$this->endDate}", 0, 0, 'C');
		$this->Cell(0, 8, "REPORT TOTAL: {$this->total}", 0, 0, 'R');
		$this->Ln(1);
	}

	public function Footer()
	{
		$this->SetY(-15);
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages() . ' | Printed: ' . date('d-m-Y H:i'), 0, 0, 'C');
		$this->Cell(0,10, "Page Total: {$this->subtotal}", 0, 0, 'R');

	}

	public function renderReport($data)

	{
		if (empty($data)) {
			$this->AddPage();
			$this->SetFont('helvetica', 'B', 14);
			$this->SetTextColor(150, 0, 0);
			$this->Cell(0, 10, 'No records found for the selected filters.', 0, 1, 'C');
			return;
		}
		$this->AddPage();


		foreach ($data as $rec) {
			$balance = $rec['balance'];
			$dueDays = $rec['due_days'];
			$this->subtotal += $rec['amount'];

			// Auto break if needed
			$blockHeight = 8;
			if ($this->GetY() + $blockHeight > ($this->getPageHeight() - $this->getBreakMargin())) {

				$this->AddPage();
				$this->subtotal = 0;

			}

			// Row 1

			$this->SetFont('helvetica', '', 10);
			if ($balance>0 && $dueDays>=0){
				$this->SetTextColor(182,0,22);
			} else{
				$this->SetTextColor(0,0,0);
			}
			$this->Cell(25, 6, $rec['transaction_date'], 0, 0);
			$this->Cell(60,6, substr($rec['name'],0,26), 0, 0);
			$this->Cell(30,6, $rec['invoice_num'], 0, 0);
			$this->Cell(40,6, substr($rec['address'],0,14), 0, 0);
			$this->Cell(40,6, $rec['town'], 0, 0);
			$this->Cell(15,6, $rec['rec_num'], 0, 0);
			$this->Cell(20,6, number_format($rec['amount'],0), 0, 0,'R');
			$this->Cell(20,6, $rec['user'], 0, 0, 'R');
			$this->Cell(25,6, $rec['due_date'], 0, 0,'R');
			$this->Ln(5);
		}
	}
}
