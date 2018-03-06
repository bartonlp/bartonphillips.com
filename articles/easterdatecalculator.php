<?php
/**
 * easterdatecalculator
 * This class will output calculate the date on which easter will fall for a given year.
 *
 * Other Christian Hollydays:
 * Carnaval      (Mardi Grass)   -49 days from Easter
 * Aswoensdag    (Ash Wednesday) -46 days from Easter
 * Goede vrijdag (Good Friday)    -2 days from Easter
 * Hemelvaart    (Assention)     +39 days from Easter
 * Pinksteren    (Pentecost)     +49 days from Easter
 *
 * Copyright (C) 2010 by Marcel Bachus
 *
 * Modified by Barton Phillips to use PHP easter_days function which does not have the unix time stamp
 * limitations of 1970 to 2038. This class is now acurate from 1800-as long as Easter will be celibrated.
 * For some reason dates before 1800 seem to switch to Julian Easter dates (I have no idea why).
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *_________________________________________________________________________
 *
 * Updates:
 * version 1.0 - First release of the class, for more explanations please
 *               see the example1.php
 *
 **/

class easterdatecalculator {
  public $dayofyead; // Day of the year for last conversion
  
  public function __construct() {
  }

  /**
   * Calculates the Easterdate for a particular year and outputs yyyy-mm-dd.
   **/

  public function easter($year) {
    $days = 0;
    $this->easter = $this->calcdate($year, $days);
    return $this->easter ;
  }

  /**
   * Calculates the date difference in $days from the Easterdate for a
   * particular year.
   **/

  private function calcdate($year, $days) {
    $m21 = 31+($this->isLeapYear($year) ? 29 : 28)+21; // number of day from Jan1 to March 21
    $edays = easter_days($year); // Use PHP function. Number of days after March 21 when Easter falls
    $t = $m21 + $edays;
    $t += $days; // Number of days between Easter and our target date. Can be plus or minus or zero.
    list($mo, $day) = $this->days2moday($year, $t);
    $this->dayofyear = $t;
    $this->calcdate = sprintf("%04d-%02d-%02d", $year, $mo, $day);
    return $this->calcdate;
  }

  /**
   * Given the year and day of the year return the month and day of the year.
   * @param $year
   * @param $days day of the year
   * @return array (month, day)
   */
  
  private function days2moday($year, $dayofyear) {
    $feb = $this->isLeapYear($year) ? 29 : 28;
    
    $months = array(31, $feb, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

    // Subtract the days in each month until value goes neg.
    
    for($i=0; $i < 12; ++$i) {
      $dayofyear -= $months[$i];
      if($dayofyear < 1) {
        $dayofyear += $months[$i]; // add back last month value
        $mo = $i + 1; // $i is zero based so add on to make the month
        break;
      }
    }
    return array($mo, $dayofyear);
  }
  
  /**
   * Calculates the date for Mardi Grass (in dutch called Carnaval) for a
   * particular year.
   **/

  public function carnaval($year)
  {
    $days = -49;
    $this->carnaval = $this->calcdate($year, $days) ;
    return $this->carnaval;
  }

  public function mardi_grass($year)
  {
    $days = -49;
    $this->carnaval = $this->calcdate($year, $days) ;
    return $this->carnaval;
  }

  /**
   * Calculates the date for Ash Wednessday (in dutch called Aswoensday) for a
   * particular year.
   **/

  public function aswoensdag($year)
  {
    $days = -46;
    $this->aswoensdag = $this->calcdate($year, $days) ;
    return $this->aswoensdag;
  }

  public function ash_wednesday($year)
  {
    $days = -46;
    $this->ash_wednesday = $this->calcdate($year, $days) ;
    return $this->ash_wednesday;
  }

  /**
   * Calculates the date for Good Friday (in dutch called Goede Vrijdag) for a
   * particular year.
   **/

  public function goede_vrijdag($year)
  {
    $days = -2;
    $this->goede_vrijdag = $this->calcdate($year, $days) ;
    return $this->goede_vrijdag;
  }

  public function good_friday($year)
  {
    $days = -2;
    $this->good_friday = $this->calcdate($year, $days) ;
    return $this->good_friday;
  }

  /**
   * Calculates the date for Assention (in dutch called Hemelvaartsdag) for a
   * particular year.
   **/

  public function hemelvaartsdag($year)
  {
    $days = 39;
    $this->goede_vrijdag = $this->calcdate($year, $days) ;
    return $this->goede_vrijdag;
  }

  public function assention($year)
  {
    $days = 39;
    $this->assention = $this->calcdate($year, $days) ;
    return $this->assention;
  }

  /**
   * Calculates the date for Pentacost (in dutch called Pinksteren) for a
   * particular year.
   **/

  public function pinksteren($year)
  {
    $days = 49;
    $this->pinksteren = $this->calcdate($year, $days) ;
    return $this->pinksteren;
  }

  public function pentecost($year)
  {
    $days = 49;
    $this->pentecost = $this->calcdate($year, $days) ;
    return $this->pentecost;
  }

  /**
   * Is this year a leap year
   * Every year that is exactly divisible by four is a leap year, except for years that are exactly divisible by 100;
   * the centurial years that are exactly divisible by 400 are still leap years.
   **/
  
  public function isLeapYear($year) {
    return ((($year%4==0) && ($year%100)) || $year%400==0) ? (true) : (false);
  }
}
