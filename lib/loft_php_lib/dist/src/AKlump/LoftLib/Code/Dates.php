<?php


namespace AKlump\LoftLib\Code;

// A shorter version that does not have the +0000
// The DateTime object must be in UTC timezone first.
define('DATE_ISO8601_SHORT', "Y-m-d\TH:i:s");

/**
 * @var DATE_QUARTER
 *
 * A string representing the quarter of the year of $date, e.g. 2017-Q4
 */
define('DATES_FORMAT_QUARTER', 'Y-Qq');

/**
 * @var DATES_FORMAT_ISO8601_TRIMMED
 */
define('DATES_FORMAT_ISO8601_TRIMMED', DATE_ISO8601 . '<');

class Dates {

    /**
     * Dates constructor.
     *
     * @param string             $localTimeZoneName The name of the timezone to use for local times, this is used when
     *                                              the timezone is not specified in dates used by this class.
     * @param string             $nowString         Optional.  This string will be used to compute the current moment
     *                                              in time.  By default the string is 'now'.
     * @param \DateTime|null     $periodStart       The bounds control how things like 'monthly' gets normalized.  By
     *                                              default the bounds are 1 month beginning the 1st of the current
     *                                              month.
     * @param \DateInterval|null $periodInterval    If you want a normalized monthly to generate 12 dates instead of 1,
     *                                              you would set this to 'P1Y' and set the $periodStart to the
     *                                              earliest month of the year, and probably January first.
     * @param array              $defaultTime       A three element indexed array with hour, minute, second for the
     *                                              default UTC time when using the normalize() method.  Be careful
     *                                              here because if you're local timezone is not UTC then you will not
     *                                              be getting the numbers you use here as you might expect.
     */
    public function __construct(
        $localTimeZoneName,
        $nowString = 'now',
        \DateTime $periodStart = null,
        \DateInterval $periodInterval = null,
        array $defaultTime = array()
    ) {
        $this->timezone = new \DateTimeZone($localTimeZoneName);
        $this->nowString = empty($nowString) ? 'now' : $nowString;
        $this->setNormalizationPeriod($periodStart, $periodInterval);
        $this->defaultTime = $defaultTime + [12, 0, 0];
    }

    public static function utc()
    {
        return new \DateTimeZone('UTC');
    }

    /**
     * Ensures that $date is a \DateTime object and set it's timezone to zulu (UTC)
     *
     * @param $date
     *
     * @return static
     */
    public static function z($date = 'now', $timezone = 'UTC')
    {
        return static::o($date, $timezone)->setTimezone(static::utc());
    }

    /**
     * Ensures that $date is a \DateTime object.
     *
     * If $date is a string it will be converted to an object using it's inherent timezone; if the timezone is not
     * inherent, then $timezone is used as the timezone of the object.
     *
     * Note how this is different from z(), in the case of z() the object will always have zulu timezone.  With o() if
     * the timezone is inherent, then the provided $timezone is ignored.  In the case of 2017-10-22 there is no
     * inherent timezone, so $timezone will be used to set the timezone on the returned object.
     *
     * @param string|\DateTime     $date
     * @param string|\DateTimeZone $timezone
     *
     * @return \DateTime|false
     *
     */
    public static function o($date, $timezone = 'UTC')
    {
        $timezone = is_string($timezone) ? new \DateTimeZone($timezone) : $timezone;

        return is_string($date) ? date_create($date, $timezone) : $date;
    }

    /**
     * Return an array of first/last seconds in the quarter of $date; no timezone conversion.
     *
     * @param \DateTime $date
     *
     * @return array
     * - \DateTime First second of the quarter.
     * - \DateTime Last second of the quarter.
     */
    public static function getQuarter(\DateTime $date)
    {
        $y = $date->format('Y');
        $n = static::format($date, 'q') * 3;
        $n = [$n - 2, $n * 1];
        $d1 = clone $date;
        $d2 = clone $date;
        $d2 = $d2->setDate($y, $n[1], 1);
        $d2 = $d2->setDate($y, $n[1], 1 * $d2->format('t'));
        $n = [$d1->setDate($y, $n[0], 1)->setTime(0, 0, 0), $d2->setTime(23, 59, 59)];

        return $n;
    }

    /**
     * Get the dates of the year quarter just after that in which $date falls.
     *
     * @param string|\DateTime $date
     *
     * @return array
     */
    public static function getNextQuarter($date)
    {
        $q = static::getQuarter($date);
        $q[1]->add(new \DateInterval('PT1S'));

        return static::getQuarter($q[1]);
    }

    /**
     * Get the dates of the year quarter just before that in which $date falls.
     *
     * @param string|\DateTime $date
     *
     * @return array
     */
    public static function getLastQuarter($date)
    {
        $q = static::getQuarter($date);
        $q[0]->sub(new \DateInterval('PT1S'));

        return static::getQuarter($q[0]);
    }

    /**
     * Additional formatting of \DateTime objects or string.
     *
     * Figure out the quarter a date falls into in a year
     *
     * Shorten a string by making assumptions that no timezone is UTC, no seconds, minutes or hours are 0.  Be aware
     * that shortened strings do not expand back by simply running them through date_create(), so be careful with
     * dataloss.  This can be used for ids based on dates, which will be unique to the second, and as short as possible.
     *
     * @param string|\DateTime $date       The date that is to be formatted.
     * @param                  $format     The format string; this includes date() with the addition of:
     *                                     - 'q'  The quarter in the year of the date.  1 to 4
     *                                     - '<'  End an ISO8601 string with < and:
     *                                     - The rightmost 'Z' or '+0000' will be removed
     *                                     - The rightmost ':00' will be removed
     *                                     - The rightmost 'T' will be removed.
     *
     * @return string
     *
     * @see http://php.net/manual/en/function.date.php
     * @see http://php.net/manual/en/class.datetime.php
     */
    public static function format(\DateTime $date, $format)
    {
        //  Now apply our custom formatting
        $format = preg_replace_callback('/(?<!\\\\)q/', function ($matches) use ($format, $date) {
            return strval(ceil($date->format('m') / 3));
        }, $format);
        $format = $date->format($format);

        if (substr($format, -1) === '<') {
            $format = rtrim($format, '<0Z');
            $format = rtrim($format, '+0:');
            $format = rtrim($format, 'T');
        }

        return $format;
    }

    public static function setYear(\DateTime $date, $year)
    {
        return static::setDate($date, 'y', $year);
    }

    public static function setMonth(\DateTime $date, $month)
    {
        return static::setDate($date, 'm', $month);
    }

    public static function setDay(\DateTime $date, $day)
    {
        return static::setDate($date, 'd', $day);
    }

    public static function setHour(\DateTime $date, $hour)
    {
        return static::setTime($date, 'h', $hour);
    }

    public static function setMinute(\DateTime $date, $minute)
    {
        return static::setTime($date, 'm', $minute);
    }

    public static function setSecond(\DateTime $date, $second)
    {
        return static::setTime($date, 's', $second);
    }

    public static function getMonthFromString($month, $default = null)
    {
        $months = range(1, 12);
        if (!is_numeric($month)) {
            $month_map = array_map(function ($m) {

                $mm = static::setMonth(date_create(), $m);
                $mm = static::setDay($mm, 1);

                return [
                    $m,
                    strtolower($mm->format('F')),
                ];
            }, $months);
            $month_map = array_map(function ($item) use ($month) {
                if (preg_match('/^' . preg_quote($month . '/i'), $item[1])) {
                    return $item[0];
                };
            }, $month_map);
            $month = array_filter($month_map);
            $month = count($month) === 1 ? reset($month) : $default;
        }

        return in_array($month, $months) ? $month : $default;
    }

    private static function setDate($date, $key, $value)
    {
        $y = $date->format('Y') * 1;
        $m = $date->format('n') * 1;
        $d = $date->format('j') * 1;

        // ymd === 20171031 and we're setting the month as 9, then we have to drop the day down to the highest in the month or the month shifts.  This is awkward.
        $$key = $value * 1;
        $d = min($d, $date->setDate($y, $m, 1)->format('t'));

        return $date->setDate($y, $m, $d);
    }

    private static function setTime($date, $key, $value)
    {
        $h = $date->format('G') * 1;
        $m = $date->format('H') * 1;
        $s = $date->format('s') * 1;
        $$key = $value * 1;

        return $date->setTime($h, $m, $s);
    }

    /**
     * Checks to see if now (in local tz) is between 00:00:00 and 23:59:59 on $day1.
     *
     * @param $day1
     *
     * @return bool
     *
     * @see isTodayInDays().
     */
    public function isToday($day1)
    {
        return $this->isTodayInDays($day1, $day1);
    }

    /**
     * Checks to see if today (in local timezone) is between two days (normalized to local tz).
     *
     * If you pass no argument for $day2, then the $day2 will assume end of the day of $day1.
     *
     * @param string $day1 Passed through normalizeToOne().  After normalizing the time is set to 0,0,0 local.
     * @param string $day2 Passed through normalizeToOne().  After normalizing the time is set to 23,59,59 local.
     *
     * @return bool
     */
    public function isTodayInDays($day1, $day2)
    {
        $day1 = $this->l($this->normalizeToOne($day1))->setTime(0, 0, 0);
        $day2 = $this->l($this->normalizeToOne($day2))->setTime(23, 59, 59);
        $now = $this->now();

        return $day1 <= $now && $now <= $day2;
    }

    /**
     * Filter an array of dates to those within our period
     *
     * @param array $dates
     *
     * @return array
     */
    public function filter(array $dates)
    {
        list($from, $to) = $this->bounds;

        return array_values(array_filter($dates, function ($date) use ($from, $to) {
            $date = $this->o($date, $this->timezone);

            return $from <= $date && $date <= $to;
        }));
    }


    /**
     * Filter an array of dates to those within our period
     *
     * @param array $dates
     *
     * @return array
     */
    public function filterAfter(array $dates)
    {
        list($from, $to) = $this->bounds;

        return array_values(array_filter($dates, function ($date) use ($from, $to) {
            $date = $this->o($date, $this->timezone);

            return $date > $to;
        }));
    }


    /**
     * Filter an array of dates to those within our period
     *
     * @param array $dates
     *
     * @return array
     */
    public function filterBefore(array $dates)
    {
        list($from, $to) = $this->bounds;

        return array_values(array_filter($dates, function ($date) use ($from, $to) {
            $date = $this->o($date, $this->timezone);

            return $date < $from;
        }));
    }

    /**
     * Return the current DateTime in the local timezone.
     *
     * @return \DateTime|mixed
     */
    public function now()
    {
        return $this->create($this->nowString);
    }

    /**
     * Creates a timezone object and ensures the timezone is set to the locale.
     *
     * In the case $date is already an object, ensures the timezone is in the locale.
     *
     * @param string|\DateTime $date
     *
     * @return static
     */
    public function create($date)
    {
        return $this->l($date);
    }

    /**
     * Ensures that $date is an object in the local timezone.
     *
     * @param $date
     *
     * @return static
     */
    public function l($date)
    {
        return $this->o($date, $this->timezone)->setTimeZone($this->timezone);
    }

    /**
     * Convert a string representing a date into an array of UTC DateTime objects.
     *
     * @param string $date_string
     * @param string $format Omit to return objects, include and the array will contain formatted dates using $format.
     *
     * @return array An array of dates as normalized in this function
     *
     */
    public function normalize($date_string, $format = DATE_ISO8601_SHORT)
    {
        $dates = [];
        $now = $this->now();
        list($default_hour, $default_minute, $default_second) = $this->defaultTime;
        if (preg_match('/(?:in )?(.+?)\s+(?:by|on)\s+the\s+(.+)/i', $date_string, $matches)) {
            $months = array_map(function ($value) {
                return trim($value);
            }, explode(',', str_replace('and', ',', $matches[1])));

            // Handle monthly
            if (in_array('monthly', $months)) {
                $m = [];
                array_walk($months, function ($month) use (&$m) {
                    if ($month === 'monthly') {
                        $period = new \DatePeriod($this->bounds[0], new \DateInterval('P1M'), $this->bounds[1]);
                        foreach ($period as $item) {
                            $m[] = $item->format('M');
                        }
                    }
                    else {
                        $m[] = $month;
                    }
                });
                $months = $m;
            }

            preg_match_all('/(eom|\d+(?:th|nd|st|rd))/i', $matches[2], $temp);
            $days = isset($temp[1]) ? $temp[1] : array();

            foreach ($months as $month) {
                $working_date = clone $now;
                $working_date->setTime($default_hour, $default_minute, $default_second);
                $month = $this->getMonthFromString($month);
                $this->setMonth($working_date, $month);

                foreach ($days as $day) {
                    if ($day === 'eom') {
                        $this->setDay($working_date, 1);
                        $day = 1 * $working_date->format('t');
                    }
                    else {
                        $day = preg_replace('/[^\d]/', '', $day);
                    }
                    $dates[] = clone $this->setDay($working_date, $day);
                }
            }
        }
        elseif ($date_string) {
            $working_date = str_replace(' at ', '', $date_string);
            $working_date = $this->create($working_date);
            if (preg_match('/\S+\s+\d+(th|nd|st|rd)/', $date_string)) {
                $working_date->setTime($default_hour, $default_minute, $default_second);
            }
            $dates = [$working_date];
        }

        // Normalize all timezones to UTC
        $dates = array_map(function ($date) {
            return $this->z($date);
        }, $dates);

        // Convert to strings if asked.
        if ($format) {
            $dates = array_map(function ($date) use ($format) {
                return static::format($date, $format);
            }, $dates);
        }

        return $dates;
    }

    /**
     * Normalize a date string when you require exactly one value.
     *
     * @param string $date_string
     * @param string $format
     *
     * @return \DateTime Normalized object in UTC.
     *
     * @throws \InvalidArgumentException When normalize returns other than exactly one value.
     */
    public function normalizeToOne($date_string, $format = DATE_ISO8601_SHORT)
    {
        $result = $this->normalize($date_string, $format);
        if (count($result) !== 1) {
            throw new \InvalidArgumentException("\"$date_string\" must only normalize to a single date.");
        }

        return $result[0];
    }

    /**
     * These bounds affect how things like "monthly" plays out.
     *
     * @param \DateTime|null     $start
     * @param \DateInterval|null $period
     *
     * @return $this
     */
    private function setNormalizationPeriod(\DateTime $start = null, \DateInterval $period = null)
    {
        $start = is_null($start) ? $this->setDay($this->now(), 1)->setTime(0, 0, 0) : $start;
        $period = is_null($period) ? new \DateInterval('P1M') : $period;
        $end = clone $start;
        $this->bounds = [$start, $end->add($period)->sub(new \DateInterval('PT1S'))];

        return $this;
    }
}
