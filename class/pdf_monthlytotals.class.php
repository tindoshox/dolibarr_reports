<?php

require_once DOL_DOCUMENT_ROOT . '/includes/tecnickcom/tcpdf/tcpdf.php';

class MonthlyTotalsReportPDF extends TCPDF
{
	public $reportTitle = '';
	public $dates = [];
	public $states = [];
	public $data = [];

	public function Header()
	{
		$this->SetFont('helvetica', 'B', 12);
		$this->Cell(0, 10, $this->reportTitle, 0, 1, 'C');
		$this->Ln(2);
	}

	public function Footer()
	{
		$this->SetY(-15);
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages() . ' | Printed: ' . date('d-m-Y H:i'), 0, 0, 'C');
	}

	public function renderCrosstab()
	{
		if (empty($this->data)) {
			$this->AddPage();
			$this->SetFont('helvetica', 'B', 14);
			$this->SetTextColor(150, 0, 0);
			$this->Cell(0, 10, 'No records found for the selected filters.', 0, 1, 'C');
			return;
		}
		$cellWidth = 30;
		$rowLabelWidth = 38;
		$totalsColumnWidth = 32;

		// Header Row
		$this->SetFont('helvetica', 'B', 12);
		$this->SetFillColor(230, 240, 255); // light blue
		$this->SetTextColor(0);

		$this->Cell($rowLabelWidth, 7, 'Group/Date', 1, 0, 'C', true);
		foreach ($this->dates as $date) {
			$this->Cell($cellWidth, 7,date('M-Y', strtotime($date)), 1, 0, 'C', true);
		}

		$this->SetFillColor(0, 95, 179); // medium blue
		$this->SetTextColor(255); // white
		$this->Cell($totalsColumnWidth, 7, 'Total', 1, 1, 'C', true);

		// Data Rows
		$this->SetFont('helvetica', '', 10);
		$this->SetTextColor(0);
		foreach ($this->states as $state) {
			$this->SetFillColor(230, 240, 255);
			$this->Cell($rowLabelWidth, 10, $state , 1, 0, 'C', true);

			$rowTotal = 0;
			foreach ($this->dates as $date) {
				$amount = $this->getAmount($date, $state);
				$this->Cell($cellWidth, 10, $amount > 0 ? number_format($amount) : '-', 1, 0, 'C');
				$rowTotal += $amount;
			}

			$this->SetFillColor(0, 95, 179);
			$this->SetTextColor(255);
			$this->Cell($totalsColumnWidth, 10, number_format($rowTotal), 1, 1, 'C', true);
			$this->SetTextColor(0);
		}

		// Totals Row
		$this->SetFont('helvetica', 'B', 11);
		$this->SetFillColor(0, 95, 179);
		$this->SetTextColor(255);
		$this->Cell($rowLabelWidth, 10, 'Total', 1, 0, 'C', true);

		$grandTotal = 0;
		foreach ($this->dates as $date) {
			$colTotal = 0;
			foreach ($this->states as $state) {
				$colTotal += $this->getAmount($date, $state);
			}
			$grandTotal += $colTotal;
			$this->Cell($cellWidth, 10, number_format($colTotal), 1, 0, 'C', true);
		}
		$this->Cell($totalsColumnWidth, 10, number_format($grandTotal), 1, 1, 'C', true);

		// Reset colors just in case
		$this->SetTextColor(0);
	}

	private function getAmount($date, $state)
	{
		foreach ($this->data as $row) {
			if ($row['date'] === $date && $row['state'] === $state) {
				return (int)$row['amount'];
			}
		}
		return 0;
	}
}
