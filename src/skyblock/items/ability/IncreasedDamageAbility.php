<?php

declare(strict_types=1);

namespace skyblock\items\ability;

use pocketmine\item\Item;
use skyblock\entity\PveEntity;
use skyblock\events\PlayerAttackPveEvent;
use skyblock\player\PvePlayer;

class IncreasedDamageAbility extends ItemAbility {
	/**
	 * @param array                   $entityIds
	 * @param float                   $percentage range 0-1
	 * @param PlayerAttackEntityEvent $event
	 * @param string                  $abilityName
	 * @param int                     $manaCost
	 * @param int                     $cooldown
	 */
	public function __construct(
		private array $entityIds,
		private float $percentage,
		private PlayerAttackEntityEvent $event,
		string $abilityName,
		int $manaCost,
		int $cooldown
	) {
		parent::__construct($abilityName, $manaCost, $cooldown);
	}

	protected function execute(PvePlayer $player, Item $item): bool {
		$e = $this->event->getEntity();

		if (in_array($e->getNetworkID(), $this->entityIds)) {
			$this->event->multiplyDamage($this->percentage, $this->abilityName);
			return true;
		}

		return false;
	}
}
