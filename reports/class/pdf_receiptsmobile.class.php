<?php


require_once DOL_DOCUMENT_ROOT . '/includes/tecnickcom/tcpdf/tcpdf.php';

class ReceiptsMobileReportPDF extends TCPDF
{


	public function Header()

	{


		$this->SetFont('helvetica', 'B', 11);
		$this->Cell(0, 8, "RECEIPTS : {$this->startDate} TO {$this->endDate}", 0, 0, 'L');
		$this->Cell(0, 8, $this->total, 0, 0, 'R');
		$this->Ln(2);
	}

	public function Footer()
	{
		$this->SetY(-15);
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages() . ' | Printed: ' . date('d-m-Y H:i'), 0, 0, 'C');
	}

	public function renderReport($data)
	{if (empty($data)) {
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

			// Auto break if needed
			$blockHeight = 8;
			if ($this->GetY() + $blockHeight > ($this->getPageHeight() - $this->getBreakMargin())) {
				$this->AddPage();
			}

			// Row 1

			$this->SetFont('helvetica', '', 9);
			if ($balance > 0 && $dueDays >= 0) {
				$this->SetTextColor(182, 0, 22);
			} else {
				$this->SetTextColor(0, 0, 0);
			}
			$this->Cell(15, 6, date('d-m-y', strtotime($rec['transaction_date'])), 0, 0);
			$this->Cell(35, 6, substr($rec['name'],0,15), 0, 0);
			$this->Cell(15, 6, substr($rec['town'],0,6), 0, 0);
			$this->Cell(10, 6, number_format($rec['amount'], 0), 0, 0, 'R');
			$this->Cell(12, 6, $rec['user'], 0, 0);
			$this->Cell(10, 6,date( 'd-m H:i',strtotime($rec['capture_date']),), 0, 0);
			$this->Ln(5);
		}
	}
}
