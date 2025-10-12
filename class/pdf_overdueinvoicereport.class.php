<?php
require_once DOL_DOCUMENT_ROOT . '/includes/tecnickcom/tcpdf/tcpdf.php';


class OverdueInvoiceReportPDF extends TCPDF
{
	public $provinceName = '';
	public $startDate = '';
	public $endDate = '';

	public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
	{
		parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);

		// Set equal left and right margins
		$this->SetMargins(15, 10, 15);
		$this->SetHeaderMargin(5);
		$this->SetAutoPageBreak(true, 15);
	}

	public function Header()
	{
		$this->SetFont('helvetica', 'B', 11);
		$this->Cell(0, 8, "{$this->provinceName} - OVERDUE FOR DUE DATES: {$this->startDate} TO {$this->endDate}", 0, 1, 'C');
		$this->Ln(2);
	}

	public function Footer()
	{
		$this->SetY(-15);
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . ' / ' . $this->getAliasNbPages() . ' | Printed: ' . date('d-m-Y H:i'), 0, 0, 'C');
	}

	public function renderReport($data, $payments = [], $agenda = [])
	{
		if (empty($data)) {
			$this->AddPage();
			$this->SetFont('helvetica', 'B', 14);
			$this->SetTextColor(150, 0, 0);
			$this->Cell(0, 10, 'No records found for the selected filters.', 0, 1, 'C');
			return;
		}
		$this->AddPage();

		foreach ($data as $inv) {
			// Auto break if needed
			$blockHeight = 14;
			if ($this->GetY() + $blockHeight > ($this->getPageHeight() - $this->getBreakMargin())) {
				$this->AddPage();
			}

			$this->SetFont('helvetica', '', 9);
			$this->SetTextColor(0, 0, 0);

			// ROW 1
			$phone = $inv['phone'] ?: ($inv['fax'] ?: '');
			$lastPay = $payments[$inv['rowid']][0] ?? null;
			$lastPayStr = $lastPay ? "LAST: " . number_format($lastPay['tot']) . " " . date('d-m-Y', strtotime($lastPay['datep'])) : '';

			$this->SetFont('helvetica', '', 9);
			$this->SetTextColor(0, 0, 0);

			$this->Cell(60, 6, substr($inv['nom'], 0, 35), 0, 0);             // Customer name
			$this->Cell(35, 6, $inv['town'], 0, 0);                           // City
			$this->Cell(30, 6, $phone, 0, 0);                                 // Phone or fax
			$this->Cell(30, 6, $inv['due_date'] ? date('d-m-Y', strtotime($inv['due_date'])) : '', 0, 0); // Due date
			$this->Cell(0, 6, $lastPayStr, 0, 1, 'R');                        // Last payment right-aligned

// ROW 2
			$agendaText = $agenda[$inv['rowid']] ?? '';
			$this->SetFont('helvetica', 'I', 8);

			$this->Cell(60, 5, "{$inv['invoice_num']} - " . date('d-m-Y', strtotime($inv['invoice_date'])), 0, 0); // Invoice + date (no label)
			$this->Cell(35, 5, $inv['address'], 0, 0);                        // Address
			$this->Cell(30, 5, substr($inv['prod'], 0, 12), 0, 0);            // Product
			$this->Cell(30, 5, number_format($inv['balance'], 2), 0, 0);      // Balance (aligned left, not right)
			$this->Cell(0, 5, $agendaText, 0, 1, 'R');                 // Agenda (right)

			// Spacer and line
			$this->Ln(1);
			$this->Line($this->lMargin, $this->GetY(), $this->getPageWidth() - $this->rMargin, $this->GetY());
			$this->Ln(2);
		}
	}

}
