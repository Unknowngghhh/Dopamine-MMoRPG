<?php

declare(strict_types=1);

namespace skyblock\entity\projectile;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class InkBombEntity extends SkyBlockProjectile {
	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.3, 0.3); }

	public static function getNetworkTypeId() : string{
		return EntityIds::AGENT;
	}

	protected function getInitialGravity(): float {
		return 0.0;
	}

	protected function getInitialDragMultiplier(): float {
		return 1;
	}

	protected function onHit(ProjectileHitEvent $event) : void{
		parent::onHit($event);

		$this->flagForDespawn();
	}
}