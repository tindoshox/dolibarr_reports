<?php

require_once DOL_DOCUMENT_ROOT . '/includes/tecnickcom/tcpdf/tcpdf.php';

class StockReportPDF extends TCPDF
{

	public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
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
		$this->Cell(0, 8, "PRODUCTS IN STOCK ", 0, 0, 'C');

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

		foreach ($data as $prod) {


			// Auto break if needed
			$blockHeight = 2;
			if ($this->GetY() + $blockHeight > ($this->getPageHeight() - $this->getBreakMargin())) {

				$this->AddPage();
				$this->SetY(15);

			}

			// Row 1

			$this->SetFont('helvetica', '', 10);
			$this->Cell(40, 6,$prod['warehouse'], 0, 0);
			$this->Cell(65, 6,$prod['product'], 0, 0);
			$this->Cell(20,6, $prod['instock'], 0, 0);

			$this->Ln(6);
		}

	}

}
