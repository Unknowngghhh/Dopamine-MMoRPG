<?php

declare(strict_types=1);

namespace skyblock\traits;

trait RarityTrait {

	protected int $rarity;

	public static function common(): int {
		return 1;
	}

	public static function uncommon(): int {
		return 2;
	}

	public static function rare(): int {
		return 3;
	}

	public static function epic(): int {
		return 4;
	}

	public static function legendary(): int {
		return 5;
	}

	public function isLegendary(): bool {
		return $this->rarity === self::legendary();
	}

	public function isEpic(): bool {
		return $this->rarity === self::epic();
	}

	public function isRare(): bool {
		return $this->rarity === self::rare();
	}

	public function isUncommon(): bool {
		return $this->rarity === self::uncommon();
	}

	public function isCommon(): bool {
		return $this->rarity === self::common();
	}

	/**
	 * @param int $rarity
	 */
	public function setRarity(int $rarity) : void{
		$this->rarity = $rarity;
	}

	/**
	 * @return int
	 */
	public function getRarity() : int{
		return $this->rarity;
	}
}