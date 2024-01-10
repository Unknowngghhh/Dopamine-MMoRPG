<?php

declare(strict_types=1);

namespace skyblock\items\ability;

use pocketmine\item\Item;
use skyblock\player\PvePlayer;
use skyblock\traits\StaticPlayerCooldownTrait;

abstract class ItemAbility {
	use StaticPlayerCooldownTrait;

	protected int $manaCost;
	protected int $cooldown;
	protected string $abilityName;

	public function __construct(
		string $abilityName,
		int $manaCost,
		int $cooldown
	) {
		$this->manaCost = $manaCost;
		$this->cooldown = $cooldown;
		$this->abilityName = $abilityName;
	}
	public function start(PvePlayer $player, Item $item): bool {
		if (self::getCooldownByName($this->abilityName, $player) > 0) {
			$player->sendActionBarMessage(
				'§cCooldown (§b' .
					number_format(
						self::getCooldownByName($this->abilityName, $player),
						2
					) .
					's§r§c)'
			);
			return false;
		}

		if ($player->getPveData()->getMana() < $this->manaCost) {
			$player->sendActionBarMessage('§cNot enough mana');
			return false;
		}

		$bool = $this->execute($player, $item);

		if ($bool) {
			self::setCooldownByName(
				$this->abilityName,
				$player,
				$this->cooldown
			);
			$player
				->getPveData()
				->setMana(
					$player->getPveData()->getIntelligence() - $this->manaCost
				);

			if ($this->manaCost > 0) {
				$player->sendActionBarMessage(
					"§c-{$this->manaCost} Mana ({$this->abilityName}§r§c)"
				);
			}
		}

		return $bool;
	}

	abstract protected function execute(PvePlayer $player, Item $item): bool;
}
