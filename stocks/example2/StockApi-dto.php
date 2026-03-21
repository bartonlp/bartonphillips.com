<?php
/**
 * Class to do all of the stock logic
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
 * @property-read string      $lastDay         Date of last trade
 * @property-read string      $lastTradeTime   Time of last trade
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
                              public float $open,
                              public float $high,
                              public float $low,
                              public float $previousClose,
                              public float $change,
                              public string $percentChange,
                              public int $volume,
                              public string $lastDay,
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
   * @param string $UA default null. The User Agent String to use.
   */
  public function __construct(dbPdo $siteClass, string $UA = null) {
    echo "Construct<br>";
    $this->db = $db = $siteClass;
    $this->mydb = $db->dbinfo->database;

    $this->alphaKey = $alphaKey = require '/var/www/PASSWORDS/alpha-prem-token';
    $this->url = "https://www.alphavantage.co/query?function=:function&symbol=:stock&apikey=$alphaKey";

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
   * @return string|object
   */
  public function getQuotes(string $status=null, string $mode='table'): object|string {
    // Set up variables
    
    $result = new stdClass;
    $result->grandTotal = 0;
    $result->buyTotal = 0;
    $result->divTotal = 0;
    $result->stock = new stdClass;
    $rows = '';

    // Get the stock from the database.
    
    $stocks = $this->getStocks($status);

    // Loop through the stock.
    
    foreach($stocks as $symbol => $val) {
      // Replace the space holder ':stock' with the real symbol.
      
      $urlTmp = str_replace(':stock', $symbol, $this->url);

      try {
        $stockQuotes = $this->GetAlphaQuote($urlTmp);

        $overview = $this->GetAlphaOverview($urlTmp);
        //$wjs = $this->GetWsjQuote();
      } catch(Exception $e) {
        // Log the exception and continue.
        
        logInfo("StockApi: ERROR, {$e->getCode()}, {$e->getMessage()}");
        continue;
      }

      // Get the information for the rather wordy Alpha response.
      
      $purchasePrice = $val['price'] ?? 0;
      $qty = $val['qty'] ?? 0;
      $dividend = $overview['DividendPerShare'] ?? 0;

      // Special cases.
      // DIA becomes DJI.
      // CII, RDIV and MBGAF don't have values from Alpha so add the raw amount we get per year.
      // THIS INFO WILL CHANGE from time to time. I should reall figure a different way of doing this.

      extract($stockQuotes); // $price, $high etc.

      switch($symbol) {
        case 'DIA':
          $djiPrice = money_format($price*100);
          $djiHigh = money_format($high*100);
          $djiLow = money_format($low*100);
        
          $dji = (object)['price'=>$djiPrice, 'high'=>$djiHigh, 'low'=>$djiLow];
          break;
        case 'CII':
          $divTotal = $divValue = 857.13;
          $dividend = '';
          $lastDay = '';
          break;
        case 'RDIV':
          $divTotal = $divValue = 981.50;
          $dividend = '';
          $lastDay = '';
          break;
        case 'MBGAF':
          $divTotal  = $divValue = 1462.59;
          $dividend = '';
          $lastDay = '';
          break;
        default:
          // The default if none of the above.
          
          $divTotal = $dividend * $qty;
          $divValue = $dividend * $qty;
          break;
      }

      if($symbol == "DIA") continue;
      
      $total = $price * $qty;
      $buyTotal = $purchasePrice * $qty;

      // Here I am using 'named' arguments. The 'symbol:' is the name of the argument in the
      // StockDataDTO.
      
      $stockData = new StockDataDTO(
symbol: (string)$symbol,
open: (float)$open,                            
price: (float)$price,
high: (float)$high,
low: (float)$low,
previousClose: (float)$previousClose,
change: (float)$change,
percentChange: (string)$percentChange,
volume: (int)$volume,
lastDay: (string)$lastDay,
qty: (int)$qty,
purchasePrice: (float)$purchasePrice,
name: (string)$val['name'],
status: (string)$val['status'],
companyName: (string)$overview['Name'] ?? '',
dividend: (string)$dividend,
divYield: (string)$overview['DividendYield'] ?? null,
moving50: (float)$overview['50DayMovingAverage'] ?? null,
moving200: (float)$overview['200DayMovingAverage'] ?? null,
divDate: (string)$overview['DividendDate'] ?? null,
divExDate: (string)$overview['ExDividendDate'] ?? null,
divValue: (float)$divValue 
      );

      $rows .= $this->createRow($symbol, $stockData) . "\n";

      $result->stock->$symbol = $stockData;
      $result->grandTotal += $total;
      $result->buyTotal += $buyTotal;
      $result->divTotal += $divTotal;
    }
    echo "Done foreach<br>";
    
    $grandTotal = money_format($result->grandTotal);
    $buyTotal = money_format($result->buyTotal);
    $divTotal = money_format($result->divTotal);
    $curBuyDiff = ($result->grandTotal - $result->buyTotal) / $result->buyTotal;
    $curBuyDiff = number_format($curBuyDiff * 100) . "%";
    
    $ftr = "<tfoot>
<tr><th>Totals</th><th></th><th class='buyTotal'>$buyTotal<br>$curBuyDiff</th><th class='grandTotal'>$grandTotal</th>
<th></th><th class='divTotal'>$divTotal</th><th></th></tr>
</tfoot>\n";

    echo "make reslult<br>";
    
    $result = (object)['dji'=>$dji, 'rows'=>$rows, 'footer'=>$ftr, 'data'=>$result];
    echo "return result<br>";
    return $result;
  }

  /**
   * Write the stock total infomtion to the 'stocktotals' table.
   *
   * @param array $info
   * @thows Exception 
   */
  public function putStockTotals(array $info): void {
    $djiPrice = $info[0];
    $total = $info[1];
    $sql = "insert into bartonphillips.stocktotals (dji, total, created)
values('$djiPrice', '$total', current_date())
on duplicate key update dji='$djiPrice', total='$total', created=current_date()";
         
    // This is in the bartonphillips database which is what I am using.
  
    $this->db->sql($sql);
  }

  /**
   * Update the 'stocksmoving' table
   *
   * @thow Exception On SQL error
   */
  public function updateStocksMoving(): void {
    $info = $this->getStocks();
    
    foreach($info as $stock=>$var) {
      $price = $var['price'];
      
      $sql = "insert into bartonphillips.stocksmoving (stock, price, date)
values('$stock', '$price', current_date())
on duplicate key update stock='$stock', price='$price', date=current_date";
      
      $this->db->sql($sql);
    }
  }

  // ****************
  // Private Methods
  // ****************

  /**
   * Get the Alpha Quote info.
   *
   * @param string $urlTmp
   * @return array
   * @throws Exception On file_get_contents error.
   */
  private function GetAlphaQuote(string $urlTmp): array {
    $url = str_replace(':function', 'GLOBAL_QUOTE', $urlTmp);
    $getStocks = $this->getStocks();

    $stocks = [];
    
    foreach($getStocks as $stock=>$val) {
      $url = str_replace(':stock', $stock, $url);
    
      $json = file_get_contents($url);
      $data = json_decode($json, true);

      $ar = [];

      $ar['price'] = $data['Global Quote']['05. price'] ?? 0;
      $ar['high'] = $data['Global Quote']['03. high'] ?? 0;
      $ar['low'] = $data['Global Quote']['04. low'] ?? 0;
      $ar['open'] = $data['Global Quote']['02. open'] ?? 0;
      $ar['previousClose'] = (float)($data['Global Quote']['08. previous close'] ?? 0);
      $ar['change'] = (float)($data['Global Quote']['09. change'] ?? 0);
      $ar['percentChange'] = $data['Global Quote']['10. change percent'] ?? "0%";
      $ar['volume'] = (int)($data['Global Quote']['06. volume'] ?? 0);
      $ar['lastDay'] = $data['Global Quote']['07. latest trading day'] ?? "";
      $stocks[$stock] = $ar;
    }

    return $stocks;
  }

  /**
   * Get quote info from https://api.wsj.net/api/dylan/quotes/v2/comp/quotebydialect
   *
   * @return array
   * @thows Exception On file_get_contents error.
   */
  private function GetWsjQuote(): array {
    $getStocks = $this->getStocks();

    $symbols = array_keys($getStocks);

    $instruments = array_map(
                             fn($s) => "STOCK/US/" .
                                     ($getStocks[$s] === 'COCO' ? 'XNAS' : 'XNYS') .
                                     "/$s",
                             array_keys($getStocks)
                            );

    array_unshift($instruments, 'INDEX/US/DOW JONES GLOBAL/DJIA');

    $encodedIds = urlencode(implode(',', $instruments));

    $options = ['http' => [
                           'method' => 'GET',
                           'header' => [
                                        'Accept: application/json',
                                        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36'
                                       ]]];

    // Create a context

    $context = stream_context_create($options);

    $url = "https://api.wsj.net/api/dylan/quotes/v2/comp/quotebydialect"
           . "?id={$encodedIds}"
           . "&dialect=charting"
           . "&needed=CompositeTrading"
           . "&MaxInstrumentMatches=1"
           . "&ckey=cecc4267a0"
           . "&EntitlementToken=cecc4267a0194af89ca343805a3e57af";

    $json = file_get_contents($url, false, $context);

    
    $data = json_decode($json, true);
    if($data === null) die("json_decode error");
    
    $stocks = [];
    
    for($cnt=0; $cnt < count($data['InstrumentResponses']); ++$cnt) {
      $ar = [];
      
      foreach($data['InstrumentResponses'][$cnt]['Matches'] as $match) {
        $ar['name'] = $match['Instrument']['CommonName'] ?? '(unknown)';
        $symbol = $match['Instrument']['Ticker'] ?? '(none)';
        $ar['price'] = $match['CompositeTrading']['Last']['Price']['Value'] ?? null;
        $ar['open'] = $match['CompositeTrading']['Open']['Value'] ?? 0;
        $ar['high'] = $match['CompositeTrading']['High']['Value'] ?? 0;
        $ar['low'] = $match['CompositeTrading']['Low']['Value'] ?? 0;
        $ar['change'] = $match['CompositeTrading']['NetChange']['Value'] ?? 0;
        $ar['volume'] = $match['CompositeTrading']['Volume'] ?? 0;
        $ar['changepercent'] = $match['CompositeTrading']['ChangePercent'] ?? 0; // Not sure what this is??
        $ar['lastTradTime'] = $match['CompositeTrading']['Last']['Time'] ?? null;
      }

      $stocks[$symbol] = $ar;
    }
    return $stocks;
  }

  /**
   * Get the Alpha Overview info.
   *
   * @param string $urlTmp
   * @return array
   * @thors Exception On file_get_contents error
   */
  private function GetAlphaOverview(string $urlTmp): array {
    $url = str_replace(':function', 'OVERVIEW', $urlTmp);
    $json = file_get_contents($url);
    $data = json_decode($json, true);
    return $data;            
  }
  
  /**
   * Make the <td> line. Private method.
   *
   * @param array $items
   * @return string
   */
  private function makeTd(array $items): string {
    $ret = '';
    
    foreach($items as $name=>$value) {
      if(is_string($value)) {
        $ret .= "<td class='$name'>$value</td>";
      } else {
        $ret .= "<td class='$name'>";
        foreach($value as $key=>$val) {
          $ret .= "$val<br>";
        }

        $ret = preg_replace("~(<br>.*?)<br>$~", "$1", $ret) . "</td>";
      }
    }

    return $ret;
  }

  /**
   * Create a <tr> row, private method
   *
   * @param string $symbol
   * @param stockDataDTO $data
   * @return string
   */
  private function createRow(string $symbol, stockDataDTO $data): string {
    extract((array)$data);

    $diff = number_format(($price - $purchasePrice) / $purchasePrice, 2);
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
