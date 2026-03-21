<?php
require_once "StockQuoteDTO.php";
require_once "StockOverviewDTO.php";

readonly class StockDataDTO {
  public function __construct(
    public StockQuoteDTO $quote,
    public StockOverviewDTO $overview
  ) {}
}
