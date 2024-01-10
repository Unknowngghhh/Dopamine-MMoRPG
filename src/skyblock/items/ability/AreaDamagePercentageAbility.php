<?php

declare(strict_types=1);

namespace skyblock\items\ability;

use pocketmine\item\Item;
use skyblock\entity\PveEntity;
use skyblock\events\PlayerAttackEntityEvent;
use skyblock\player\PvePlayer;

class AreaDamagePercentageAbility extends ItemAbility {
	/**
	 * @param int                  $range
	 * @param float                $percentage range 0-inf
	 * @param PlayerAttackEntityEvent $event
	 * @param string               $abilityName
	 * @param int                  $manaCost
	 * @param int                  $cooldown
	 */
	public function __construct(
		private int $range,
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

		$found = false;
		foreach (
			$e
				->getWorld()
				->getNearbyEntities(
					$e
						->getBoundingBox()
						->expandedCopy($this->range, $this->range, $this->range)
				)
			as $v
		) {
			if ($v instanceof PveEntity && $v->getId() !== $e->getId()) {
				$e = new PlayerAttackEntityEvent(
					$player,
					$v,
					$this->event->getFinalDamage() * $this->percentage
				);
				$e->setCause($this);
				$e->call();

				$found = true;
			}
		}

		return $found;
	}
}
