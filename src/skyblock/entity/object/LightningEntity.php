<?php

declare(strict_types=1);

namespace skyblock\entity\object;

use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class LightningEntity extends Entity {
	private int $lived = 0;

	protected function initEntity(CompoundTag $nbt): void {
		parent::initEntity($nbt);

		$this->setCanSaveWithChunk(false);
	}

	public function onUpdate(int $currentTick): bool {
		if (++$this->lived >= 10 * 20) {
			$this->flagForDespawn();
		}

		return parent::onUpdate($currentTick);
	}

	public static function getNetworkTypeId(): string {
		return EntityIds::LIGHTNING_BOLT;
	}

	protected function getInitialSizeInfo(): EntitySizeInfo {
		return new EntitySizeInfo(1.8, 0.3);
	}

	protected function getInitialGravity(): float {
		return 0.0;
	}

	protected function getInitialDragMultiplier(): float {
		return 0.2;
	}
}
