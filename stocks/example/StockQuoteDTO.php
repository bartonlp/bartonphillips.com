<?php
readonly class StockQuoteDTO {
  public function __construct(
    public string $symbol,
    public float $c,
    public float $h,
    public float $l,
    public float $pc,
    public float $d,
    public string $pd,
    public int $v,
    public string $t
  ) {}
}
