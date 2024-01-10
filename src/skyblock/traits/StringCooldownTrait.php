<?php

declare(strict_types=1);

namespace skyblock\traits;

use pocketmine\player\Player;

trait StringCooldownTrait {

	private array $cooldowns = [];

	public function setCooldown(string $id, int $seconds): void {
		$this->cooldowns[$id] = time() + $seconds;
	}

	public function getCooldown(string $id): int {
		$time = $this->cooldowns[$id] ?? time();

		return $time - time();
	}

	public function isOnCooldown(string $id): bool {
		return $this->getCooldown($id) > 0;
	}

	public function removeCooldown(string $id): void {
		unset($this->cooldowns[$id]);
	}
}