<?php
// BLP 2021-10-31 -- This is 'include'ed in holidays.php. This file does NOT run by it self.

echo "<p style='display: none;'>This is jewishholidays.php which is included</p>";

function isJewishLeapYear($year) {
  if ($year % 19 == 0 || $year % 19 == 3 || $year % 19 == 6 ||
      $year % 19 == 8 || $year % 19 == 11 || $year % 19 == 14 ||
      $year % 19 == 17)
    return true;
  else
    return false;
}

function getJewishMonthName($jewishMonth, $jewishYear) {
  $jewishMonthNamesLeap = array("Tishri", "Heshvan", "Kislev", "Tevet",
                                "Shevat", "Adar I", "Adar II", "Nisan",
                                "Iyar", "Sivan", "Tammuz", "Av", "Elul");
  $jewishMonthNamesNonLeap = array("Tishri", "Heshvan", "Kislev", "Tevet",
                                   "Shevat", "", "Adar", "Nisan",
                                   "Iyar", "Sivan", "Tammuz", "Av", "Elul");
  if (isJewishLeapYear($jewishYear))
    return $jewishMonthNamesLeap[$jewishMonth-1];
  else
    return $jewishMonthNamesNonLeap[$jewishMonth-1];
}

function getJewishHoliday($jdCurrent, $isDiaspora, $postponeShushanPurimOnSaturday) {
  $result = array();

  $TISHRI = 1;
  $HESHVAN = 2;
  $KISLEV = 3;
  $TEVET = 4;
  $SHEVAT = 5;
  $ADAR_I = 6;
  $ADAR_II = 7;
  $ADAR = 7;
  $NISAN = 8;
  $IYAR = 9;
  $SIVAN = 10;
  $TAMMUZ = 11;
  $AV = 12;
  $ELUL = 13;

  $SUNDAY = 0;
  $MONDAY = 1;
  $TUESDAY = 2;
  $WEDNESDAY = 3;
  $THURSDAY = 4;
  $FRIDAY = 5;
  $SATURDAY = 6;

  $jewishDate = jdtojewish($jdCurrent);
  list($jewishMonth, $jewishDay, $jewishYear) = explode('/', $jewishDate);

  // Holidays in Elul
  if ($jewishDay == 29 && $jewishMonth == $ELUL)
    $result[] = "Erev Rosh Hashanah";

  // Holidays in Tishri
  if ($jewishDay == 1 && $jewishMonth == $TISHRI)
    $result[] = "Rosh Hashanah I";
  if ($jewishDay == 2 && $jewishMonth == $TISHRI)
    $result[] = "Rosh Hashanah II";
  $jd = jewishtojd($TISHRI, 3, $jewishYear);
  $weekdayNo = jddayofweek($jd, 0);
  if ($weekdayNo == $SATURDAY) { // If the 3 Tishri would fall on Saturday ...
    // ... postpone Tzom Gedaliah to Sunday
    if ($jewishDay == 4 && $jewishMonth == $TISHRI)
      $result[] = "Tzom Gedaliah";
  } else {
    if ($jewishDay == 3 && $jewishMonth == $TISHRI)
      $result[] = "Tzom Gedaliah";
  }
  if ($jewishDay == 9 && $jewishMonth == $TISHRI)
    $result[] = "Erev Yom Kippur";
  if ($jewishDay == 10 && $jewishMonth == $TISHRI)
    $result[] = "Yom Kippur";
  if ($jewishDay == 14 && $jewishMonth == $TISHRI)
    $result[] = "Erev Sukkot";
  if ($jewishDay == 15 && $jewishMonth == $TISHRI)
    $result[] = "Sukkot I";
  if ($jewishDay == 16 && $jewishMonth == $TISHRI && $isDiaspora)
    $result[] = "Sukkot II";
  if ($isDiaspora) {
    if ($jewishDay >= 17 && $jewishDay <= 20 && $jewishMonth == $TISHRI)
      $result[] = "Hol Hamoed Sukkot";
  } else {
    if ($jewishDay >= 16 && $jewishDay <= 20 && $jewishMonth == $TISHRI)
      $result[] = "Hol Hamoed Sukkot";
  }
  if ($jewishDay == 21 && $jewishMonth == $TISHRI)
    $result[] = "Hoshana Rabbah";
  if ($isDiaspora) {
    if ($jewishDay == 22 && $jewishMonth == $TISHRI)
      $result[] = "Shemini Azeret";
    if ($jewishDay == 23 && $jewishMonth == $TISHRI)
      $result[] = "Simchat Torah";
    if ($jewishDay == 24 && $jewishMonth == $TISHRI)
      $result[] = "Isru Chag";
  } else {
    if ($jewishDay == 22 && $jewishMonth == $TISHRI)
      $result[] = "Shemini Azeret/Simchat Torah";
    if ($jewishDay == 23 && $jewishMonth == $TISHRI)
      $result[] = "Isru Chag";
  }

  // Holidays in Kislev/Tevet
  $hanukkahStart = jewishtojd($KISLEV, 25, $jewishYear);
  $hanukkahNo = (int) ($jdCurrent-$hanukkahStart+1);
  if ($hanukkahNo == 1) $result[] = "Hanukkah I";
  if ($hanukkahNo == 2) $result[] = "Hanukkah II";
  if ($hanukkahNo == 3) $result[] = "Hanukkah III";
  if ($hanukkahNo == 4) $result[] = "Hanukkah IV";
  if ($hanukkahNo == 5) $result[] = "Hanukkah V";
  if ($hanukkahNo == 6) $result[] = "Hanukkah VI";
  if ($hanukkahNo == 7) $result[] = "Hanukkah VII";
  if ($hanukkahNo == 8) $result[] = "Hanukkah VIII";

  // Holidays in Tevet
  $jd = jewishtojd($TEVET, 10, $jewishYear);
  $weekdayNo = jddayofweek($jd, 0);
  if ($weekdayNo == $SATURDAY) { // If the 10 Tevet would fall on Saturday ...
    // ... postpone Tzom Tevet to Sunday
    if ($jewishDay == 11 && $jewishMonth == $TEVET)
      $result[] = "Tzom Tevet";
  } else {
    if ($jewishDay == 10 && $jewishMonth == $TEVET)
      $result[] = "Tzom Tevet";
  }

  // Holidays in Shevat
  if ($jewishDay == 15 && $jewishMonth == $SHEVAT)
    $result[] = "Tu B'Shevat";

  // Holidays in Adar I
  if (isJewishLeapYear($jewishYear) && $jewishDay == 14 && $jewishMonth == $ADAR_I)
    $result[] = "Purim Katan";
  if (isJewishLeapYear($jewishYear) && $jewishDay == 15 && $jewishMonth == $ADAR_I)
    $result[] = "Shushan Purim Katan";

  // Holidays in Adar or Adar II
  if (isJewishLeapYear($jewishYear))
    $purimMonth = $ADAR_II;
  else
    $purimMonth = $ADAR;
  $jd = jewishtojd($purimMonth, 13, $jewishYear);
  $weekdayNo = jddayofweek($jd, 0);
  if ($weekdayNo == $SATURDAY) { // If the 13 Adar or Adar II would fall on Saturday ...
    // ... move Ta'anit Esther to the preceding Thursday
    if ($jewishDay == 11 && $jewishMonth == $purimMonth)
      $result[] = "Ta'anith Esther";
  } else {
    if ($jewishDay == 13 && $jewishMonth == $purimMonth)
      $result[] = "Ta'anith Esther";
  }
  if ($jewishDay == 14 && $jewishMonth == $purimMonth)
    $result[] = "Purim";
  if ($postponeShushanPurimOnSaturday) {
    $jd = jewishtojd($purimMonth, 15, $jewishYear);
    $weekdayNo = jddayofweek($jd, 0);
    if ($weekdayNo == $SATURDAY) { // If the 15 Adar or Adar II would fall on Saturday ...
      // ... postpone Shushan Purim to Sunday
      if ($jewishDay == 16 && $jewishMonth == $purimMonth)
        $result[] = "Shushan Purim";
    } else {
      if ($jewishDay == 15 && $jewishMonth == $purimMonth)
        $result[] = "Shushan Purim";
    }
  } else {
    if ($jewishDay == 15 && $jewishMonth == $purimMonth)
      $result[] = "Shushan Purim";
  }

  // Holidays in Nisan
  $shabbatHagadolDay = 14;
  $jd = jewishtojd($NISAN, $shabbatHagadolDay, $jewishYear);
  while (jddayofweek($jd, 0) != $SATURDAY) {
    $jd--;
    $shabbatHagadolDay--;
  }
  if ($jewishDay == $shabbatHagadolDay && $jewishMonth == $NISAN)
    $result[] = "Shabbat Hagadol";
  if ($jewishDay == 14 && $jewishMonth == $NISAN)
    $result[] = "Erev Pesach";
  if ($jewishDay == 15 && $jewishMonth == $NISAN)
    $result[] = "Pesach I";
  if ($jewishDay == 16 && $jewishMonth == $NISAN && $isDiaspora)
    $result[] = "Pesach II";
  if ($isDiaspora) {
    if ($jewishDay >= 17 && $jewishDay <= 20 && $jewishMonth == $NISAN)
      $result[] = "Hol Hamoed Pesach";
  } else {
    if ($jewishDay >= 16 && $jewishDay <= 20 && $jewishMonth == $NISAN)
      $result[] = "Hol Hamoed Pesach";
  }
  if ($jewishDay == 21 && $jewishMonth == $NISAN)
    $result[] = "Pesach VII";
  if ($jewishDay == 22 && $jewishMonth == $NISAN && $isDiaspora)
    $result[] = "Pesach VIII";
  if ($isDiaspora) {
    if ($jewishDay == 23 && $jewishMonth == $NISAN)
      $result[] = "Isru Chag";
  } else {
    if ($jewishDay == 22 && $jewishMonth == $NISAN)
      $result[] = "Isru Chag";
  }

  $jd = jewishtojd($NISAN, 27, $jewishYear);
  $weekdayNo = jddayofweek($jd, 0);
  if ($weekdayNo == $FRIDAY) { // If the 27 Nisan would fall on Friday ...
    // ... then Yom Hashoah falls on Thursday
    if ($jewishDay == 26 && $jewishMonth == $NISAN)
      $result[] = "Yom Hashoah";
  } else {
    if ($jewishYear >= 5757) { // Since 1997 (5757) ...
      if ($weekdayNo == $SUNDAY) { // If the 27 Nisan would fall on Friday ...
        // ... then Yom Hashoah falls on Thursday
        if ($jewishDay == 28 && $jewishMonth == $NISAN)
          $result[] = "Yom Hashoah";
      } else {
        if ($jewishDay == 27 && $jewishMonth == $NISAN)
          $result[] = "Yom Hashoah";
      }
    } else {
      if ($jewishDay == 27 && $jewishMonth == $NISAN)
        $result[] = "Yom Hashoah";
    }
  }

  // Holidays in Iyar

  $jd = jewishtojd($IYAR, 4, $jewishYear);
  $weekdayNo = jddayofweek($jd, 0);

  // If the 4 Iyar would fall on Friday or Thursday ...
  // ... then Yom Hazikaron falls on Wednesday and Yom Ha'Atzmaut on Thursday
  if ($weekdayNo == $FRIDAY) {
    if ($jewishDay == 2 && $jewishMonth == $IYAR)
      $result[] = "Yom Hazikaron";
    if ($jewishDay == 3 && $jewishMonth == $IYAR)
      $result[] = "Yom Ha'Atzmaut";
  } else {
    if ($weekdayNo == $THURSDAY) {
      if ($jewishDay == 3 && $jewishMonth == $IYAR)
        $result[] = "Yom Hazikaron";
      if ($jewishDay == 4 && $jewishMonth == $IYAR)
        $result[] = "Yom Ha'Atzmaut";
    } else {
      if ($jewishYear >= 5764) { // Since 2004 (5764) ...
        if ($weekdayNo == $SUNDAY) { // If the 4 Iyar would fall on Sunday ...
          // ... then Yom Hazicaron falls on Monday
          if ($jewishDay == 5 && $jewishMonth == $IYAR)
            $result[] = "Yom Hazikaron";
          if ($jewishDay == 6 && $jewishMonth == $IYAR)
            $result[] = "Yom Ha'Atzmaut";
        } else {
          if ($jewishDay == 4 && $jewishMonth == $IYAR)
            $result[] = "Yom Hazikaron";
          if ($jewishDay == 5 && $jewishMonth == $IYAR)
            $result[] = "Yom Ha'Atzmaut";
        }
      } else {
        if ($jewishDay == 4 && $jewishMonth == $IYAR)
          $result[] = "Yom Hazikaron";
        if ($jewishDay == 5 && $jewishMonth == $IYAR)
          $result[] = "Yom Ha'Atzmaut";
      }
    }
  }

  if ($jewishDay == 14 && $jewishMonth == $IYAR)
    $result[] = "Pesach Sheini";
  if ($jewishDay == 18 && $jewishMonth == $IYAR)
    $result[] = "Lag B'Omer";
  if ($jewishDay == 28 && $jewishMonth == $IYAR)
    $result[] = "Yom Yerushalayim";

  // Holidays in Sivan
  if ($jewishDay == 5 && $jewishMonth == $SIVAN)
    $result[] = "Erev Shavuot";
  if ($jewishDay == 6 && $jewishMonth == $SIVAN)
    $result[] = "Shavuot I";
  if ($jewishDay == 7 && $jewishMonth == $SIVAN && $isDiaspora)
    $result[] = "Shavuot II";
  if ($isDiaspora) {
    if ($jewishDay == 8 && $jewishMonth == $SIVAN)
      $result[] = "Isru Chag";
  } else {
    if ($jewishDay == 7 && $jewishMonth == $SIVAN)
      $result[] = "Isru Chag";
  }

  // Holidays in Tammuz
  $jd = jewishtojd($TAMMUZ, 17, $jewishYear);
  $weekdayNo = jddayofweek($jd, 0);
  if ($weekdayNo == $SATURDAY) { // If the 17 Tammuz would fall on Saturday ...
    // ... postpone Tzom Tammuz to Sunday
    if ($jewishDay == 18 && $jewishMonth == $TAMMUZ)
      $result[] = "Tzom Tammuz";
  } else {
    if ($jewishDay == 17 && $jewishMonth == $TAMMUZ)
      $result[] = "Tzom Tammuz";
  }
  
  // Holidays in Av
  $jd = jewishtojd($AV, 9, $jewishYear);
  $weekdayNo = jddayofweek($jd, 0);
  if ($weekdayNo == $SATURDAY) { // If the 9 Av would fall on Saturday ...
    // ... postpone Tisha B'Av to Sunday
    if ($jewishDay == 10 && $jewishMonth == $AV)
      $result[] = "Tisha B'Av";
  } else {
    if ($jewishDay == 9 && $jewishMonth == $AV)
      $result[] = "Tisha B'Av";
  }
  if ($jewishDay == 15 && $jewishMonth == $AV)
    $result[] = "Tu B'Av";

  return $result;
}
