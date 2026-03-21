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

    try {
      $stockQuotes = $this->GetAlphaQuote();
      $overview = $this->GetAlphaOverview();
      $wjs = $this->GetWsjQuote();
      //vardump("wjs", $wjs);
      $tmp = $wjs['DJIA'];
      $dji = (object)[
                      'high'=>money_format($tmp['high']),
                      'low'=>money_format($tmp['low']),
                      'price'=>money_format($tmp['price']),
                      'change'=>money_format($tmp['change']),
                      'dateTime'=>$tmp['lastTradTime'],
                     ];
      unset($wjs['DJIA']);
    } catch(Exception $e) {
      // Log the exception and continue.
        
      logInfo("StockApi: ERROR, {$e->getCode()}, {$e->getMessage()}");
      echo "StockApi: ERROR, {$e->getCode()}, {$e->getMessage()}<br>";
      exit;
    }

    $stockData = [];

    foreach(array_keys($stockQuotes) as $symbol) {
      /*$stockData[$symbol] = array_merge(
                                        $stockQuotes[$symbol] ?? [],
                                        $overview[$symbol] ?? [],
                                        $wjs[$symbol] ?? []
                                       );
                                       */
      if($wjs[$symbol] === null) {
        $stockData[$symbol] = $stockQuotes[$symbol] + $overview[$symbol];
      } else {
        $stockData[$symbol] = $stockQuotes[$symbol] + $overview[$symbol] + $wjs[$symbol];
      }
      
      $total += $overview[$symbol]['buyTotal'];
      $divTotal += $overview[$symbol]['divTotal'];
    }
    //vardump("stockData", $stockData);

    $rows .= $this->createRows($stockData) . "\n";

    $result->stock->$symbol = $stockData;
    $result->grandTotal = $total;
    $result->buyTotal = $total;
    $result->divTotal = $divTotal;

    $grandTotal = money_format($result->grandTotal);
    $buyTotal = money_format($result->buyTotal);
    $divTotal = money_format($result->divTotal);
//    $curBuyDiff = ($result->grandTotal - $result->buyTotal) / $result->buyTotal;
//    $curBuyDiff = number_format($curBuyDiff * 100) . "%";
    
    $ftr = "<tfoot>
<tr><th>Totals</th><th></th><th class='buyTotal'>$buyTotal<br>$curBuyDiff</th><th class='grandTotal'>$grandTotal</th>
<th></th><th class='divTotal'>$divTotal</th><th></th></tr>
</tfoot>\n";

    $result = (object)['dji'=>$dji, 'rows'=>$rows, 'footer'=>$ftr, 'data'=>$result];
    vardump("result", $result);
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
  private function GetAlphaQuote(): array {
    $getStocks = $this->getStocks();

    $stocks = [];

    $urlTmp = str_replace(':function', 'GLOBAL_QUOTE', $this->url);
    
    foreach($getStocks as $stock=>$val) {
      $urlTmp = str_replace(':stock', $stock, $urlTmp);
    
      $json = file_get_contents($urlTmp);
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
      $ar['purchasePrice'] = $val['price'];
      $ar['qty'] = $val['qty'] ?? 0;
      $ar['buyTotal'] = $val['price'] * $val['qty'];
      $ar['status'] = $val['status'];
      
      $stocks[$stock] = $ar;
    }
    unset($stocks['DIA']);
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
        $ar['compVolume'] = $match['CompositeTrading']['Volume'] ?? 0;
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
  private function GetAlphaOverview(): array {
    $getStocks = $this->getStocks();

    $stocks = [];

    $urlTmp = str_replace(':function', 'OVERVIEW', $this->url);

    foreach($getStocks as $stock=>$val) {
      $ar = [];
      $url = str_replace(':stock', $stock, $urlTmp);
    
      $json = file_get_contents($url);
      $data = json_decode($json, true);
      $ar['dividend'] = $div = $data['DividendPerShare'] ?? 0;
      $ar['divTotal'] = $div * $val['qty'];
      $ar['divValue'] = $div * $val['qty'];
      $ar['moving50'] = $data['50DayMovingAverage'] ?? 0;
      $ar['moving200'] = $data['200DayMovingAverage'] ?? 0;
      $ar['divDate'] = $data['DividendDate'] ?? '';
      $ar['divExDate'] = $data['ExDividendDate'] ?? '';
      
      $stocks[$stock] = $ar;
    }
    return $stocks;
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
      if(!is_array($value)) {
        echo "String: name=$name, value=$value<br>";
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
   * Create all of the <tr> rows. Private method
   *
   * @param string $symbol
   * @param array $data The composit of StockData and overview.
   * @return string
   */
  private function createRows(array $data): string {
    foreach($data as $symbol=>$val) {
      //vardump("val", $val);

      if($symbol == "DIA") continue;
      
      extract($val);
      
      if($purchasePrice == 0) {
        //echo "$symbol, purchasePrice=$purchasePrice<br>";
        $diff = 0;
      } else {
        //echo "$symbol, purchasePrice=$purchasePrice<br>";
        $diff = number_format(($price - $purchasePrice) / $purchasePrice, 2);
      }
    
      $diff = $diff >= 0 ? "$diff%" : "<span class='red'>$diff%</span>";

      $divValue = money_format($divValue);

      $val = money_format($price * $qty); // before we do money_format()

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

      $row = "<tr class='$status'>" .
             $this->makeTd(['stock'=>['stock'=>$symbol, 'name'=>$company],
                            'priceinfo'=>['price'=>$price, 'high'=>$high, 'low'=>$low],
                            'buyprice'=>['buy'=>$purchasePrice, 'diff'=>$diff],
                            'qtyval'=>['qty'=>$qty, 'val'=>$val],
                            'changeinfo'=>['change'=>$change, 'perecent'=>$percentChange],
                            'dividend'=>['div'=>$dividend, 'divVal'=>$divValue],
                            'moving'=>['moving50'=>$moving50, 'moving200'=>$moving200],
                            'volume'=>$volume]) . "</tr>";

      $rows .= "$row\n";
    }
    return $rows;
  }
}

/*      switch($symbol) {
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
*/
      
