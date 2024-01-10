<?php

declare(strict_types=1);

namespace skyblock\entity\projectile;

use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use skyblock\items\SkyblockItem;
use skyblock\player\PvePlayer;

abstract class SkyBlockProjectile extends Projectile {
	protected function initEntity(CompoundTag $nbt): void {
		parent::initEntity($nbt);

		$this->setCanSaveWithChunk(false);
	}

	protected function onHit(ProjectileHitEvent $event): void {
		parent::onHit($event);

		$item = $this->getSourceItem();
		if ($item instanceof SkyblockItem) {
			$owning = $this->getOwningEntity();

			if ($owning instanceof PvePlayer && $owning->isOnline()) {
				$item->onProjectileHitEvent($owning, $event);
			}
		}
	}

	/** @var Item|null the item this projectile has been shot from, e.g. a bow */
	private ?Item $sourceItem = null;

	/**
	 * @return Item|null
	 */
	public function getSourceItem(): ?Item {
		return $this->sourceItem;
	}

	/**
	 * @param Item|null $sourceItem
	 */
	public function setSourceItem(?Item $sourceItem): void {
		$this->sourceItem = $sourceItem;
	}
}
