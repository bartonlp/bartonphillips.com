<?php

/**
 * Read only class to store stock information
 */
readonly class StockDataDTO {
  /**
   * Construct
   *
   * @params mixed values
   */  
  public function __construct(
                              public string $symbol,
                              public float $c,
                              public float $h,
                              public float $l,
                              public float $pc,
                              public float $d,
                              public string $pd,
                              public int $v,
                              public string $t,
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

/**
 * Get stock information
 *
 */
class StocksApi {
  private string $userAgent;
  private dbPdo $db;
  private array $stocks;
  private string $alphaKey;
  private string $mydb;
  private string $url;

  /**
   * Constructor
   *
   * @param pdPdo $siteClass
   * @param string $UA default null. The User Agent String to use.
   */
  public function __construct(dbPdo $siteClass, string $UA = null) {
    $this->db = $db = $siteClass;
    $this->mydb = $db->dbinfo->database;

    $this->alphaKey = $alphaKey = require '/var/www/PASSWORDS/alpha-prem-token';
    $this->url = "https://www.alphavantage.co/query?function=:function&symbol=:stock&apikey=$alphaKey";

    $this->userAgent = $UA ?? "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) ".
                         "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36)
";
  }

  /**
   * Get stock information from database
   *
   * @param ?string $statusStr default null
   * @return array of stock information.
   */
  public function getStocks(?string $statusStr = null): array {
    $db = $this->db;
    if($statusStr) $statusStr = " where status='$statusStr'";
    $db->sql("select stock, price, qty, name, status from $this->mydb.stocks{$statusStr}");

    while([$stock, $price, $qty, $name, $status] = $db->fetchrow('num')) {
      $this->stocks[$stock]  = ['price' => $price, 'qty' => $qty, 'name' => $name, 'status' => $status];
    }

    return $this->stocks;
  }

  /**
   * Get Quotes from Alpha Advantage
   *
   * @param string $status default null
   * @return object of stock information
   */
  public function getQuotes(string $status = null): object {
    $result = new stdClass;
    $result->grandTotal = 0;
    $result->stock = new stdClass;

    $stocks = $this->getStocks($status);

    foreach($stocks as $symbol => $val) {
      $urlTmp = str_replace(':stock', $symbol, $this->url);
      $url = str_replace(':function', 'GLOBAL_QUOTE', $urlTmp);

      try {
        $json = file_get_contents($url);
        $data = json_decode($json, true);
        $url = str_replace(':function', 'OVERVIEW', $urlTmp);
        $json = file_get_contents($url);
        $overview = json_decode($json, true);
      } catch(Exception $e) {
        logInfo("StockApi: ERROR, {$e->getCode()}, {$e->getMessage()}");
        continue;
      }

      $price = $data['Global Quote']['05. price'] ?? 0;
      $total = $price * ($val['qty'] ?? 0);

      $stockData = new StockDataDTO(
        symbol: $symbol,
        c: (float)$price,
        h: (float)($data['Global Quote']['03. high'] ?? 0),
        l: (float)($data['Global Quote']['04. low'] ?? 0),
        pc: (float)($data['Global Quote']['08. previous close'] ?? 0),
        d: (float)($data['Global Quote']['09. change'] ?? 0),
        pd: $data['Global Quote']['10. change percent'] ?? "0%",
        v: (int)($data['Global Quote']['06. volume'] ?? 0),
        t: $data['Global Quote']['07. latest trading day'] ?? "",
        qty: (int)$val['qty'],
        purchasePrice: (float)$val['price'],
        name: $val['name'],
        status: $val['status'],
        companyName: $overview['Name'] ?? '',
        dividend: $overview['DividendPerShare'] ?? null,
        divYield: $overview['DividendYield'] ?? null,
        moving50: $overview['50DayMovingAverage'] ?? null,
        moving200: $overview['200DayMovingAverage'] ?? null,
        divDate: $overview['DividendDate'] ?? null,
        divExDate: $overview['ExDividendDate'] ?? null,
        totalValue: $total
      );

      $result->stock->$symbol = $stockData;
      $result->grandTotal += $total;
    }

    return $result;
  }
}
