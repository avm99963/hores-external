<?php
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
