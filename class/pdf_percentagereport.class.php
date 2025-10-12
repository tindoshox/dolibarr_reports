<?php
require_once DOL_DOCUMENT_ROOT . '/includes/tecnickcom/tcpdf/tcpdf.php';

class PercentageReportPDF extends TCPDF
{
	public $title = 'CUSTOMER PAYMENT PERFORMANCE';
	public $data = [];
	public $conf;
	public $langs;

	public function __construct($orientation = 'L', $unit = 'mm', $format = 'A4')
	{
		parent::__construct($orientation, $unit, $format, true, 'UTF-8', false);

		// Set equal left and right margins

		$this->SetMargins(15, 10, 15);
		$this->SetHeaderMargin(5);
		$this->SetAutoPageBreak(true, 15);
	}


	public function Header()
	{
		$this->SetFont('helvetica', 'B', 12);
		$this->Cell(0, 10, $this->title, 0, 1, 'C');
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

		if (empty($data)) {
			$this->AddPage();
			$this->SetFont('helvetica', 'B', 14);
			$this->SetTextColor(150, 0, 0);
			$this->Cell(0, 10, 'No records found for the selected filters.', 0, 1, 'C');
			return;
		}


		$this->AddPage();
		$this->SetFont('helvetica', '', 10);
		$contentWidth = $this->getPageWidth() - $this->getMargins()['left'] - $this->getMargins()['right'];
		$columns = ['GROUP', 'AMOUNT', 'AVE', 'PAID', 'UNPAID', 'TOTAL', 'SALES', 'FINISHED', 'RETURN', '%PAID', 'LOST'];
		$stateWidth = 40;
		$colWidth = ($contentWidth - $stateWidth) / count($columns) - 1;

		$widths = [$stateWidth, $colWidth];
$rowHeight = 10;
// Table Header
		$this->SetFillColor(230, 240, 255);
		$this->SetFont('', 'B');
		foreach ($columns as $i => $col) {
			$this->Cell($i == 0 ? $widths[$i]:$widths[1], $rowHeight, $col, 0 , 0, 'C', true);
		}
		$this->Ln(10);

		$this->SetFont('', '');
		$totals = [
			'total' => 0,
			'paid' => 0,
			'open' => 0,
			'finished' => 0,
			'sales' => 0,
			'returns' => 0,
			'amount' => 0,
			'lost' => 0,
			'unpaid' => 0
		];

		foreach ($data as $row) {
			$state = strtoupper($row['state']);
			$amount = (float)$row['amount'];
			$paid = (int)$row['paid'];
			$open = (int)$row['opentiems'];
			$finished = (int)$row['finished'];
			$sales = (int)$row['sales'];
			$returns = (int)$row['returns'];
			$total = $open + $finished + $returns;
			$unpaid = $total - $paid;
			$ave = $paid > 0 ? $amount / $paid : 0;
			$percent_paid = $total > 0 ? ($paid / $total * 100) : 0;
			$lost = $open * $ave;

			$totals['total'] += $total;
			$totals['paid'] += $paid;
			$totals['open'] += $open;
			$totals['finished'] += $finished;
			$totals['sales'] += $sales;
			$totals['returns'] += $returns;
			$totals['amount'] += $amount;
			$totals['lost'] += $lost;
			$totals['unpaid'] += $unpaid;



			$values = [
				$state,
				'R' . number_format($amount),
				'R' . number_format($ave, 0),
				$paid,
				$unpaid,
				$total,
				$sales,
				$finished,
				$returns,
				number_format($percent_paid, 2) . '%',
				'R' . number_format($lost > 0 ? $lost : ($totals['paid'] > 0 ? $unpaid * $totals['amount'] / $totals['paid'] : 0), 0),
			];
			$fill = false;
			foreach ($values as $i => $val) {
				$this->SetFillColor(245, 245, 245); // very light gray
				$this->Cell($i == 0 ? $widths[$i] : $widths[1], $rowHeight, $val, 0 , 0,  'C', $fill);
				$fill = !$fill;
			}
			$this->Ln(10);
		}

// Totals row
		$this->SetFont('', 'B');
		$this->SetFillColor(200, 220, 255);
		$this->Cell($widths[0], $rowHeight, 'TOTAL', 0, 0, 'C', true);
		$this->Cell($widths[1], $rowHeight,'R'. number_format($totals['amount']), 0, 0, 'C', true);
		$tot_ave = $totals['paid'] > 0 ? $totals['amount'] / $totals['paid'] : 0;
		$this->Cell($widths[1], $rowHeight,'R'. number_format($tot_ave, 0), 0, 0, 'C', true);
		$this->Cell($widths[1], $rowHeight, $totals['paid'], 0, 0, 'C', true);
		$this->Cell($widths[1], $rowHeight, $totals['unpaid'], 0, 0, 'C', true);
		$this->Cell($widths[1], $rowHeight, $totals['total'], 0, 0, 'C', true);
		$this->Cell($widths[1], $rowHeight, $totals['sales'], 0, 0, 'C', true);
		$this->Cell($widths[1], $rowHeight, $totals['finished'], 0, 0, 'C', true);
		$this->Cell($widths[1], $rowHeight, $totals['returns'], 0, 0, 'C', true);
		$tot_percent = $totals['total'] > 0 ? ($totals['paid'] / $totals['total'] * 100) : 0;
		$this->Cell($widths[1], $rowHeight, number_format($tot_percent, 1) . '%', 0, 0, 'C', true);
		$this->Cell($widths[1], $rowHeight,'R'. number_format($totals['lost'], 0), 0, 0, 'C', true);
	}
}
