<?php

declare(strict_types=1);

namespace skyblock\traits;


trait StaticStringCooldownTrait {

	private static array $cooldowns = [];

	public static function setCooldown(string $string, int $seconds): void {
		self::$cooldowns[$string] = time() + $seconds;
	}

	public static function getCooldown(string $string): int {
		$time = self::$cooldowns[$string] ?? time();

		return $time - time();
	}

	public static function isOnCooldown(string $string): bool {
		return self::getCooldown($string) > 0;
	}

	public static function removeCooldown(string $string): void {
		unset(self::$cooldowns[$string]);
	}
}