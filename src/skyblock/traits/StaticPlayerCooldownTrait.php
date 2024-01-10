<?php

declare(strict_types=1);

namespace skyblock\traits;

use pocketmine\player\Player;

trait StaticPlayerCooldownTrait {

	private static array $cooldowns = [];

	private static array $namedCooldowns = [];

	public static function setCooldown(Player $player, float $seconds): void {
		self::$cooldowns[$player->getName()] = time() + $seconds;
	}

	public static function getCooldown(Player $player): float {
		$time = self::$cooldowns[$player->getName()] ?? microtime(true);

		return $time - microtime(true);
	}

	public static function isOnCooldown(Player $player): bool {
		return self::getCooldown($player) > 0;
	}

	public static function removeCooldown(Player $player): void {
		unset(self::$cooldowns[$player->getName()]);
	}

	public static function setCooldownByName(string $name, Player $player, float $seconds): void {
		if (!isset(self::$namedCooldowns[$name])) {
			self::$namedCooldowns[$name] = [];
		}

		self::$namedCooldowns[$name][$player->getName()] = microtime(true) + $seconds;
	}

	public static function getCooldownByName(string $name, Player $player): float {
		if (!isset(self::$namedCooldowns[$name])) {
			self::$namedCooldowns[$name] = [];
		}

		return (self::$namedCooldowns[$name][$player->getName()] ?? microtime(true)) - microtime(true);
	}

	public static function isOnCooldownByName(string $name, Player $player): bool {
		if (!isset(self::$namedCooldowns[$name])) {
			return false;
		}

		return self::getCooldownByName($name, $player) > 1;
	}

	public static function removeCooldownByName(string $name, Player $player): void {
		if (!isset(self::$namedCooldowns[$name])) {
			return;
		}

		unset(self::$namedCooldowns[$name][$player->getName()]);
	}
}