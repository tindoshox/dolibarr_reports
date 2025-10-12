<?php

class ReportModel
{
	public string $reportid;
	public string $displayName;
	public bool $hasState;
	public bool $hasSales;
	public bool $hasStartDate;
	public bool $hasEndDate;
	public bool $stateIsRequired;
	public bool $hasStartPeriod;
	public bool $hasEndPeriod;
	public bool $hasWarehouse;
	public bool $hasStartReceipt;
	public bool $hasEndReceipt;

	public function __construct(array $data = [])
	{
		$this->reportid = $data['reportid'] ?? '';
		$this->displayName = $data['displayName'] ?? '';
		$this->hasState = $data['hasState'] ?? false;
		$this->hasSales = $data['hasSales'] ?? false;
		$this->hasStartDate = $data['hasStartDate'] ?? false;
		$this->hasEndDate = $data['hasEndDate'] ?? false;
		$this->stateIsRequired = $data['groupIsRequired'] ?? false;
		$this->hasStartPeriod = $data['hasStartPeriod'] ?? false;
		$this->hasEndPeriod = $data['hasEndPeriod'] ?? false;
		$this->hasWarehouse = $data['hasWarehouse'] ?? false;
		$this->hasStartReceipt = $data['hasStartReceipt'] ?? false;
		$this->hasEndReceipt = $data['hasEndReceipt'] ?? false;
	}

	public function toJson(): string
	{
		return json_encode($this->toArray());
	}

	public function toArray(): array
	{
		return [
			'reportid' => $this->reportid,
			'displayName' => $this->displayName,
			'hasState' => $this->hasState,
			'hasSales' => $this->hasSales,
			'hasStartDate' => $this->hasStartDate,
			'hasEndDate' => $this->hasEndDate,
			'groupIsRequired' => $this->stateIsRequired,
			'hasStartPeriod' => $this->hasStartPeriod,
			'hasEndPeriod' => $this->hasEndPeriod,
			'hasWarehouse' => $this->hasWarehouse,
			'hasStartReceipt' => $this->hasStartReceipt,
			'hasEndReceipt' => $this->hasEndReceipt,
		];
	}

	public static function fromJson(string $json): ReportModel
	{
		return new self(json_decode($json, true));
	}
}
