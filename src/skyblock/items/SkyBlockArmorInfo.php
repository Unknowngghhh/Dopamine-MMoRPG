<?php

declare(strict_types=1);

namespace skyblock\items;

use pocketmine\item\ArmorTypeInfo;
use skyblock\items\rarity\Rarity;

class SkyBlockArmorInfo {
	private Rarity $rarity;

	public function __construct(
		private int $defensePoints,
		private int $armorSlot,
		?Rarity $rarity = null
	) {
		if ($rarity === null) {
			$this->rarity = Rarity::common();
		} else {
			$this->rarity = $rarity;
		}
	}

	public function getDefensePoints(): int {
		return $this->defensePoints;
	}

	public function getRarity(): Rarity {
		return $this->rarity;
	}

	public function getArmorSlot(): int {
		return $this->armorSlot;
	}
}
