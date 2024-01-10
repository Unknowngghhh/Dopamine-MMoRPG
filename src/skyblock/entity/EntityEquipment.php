<?php

declare(strict_types=1);

namespace skyblock\entity;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use skyblock\utils\Utils;

class EntityEquipment {
	public function __construct(
		public ?Item $hand,
		public ?Item $helmet = null,
		public ?Item $chestplate = null,
		public ?Item $leggings = null,
		public ?Item $boots = null
	) {
		if ($this->hand === null) {
			$this->hand = VanillaItems::AIR();
		}

		if ($this->chestplate === null) {
			$this->chestplate = VanillaItems::AIR();
		}

		if ($this->boots === null) {
			$this->boots = VanillaItems::AIR();
		}
		if ($this->helmet === null) {
			$this->helmet = VanillaItems::AIR();
		}

		if ($this->leggings === null) {
			$this->leggings = VanillaItems::AIR();
		}
	}

	public function serialize(): mixed {
		return [
			Utils::itemSerialize($this->hand),
			Utils::itemSerialize($this->helmet),
			Utils::itemSerialize($this->chestplate),
			Utils::itemSerialize($this->leggings),
			Utils::itemSerialize($this->boots),
		];
	}

	public static function deserialize(array $rows): self {
		return new self(
			Utils::itemDeserialize($rows[0]),
			Utils::itemDeserialize($rows[1]),
			Utils::itemDeserialize($rows[2]),
			Utils::itemDeserialize($rows[3]),
			Utils::itemDeserialize($rows[4])
		);
	}
}
