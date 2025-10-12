<?php

require_once DOL_DOCUMENT_ROOT.'/includes/tecnickcom/tcpdf/tcpdf.php';

class ReturnsReportPDF extends TCPDF
{

	public $startdate;
	public $enddate;

	public function __construct($orientation = 'L', $unit = 'mm', $format = 'A4')
	{
		parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);

		// Set equal left and right margins
		$this->SetMargins(5, 10, 5);
		$this->SetHeaderMargin(5);

		$this->SetAutoPageBreak(true, 15);
	}

	public function Header()
	{

		$this->SetFont('helvetica', 'B', 12);
		$this->Cell(0, 8, "RETURNS FOR : {$this->startdate} TO {$this->enddate}", 0, 0, 'C');
		$this->Ln(1);
	}

	public function Footer()
	{
		$this->SetY(-15);
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages() . ' | Printed: ' . date('d-m-Y H:i'), 0, 0, 'C');
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
		$this->SetY(15);

		foreach ($data as $inv) {


			// Auto break if needed
			$blockHeight = 2;
			if ($this->GetY() + $blockHeight > ($this->getPageHeight() - $this->getBreakMargin())) {

				$this->AddPage();
				$this->SetY(15);

			}

			// Row 1

			$this->SetFont('helvetica', '', 10);
			$this->Cell(60,6, substr($inv['name'],0,20), 0, 0);
			$this->Cell(30,6, $inv['ref'], 0, 0);
			$this->Cell(30,6, substr($inv['address'],0,10), 0, 0);
			$this->Cell(30,6, substr($inv['town'],0,10), 0, 0);
			$this->Cell(25, 6,date('d-m-Y', strtotime($inv['datef'])), 0, 0);
			$this->Cell(25, 6,date('d-m-Y', strtotime($inv['credit_date'])), 0, 0);
			$this->Cell(25,6, substr($inv['product'],0,10), 0, 0, );
			$this->Cell(15,6, number_format($inv['total_ttc'],0), 0, 0,);
			$this->Cell(15,6, number_format($inv['credit_amount'],0), 0, 0,);
			$this->Cell(30,6, $inv['phone'], 0, 0);
			$this->Ln(6);
		}
	}

}
