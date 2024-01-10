<?php

declare(strict_types=1);

namespace skyblock\traits;

use pocketmine\player\Player;

trait PlayerCooldownTrait {

    private array $cooldowns = [];

    private array $namedCooldowns = [];

    public function setCooldown(Player $player, float $seconds): void {
        $this->cooldowns[$player->getName()] = time() + $seconds;
    }

    public function getCooldown(Player $player): float {
		$time = $this->cooldowns[$player->getName()] ?? microtime(true);

		return $time - microtime(true);
    }

	public function isOnCooldown(Player $player): bool {
		return $this->getCooldown($player) > 0;
	}

	public function removeCooldown(Player $player): void {
		unset($this->cooldowns[$player->getName()]);
	}

    public function setCooldownByName(string $name, Player $player, int $seconds): void {
        if (!isset($this->namedCooldowns[$name])) {
            $this->namedCooldowns[$name] = [];
        }

        $this->namedCooldowns[$name][$player->getName()] = time() + $seconds;
    }

    public function getCooldownByName(string $name, Player $player): int {
        if (!isset($this->namedCooldowns[$name])) {
            $this->namedCooldowns[$name] = [];
        }

        return ($this->namedCooldowns[$name][$player->getName()] ?? time()) - time();
    }

    public function isOnCooldownByName(string $name, Player $player): bool {
        if (!isset($this->namedCooldowns[$name])) {
            return false;
        }

        return $this->getCooldownByName($name, $player) > 1;
    }

    public function removeCooldownByName(string $name, Player $player): void {
        if (!isset($this->namedCooldowns[$name])) {
            return;
        }

        unset($this->namedCooldowns[$name][$player->getName()]);
    }
}