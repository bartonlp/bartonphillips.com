<?php
readonly class StockOverviewDTO {
  public function __construct(
                              public int $qty,
                              public float $purchasePrice,
                              public string $name,
                              public string $status,
                              public string $companyName,
                              public ?string $dividend,
                              public ?string $divYield,
                              public ?string $moving50,
                              public ?string $moving200,
                              public ?string $divDate,
                              public ?string $divExDate,
                              public float $totalValue
                             ) {}
}
