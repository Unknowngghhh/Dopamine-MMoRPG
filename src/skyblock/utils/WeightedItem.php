<?php

declare(strict_types=1);

namespace skyblock\utils;

use JsonSerializable;
use pocketmine\item\Item;
use pocketmine\world\sound\ClickSound;

class WeightedItem {
	public string $uuid;

	public function __construct(
		private Item $item,
		private float $chance = 100,
		private int $minCount = 1,
		private int $maxCount = 1,
		private bool $addUniqueId = false
	) {
		$this->uuid = uniqid('' . mt_rand(1, 380) . ''); //extra safety just to be safe
	}

	/**
	 * @return Item
	 */
	public function getItem(): Item {
		$i = clone $this->item;

		if ($this->addUniqueId) {
			$i->getNamedTag()->setString(
				'unique_id',
				uniqid('' . mt_rand(1, 10))
			);
		}

		return $i;
	}

	public function getChance(): float {
		return $this->chance;
	}

	public function getMaxCount(): int {
		return $this->maxCount;
	}

	public function getMinCount(): int {
		return $this->minCount;
	}

	public function setMaxCount(int $maxCount): void {
		$this->maxCount = $maxCount;
	}

	public function setMinCount(int $minCount): void {
		$this->minCount = $minCount;
	}

	public function setChance(int $chance): void {
		$this->chance = $chance;
	}
}
