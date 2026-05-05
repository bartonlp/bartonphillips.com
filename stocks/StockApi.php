<?php
/*
15-minute Delayed US Stock Market Data | Historical Index Data: Enabled
To access 15-minute delayed US stock market data, please append entitlement=delayed to the data request. For example:
https://www.alphavantage.co/query?function=TIME_SERIES_INTRADAY&symbol=IBM&interval=5min&entitlement=delayed&apikey=GD5L5XHCYXSSF4QQ
https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=IBM&entitlement=delayed&apikey=GD5L5XHCYXSSF4QQ
https://www.alphavantage.co/query?function=TIME_SERIES_DAILY_ADJUSTED&symbol=IBM&outputsize=full&entitlement=delayed&apikey=GD5L5XHCYXSSF4QQ

Tip: you can also access 15-minute delayed technical indicators with similar URL configurations. For example:
https://www.alphavantage.co/query?function=SMA&symbol=IBM&interval=5min&time_period=10&series_type=close&entitlement=delayed&apikey=GD5L5XHCYXSSF4QQ
Tip: for historical index data, not extra entitlement parameter is needed. For example:
https://www.alphavantage.co/query?function=INDEX_DATA&symbol=DJI&interval=daily&apikey=GD5L5XHCYXSSF4QQ
*/

/**
 * Format as money with Dollar sign and commas and a period at two decimal places.
 *
 * Small helper function.
 *
 * @param mixed $num
 * @return string formated as money.
 */
function money_format(mixed $num): string {
  return "$". number_format((float)$num, 2);
}

/**
 * Data Transfer Object for holding stock market data.
 *
 * This DTO encapsulates a single snapshot of stock-related information from
 * sources like Alpha Vantage. It is designed for read-only use and can be
 * instantiated using either positional or named arguments.
 *
 * @property-read string      $symbol          The stock ticker symbol (e.g., "AAPL").
 * @property-read float       $price           Current market price.
 * @property-read float       $high            Daily high price.
 * @property-read float       $low             Daily low price.
 * @property-read float       $previousClose   Previous day's closing price.
 * @property-read float       $change          Price change since previous close.
 * @property-read string      $percentChange   Percent change (e.g., "-0.62%").
 * @property-read int         $volume          Volume of shares traded.
 * @property-read string      $time            Timestamp of quote (e.g., ISO8601 or raw string).
 * @property-read int         $qty             Quantity of shares held.
 * @property-read float       $purchasePrice   Purchase price of the stock.
 * @property-read string      $name            Original name from your database record.
 * @property-read string      $status          One of: 'active', 'watch', 'sold', etc.
 * @property-read ?string     $companyName     Optional: Full company name (e.g., "Apple Inc.").
 * @property-read ?string     $dividend        Optional: Dividend per share.
 * @property-read ?string     $divYield        Optional: Dividend yield as a percentage.
 * @property-read ?float      $moving50        Optional: 50-day moving average.
 * @property-read ?float      $moving200       Optional: 200-day moving average.
 * @property-read ?string     $divDate         Optional: Dividend payment date.
 * @property-read ?string     $divExDate       Optional: Ex-dividend date.
 * @property-read float       $divValue        Calculated: total dividend value (qty × dividend).
 *
 * @author  Barton Phillips
 * @license MIT
 */
readonly class StockDataDTO {
  /**
   * Construct a new StockDataDTO.
   *
   * You may use positional or named arguments as supported in PHP 8+.
   */
  public function __construct(
                              public string $symbol,
                              public float $price,
                              public float $high,
                              public float $low,
                              public float $previousClose,
                              public float $change,
                              public string $percentChange,
                              public int $volume,
                              public string $time,
                              public int $qty,
                              public float $purchasePrice,
                              public string $name,
                              public string $status,
                              public ?string $companyName,
                              public ?string $dividend,
                              public ?string $divYield,
                              public ?float $moving50,
                              public ?float $moving200,
                              public ?string $divDate,
                              public ?string $divExDate,
                              public float $divValue
                             ) {}
}

/**
 * API handler for retrieving and formatting stock data.
 *
 * This class interacts with Alpha Vantage and transforms the result into
 * either raw DTOs or HTML tables for frontend use.
 *
 * @var string $userAgent  The user agent string of the client request.
 * @var dbPdo  $db         Database access object (extends PDO).
 * @var array  $stocks     Stock data returned or formatted.
 * @var string $alphaKey   Alpha Vantage API key.
 * @var string $mydb       Name of the MySQL database for lookup/storage.
 * @var string $url        Full endpoint URL for data requests.
 *
 * @author  Barton Phillips
 * @license MIT
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
   * @param string $UA The User Agent String (default: null)
   */
  public function __construct(dbPdo $siteClass, string $UA = null) {
    $this->db = $db = $siteClass;
    $this->mydb = $db->dbinfo->database; // The name of the default database

    // Get the Alpha key from a safe place.
    
    $this->alphaKey = $alphaKey = require '/var/www/PASSWORDS/alpha-prem-token';
    $this->url = "https://www.alphavantage.co/query?function=:function&symbol=:stock&apikey=$alphaKey";

    // Use the $UA arg or the default.
    
    $this->userAgent = $UA ?? "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) ".
                       "AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36)";
  }

  /**
   * Get stock information from database
   *
   * I have a table in my 'bartonphillips' database called 'stocks'.
   * Because the 'mysitmap.json' has the database named I really don't need to specify it in my 'select'.
   *
   * @param ?string $statusStr Optional status string (default: null)
   * @return array Stock information
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
   * Get the stock information from Alpha Advantage
   *
   * @param string $status Optional status (default: null)
   * @param string $mode Optional mode (default: null)
   *   `$mode` can be strings, 'table' or 'raw'. If 'table' then a fully formed table is retuned.
   *   If 'raw' then an object is returned with all of the stock information.
   * @return object
   */
  public function getQuotes(string $status=null, string $mode='table'): object {
    // Set up variables
    
    $result = new stdClass;
    $result->grandTotal = 0;
    $result->buyTotal = 0;
    $result->divTotal = 0;
    $result->stock = new stdClass;
    $rows = '';

    $urlTmp = str_replace(':stock', 'DIA', $this->url);
    $url = str_replace(':function', 'GLOBAL_QUOTE', $urlTmp);

    $json = file_get_contents($url);

    $data = json_decode($json, true);
    error_log("data: " . print_r($data, true));
    $price = $data['Global Quote']['05. price'] ?? 0;
    $high = $data['Global Quote']['03. high'] ?? 0;
    $low = $data['Global Quote']['04. low'] ?? 0;

    $price = money_format($price*100);
    $high = money_format($high*100);
    $low = money_format($low*100);

    $dji = (object)['price'=>$price, 'high'=>$high, 'low'=>$low];

    // Get the stock from the database.
    
    $stocks = $this->getStocks($status);

    // Loop through the stock.
    
    foreach($stocks as $symbol => $val) {
      // Replace the space holder ':stock' with the real symbol.
      
      $urlTmp = str_replace(':stock', $symbol, $this->url);
      
      try {
        // Replace the space holder ':function' with the real value.
        
        $url = str_replace(':function', 'GLOBAL_QUOTE', $urlTmp);
        $json = file_get_contents($url);
        $data = json_decode($json, true);

        $url = str_replace(':function', 'OVERVIEW', $urlTmp);
        $json = file_get_contents($url);
        $overview = json_decode($json, true);
      } catch(Exception $e) {
        // Log the exception and continue.
        
        logInfo("StockApi: ERROR, {$e->getCode()}, {$e->getMessage()}");
        continue;
      }

      // Get the information for the rather wordy Alpha response.
      
      $price = $data['Global Quote']['05. price'] ?? 0;
      $high = $data['Global Quote']['03. high'] ?? 0;
      $low = $data['Global Quote']['04. low'] ?? 0;
      $purchasePrice = $val['price'] ?? 0;
      $qty = $val['qty'] ?? 0;
      $dividend = $overview['DividendPerShare'] ?? 0;

      // Special cases.
      // DIA becomes DJI.
      // CII, RDIV and MBGAF don't have values from Alpha so add the raw amount we get per year.
      // THIS INFO WILL CHANGE from time to time. I should reall figure a different way of doing this.
      
      switch($symbol) {
        case 'CII':
          $divTotal = $divValue = 857.13;
          $dividend = '';
          break;
        case 'RDIV':
          $divTotal = $divValue = 981.50;
          $dividend = '';
          break;
        case 'MBGAF':
          $divTotal  = $divValue = 1462.59;
          $dividend = '';
          break;
        default:
          // The default if none of the above.
          
          $divTotal = $dividend * $qty;
          $divValue = $dividend * $qty;
          break;
      }

      //if($symbol == "DIA") continue;
      
      //echo "$symbol, divTotal=$divTotal<br>";
      
      $total = $price * $qty;
      $buyTotal = $purchasePrice * $qty;

      // Here I am using 'named' arguments. The 'symbol:' is the name of the argument in the
      // StockDataDTO.
      
      $stockData = new StockDataDTO(
        symbol: $symbol,
        price: (float)$price,
        high: (float)$high,
        low: (float)$low,
        previousClose: (float)($data['Global Quote']['08. previous close'] ?? 0),
        change: (float)($data['Global Quote']['09. change'] ?? 0),
        percentChange: $data['Global Quote']['10. change percent'] ?? "0%",
        volume: (int)($data['Global Quote']['06. volume'] ?? 0),
        time: $data['Global Quote']['07. latest trading day'] ?? "",
        qty: (int)$qty,
        purchasePrice: (float)$purchasePrice,
        name: $val['name'],
        status: $val['status'],
        companyName: $overview['Name'] ?? '',
        dividend: $dividend,
        divYield: $overview['DividendYield'] ?? null,
        moving50: $overview['50DayMovingAverage'] ?? null,
        moving200: $overview['200DayMovingAverage'] ?? null,
        divDate: $overview['DividendDate'] ?? null,
        divExDate: $overview['ExDividendDate'] ?? null,
        divValue: $divValue 
      );

      // Use the private createRow method to make a full <tr> row.
      
      $rows .= $this->createRow($symbol, $stockData) . "\n";

      $result->stock->$symbol = $stockData;
      $result->grandTotal += $total;
      $result->buyTotal += $buyTotal;
      $result->divTotal += $divTotal;
    }

    // Format infomation for the footer row.
    
    $grandTotal = money_format($result->grandTotal);
    $buyTotal = money_format($result->buyTotal);
    $divTotal = money_format($result->divTotal);
    $curBuyDiff = ($result->grandTotal - $result->buyTotal) / $result->buyTotal;
    $curBuyDiff = number_format($curBuyDiff * 100) . "%";
    //echo "divTotal=$divTotal<br>";
    
    $ftr = "<tfoot>
<tr><th>Totals</th><th></th><th class='buyTotal'>$buyTotal<br>$curBuyDiff</th><th class='grandTotal'>$grandTotal</th>
<th></th><th class='divTotal'>$divTotal</th><th></th></tr>
</tfoot>\n";

    // dji has the Dow Jones Industrial 'current', 'high' and 'low'
    // rows has all of the full <tr>rows as a string
    // footer has the <tfoot> as a string
    // data has the full data set before adding the other elements.
    
    $result = (object)['dji'=>$dji, 'rows'=>$rows, 'footer'=>$ftr, 'data'=>$result];

    // $result is now an object of objects.
    
    return $result;
  }

  /**
   * Write the Stock Totals to the 'stocktotals' database.
   *
   * @param array $info The current DJI price and the current grand total of my assets
   * @return bool Always true.
   * @throw Exception If a SQL error.
   */
  public function putStockTotals(array $info): bool {
    $djiPrice = $info[0];
    $total = $info[1];
    $sql = "insert into bartonphillips.stocktotals (dji, total, created)
values('$djiPrice', '$total', current_date())
on duplicate key update dji='$djiPrice', total='$total', created=current_date()";
         
    // This is in the bartonphillips database which is what I am using.
  
    $this->db->sql($sql); // Can throw an Exception if an error.

    return true;
  }

  /**
   * Update the 'stocksmoving' table
   *
   * This writes all of the Stocks and stocks value to the 'stocksmoving' table.
   *
   * @thow Exception On SQL error
   */
  public function updateStocksMoving(): void {
    $info = $this->getStocks();
    
    foreach($info as $stock=>$var) {
      $price = $var['price'];
      //echo "Stock=$stock, var->price=$price<br>";

      $sql = "insert into bartonphillips.stocksmoving (stock, price, date)
values('$stock', '$price', current_date())
on duplicate key update stock='$stock', price='$price', date=current_date";
      
      $this->db->sql($sql);
    }
  }

  /**
   * Make the <td> line.
   *
   * This is a private helper function that take an array,
   * ['stock'=>['stock'=>$symbol, 'name'=>$company],
      'priceinfo'=>['price'=>$price, 'high'=>$high, 'low'=>$low],
      'buyprice'=>['buy'=>$purchasePrice, 'diff'=>$diff],
      'qtyval'=>['qty'=>$qty, 'val'=>$val],
      'changeinfo'=>['change'=>$change, 'perecent'=>$percentChange],
      'dividend'=>['div'=>$dividend, 'divVal'=>$divValue],
      'moving'=>['moving50'=>$moving50, 'moving200'=>$moving200]]
   * and makes a <td> string.
   *
   * @param array $items A name=>value array.
   * @return string
   */
  private function makeTd(array $items): string {
    $ret = '';

    // Decompose the array. Use the $name for the class and the $value as the 'tds' value.
    
    foreach($items as $name=>$value) {
      // The $value can be a single string or an array.
      
      if(is_string($value)) {
        $ret .= "<td class='$name'>$value</td>";
      } else {
        // If this is an array then decompose the array.
        // Use the original $name for the class.
        // Get each $val and add it to the $ret plus a trailing '<br>'.
        // This makes a 'td' with multiple items.
        $ret .= "<td class='$name'>";
        foreach($value as $val) {
          $ret .= "$val<br>";
        }

        // Finally remove the trailing <br>.
        
        $ret = preg_replace("~(<br>.*?)<br>$~", "$1", $ret) . "</td>";
      }
    }

    // Return the final <td ...
    
    return $ret;
  }

  /**
   * Create a <tr> row
   *
   * A private helper method to create a <tr> row with the stock name and all of the
   * stock data.
   *
   * @param string $symbol The stock symbol
   * @param stockDataDTO $data The stockDataDTO with all the stock information.
   * @return string The full <tr> row as a string.
   */
  private function createRow(string $symbol, stockDataDTO $data): string {
    extract((array)$data); // Extract all of the array info into named variables.

    // Now add the dollar signs and augmentations.
    
    $diff = number_format(($price - $purchasePrice) / $purchasePrice * 100, 2);
    $diff = $diff >= 0 ? "$diff%" : "<span class='red'>$diff%</span>";

    $val = money_format($price * $qty);

    $divValue = money_format($divValue);
    
    $price = money_format($price);
    $high = money_format($high);
    $low = money_format($low);
    $qty = number_format($qty, 2);
    $purchasePrice = money_format($purchasePrice);
    $change = money_format($change);
    
    $change = str_contains($change, '-') ? "<span class='red'>$change</span>" : $change;
    $percentChange = str_contains($percentChange, '-') ? "<span class='red'>$percentChange</span>" : $percentChange;

    $dividend = $dividend ? money_format($dividend) : '';
    $moving50 = money_format($moving50);
    $moving200 = money_format($moving200);

    $company = $companyName ? $companyName : $name;
    $company = "<span class='coname'>$company</span>";
    $symbol = "<span class='stocksymbol'>$symbol</span>";
    $high = "h: $high";
    $low = "l: $low";

    // Use the makeTd method to put the <td> together.
    // Some items could be a single string,
    // while others are arrays (here all are arrays).
    
    $row = "<tr class='$status'>" . $this->makeTd(['stock'=>['stock'=>$symbol, 'name'=>$company],
      'priceinfo'=>['price'=>$price, 'high'=>$high, 'low'=>$low],
      'buyprice'=>['buy'=>$purchasePrice, 'diff'=>$diff],
      'qtyval'=>['qty'=>$qty, 'val'=>$val],
      'changeinfo'=>['change'=>$change, 'perecent'=>$percentChange],
      'dividend'=>['div'=>$dividend, 'divVal'=>$divValue],
      'moving'=>['moving50'=>$moving50, 'moving200'=>$moving200]]) . "</tr>";

    return $row;
  }
}
