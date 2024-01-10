<?php

declare(strict_types=1);

namespace skyblock\items\itemattribute;

class ItemAttribute {
	public function __construct(
		private string $name,
		private string $symbol,
		private string $color,
		private bool $isPercentage = false,
		private int $minValue = 0,
		private int $maxValue = PHP_INT_MAX
	) {
	}

	/**
	 * @return bool
	 */
	public function isPercentage(): bool {
		return $this->isPercentage;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getColor(): string {
		return $this->color;
	}

	/**
	 * @return int
	 */
	public function getMaxValue(): int {
		return $this->maxValue;
	}

	/**
	 * @return int
	 */
	public function getMinValue(): int {
		return $this->minValue;
	}

	/**
	 * @return string
	 */
	public function getSymbol(): string {
		return $this->symbol;
	}
}
