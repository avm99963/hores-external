<?php
/*
 * hores
 * Copyright (c) 2023 Adrià Vilanova Martínez
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this program.
 * If not, see http://www.gnu.org/licenses/.
 */

class date {
  const LOCALE = 'es';

  private static function createFormatter(string $pattern) {
    return new IntlDateFormatter(
        locale: self::LOCALE,
        dateType: IntlDateFormatter::FULL,
        timeType: IntlDateFormatter::FULL,
        timezone: null,
        calendar: IntlDateFormatter::GREGORIAN,
        pattern: $pattern
    );
  }

  public static function getMonthYear(IntlCalendar|DateTimeInterface|array|string|int|float $timestamp) {
    static $formatter = self::createFormatter("MMMM yyyy");
    return $formatter->format($timestamp);
  }

  public static function getShortDate(IntlCalendar|DateTimeInterface|array|string|int|float $timestamp) {
    static $formatter = self::createFormatter("dd MMM yyyy");
    return $formatter->format($timestamp);
  }

  public static function getShortDateWithTime(IntlCalendar|DateTimeInterface|array|string|int|float $timestamp) {
    static $formatter = self::createFormatter("dd MMM yyyy HH:mm:ss");
    return $formatter->format($timestamp);
  }

  public static function getLongDate(IntlCalendar|DateTimeInterface|array|string|int|float $timestamp) {
    static $formatter = self::createFormatter("dd 'de' MMMM 'de' yyyy");
    return $formatter->format($timestamp);
  }
}
