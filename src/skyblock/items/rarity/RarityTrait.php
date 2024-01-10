<?php

namespace skyblock\items\rarity;

trait RarityTrait {
	private ?Rarity $rarity = null;

	public function setRarity(Rarity $rarity): void {
		$this->rarity = $rarity;
	}

	public function getRarity(): Rarity {
		if ($this->rarity === null) {
			$this->rarity = Rarity::common();
		}

		return $this->rarity;
	}

	public function isMoreRare(Rarity $rarity): bool {
		return $this->getRarity()->getTier() > $rarity->getTier();
	}
}
