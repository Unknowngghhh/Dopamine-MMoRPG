<?php

declare(strict_types = 1);

namespace skyblock\utils;

class TimeUtils {

	public const SECONDS_IN_DAY = 86400;
	public const SECONDS_IN_WEEK = self::SECONDS_IN_DAY * 7;
	public const SECONDS_IN_MONTH = self::SECONDS_IN_DAY * 30;

	public static function secondsToTime(int $inputSeconds) : array {
		$secondsInAMinute = 60;
		$secondsInAnHour = 60 * $secondsInAMinute;
		$secondsInADay = 24 * $secondsInAnHour;

		$days = floor($inputSeconds / $secondsInADay);

		$hourSeconds = $inputSeconds % $secondsInADay;
		$hours = floor($hourSeconds / $secondsInAnHour);

		$minuteSeconds = $hourSeconds % $secondsInAnHour;
		$minutes = floor($minuteSeconds / $secondsInAMinute);

		$remainingSeconds = $minuteSeconds % $secondsInAMinute;
		$seconds = ceil($remainingSeconds);

		return ['d' => (int)$days,
			'h' => (int)$hours,
			'm' => (int)$minutes,
			's' => (int)$seconds,
		];
	}

	public static function getFormattedTime(int $seconds): string {
		$string = "";

		foreach (self::secondsToTime($seconds) as $k => $v) {
			if ($v > 0) {
				if ($string === "") {
					$string .= $v . $k;
				} else $string .= " " . $v . $k;
			}
		}

		return $string;
	}

	public static function getFullyFormattedTime(int $seconds): string {
		$dtF = new \DateTime('@0');
		$dtT = new \DateTime("@$seconds");
		return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
	}
}