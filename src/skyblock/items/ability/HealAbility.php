<?php

declare(strict_types=1);

namespace skyblock\items\ability;

use pocketmine\item\Item;
use skyblock\player\PvePlayer;
use skyblock\traits\AwaitStdTrait;
use skyblock\utils\PveUtils;
use SOFe\AwaitGenerator\Await;

class HealAbility extends ItemAbility {
	use AwaitStdTrait;

	public function __construct(
		private float $health,
		private int $range,
		string $abilityName,
		int $manaCost,
		int $cooldown
	) {
		parent::__construct($abilityName, $manaCost, $cooldown);
	}

	protected function execute(PvePlayer $player, Item $item): bool {
		foreach (
			$player
				->getWorld()
				->getNearbyEntities(
					$player
						->getBoundingBox()
						->expandedCopy($this->range, $this->range, $this->range)
				)
			as $e
		) {
			if ($e instanceof PvePlayer) {
				$player
					->getPveData()
					->setHealth(
						$player->getPveData()->getHealth() + $this->health
					);
				$player->sendActionBarMessage(
					"§r§a+{$this->health} " .
						PveUtils::getHealth() .
						" ({$player->getName()})"
				);
			}
		}

		return true;
	}
}
