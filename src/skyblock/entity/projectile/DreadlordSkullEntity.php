<?php

declare(strict_types=1);

namespace skyblock\entity\projectile;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class DreadlordSkullEntity extends SkyBlockProjectile {
	protected function onHit(ProjectileHitEvent $event): void {
		parent::onHit($event);

		$this->flagForDespawn();
	}

	protected function getInitialSizeInfo(): EntitySizeInfo {
		return new EntitySizeInfo(0.3125, 0.3125);
	}

	protected function getInitialGravity(): float {
		return 0.0;
	}

	protected function getInitialDragMultiplier(): float {
		return 1.2;
	}

	public static function getNetworkTypeId(): string {
		return EntityIds::WITHER_SKULL;
	}
}
