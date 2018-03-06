<!DOCTYPE html>
<html>
<head>
<title>Calculating Jewish holidays</title>
</head>
<body>

<?php
include('jewishholidays.php');

if(isSet($_REQUEST["year"])) {
  $prevYear = $_REQUEST["year"]-1;
  $nextYear = $_REQUEST["year"]+1;
  $israeldiaspora = $_REQUEST["israeldiaspora"];
  if (isSet($_REQUEST["postponeshushanpurimonsaturday"])) {
    $postponeShushanPurimOnSaturday = $_REQUEST["postponeshushanpurimonsaturday"];
  } else {
    $postponeShushanPurimOnSaturday = "";
  }
  echo "<p>\n";
  echo "<a href=\"holidays.php?year=$prevYear&israeldiaspora=$israeldiaspora&postponeshushanpurimonsaturday=$postponeShushanPurimOnSaturday\">Previous year</a>";
  echo "| ";
  echo "<a href=\"holidays.php?year=$nextYear&israeldiaspora=$israeldiaspora&postponeshushanpurimonsaturday=$postponeShushanPurimOnSaturday\">Next year</a>";
  echo "</p>\n";
}
?>

<form action="holidays.php" method="get">
Enter Year:   
<input type="text" name="year" value="<?php if (isSet($_REQUEST["year"])) echo $_REQUEST["year"]; ?>"/>
<br/>
<input type="radio" name="israeldiaspora" value="D"<?php if (isSet($_REQUEST["israeldiaspora"]) && $_REQUEST["israeldiaspora"] == "D") echo " checked"; ?>>Diaspora
<input type="radio" name="israeldiaspora" value="I"<?php if (isSet($_REQUEST["israeldiaspora"]) && $_REQUEST["israeldiaspora"] == "I") echo " checked"; ?>>Israel
<br/>
<input type="checkbox" name="postponeshushanpurimonsaturday" value="X"<?php if (isSet($_REQUEST["postponeshushanpurimonsaturday"]) && $_REQUEST["postponeshushanpurimonsaturday"] == "X") echo " checked"; ?>>
Postpone Shushan Purim to Sunday if falling on Saturday
<br/>
<input type="submit" value="Calculate">
</form>

<?php
if (isSet($_REQUEST["year"])) {
  if ($_REQUEST["israeldiaspora"] == "D")
    $isDiaspora = true;
  else
    $isDiaspora = false;
  if (isSet($_REQUEST["postponeshushanpurimonsaturday"]) && $_REQUEST["postponeshushanpurimonsaturday"] == "X")
    $postponeShushanPurimOnSaturday = true;
  else
    $postponeShushanPurimOnSaturday = false;
  echo "<table border>\n";
  echo "<tr><th>Weekday</th><th>Gregorian date</th><th>Jewish date</th><th>Holiday</th></tr>\n";
  $gyear = $_REQUEST["year"];
  $weekdayNames = array("Sunday", "Monday", "Tuesday", "Wednesday",
                        "Thursday", "Friday", "Saturday");
  for ($gmonth = 1; $gmonth <= 12; $gmonth++) {
    $lastGDay = cal_days_in_month(CAL_GREGORIAN, $gmonth, $gyear);
    for ($gday = 1; $gday <= $lastGDay; $gday++) {
      $jdCurrent = gregoriantojd($gmonth, $gday, $gyear);
      $weekdayNo = jddayofweek($jdCurrent, 0);
      $weekdayName = $weekdayNames[$weekdayNo];
      $jewishDate = jdtojewish($jdCurrent);
      list($jewishMonth, $jewishDay, $jewishYear) = explode('/', $jewishDate);
      $jewishMonthName = getJewishMonthName($jewishMonth, $jewishYear);
      $holidays = getJewishHoliday($jdCurrent, $isDiaspora, $postponeShushanPurimOnSaturday);
      if (count($holidays) > 0) {
        echo "<tr><td>$weekdayName</td><td>$gday/$gmonth/$gyear</td><td>$jewishDay $jewishMonthName $jewishYear</td><td>";
        for ($i = 0; $i < count($holidays); $i++) {
          if ($i > 0) echo "/";
          $holiday = $holidays[$i];
          echo "$holiday";
        }
        echo "</td></tr>\n";
      }
    }
  }
  echo "</table>\n";
}
?>

</body>
</html>