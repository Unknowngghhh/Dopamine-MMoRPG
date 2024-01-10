<?php

declare(strict_types=1);

namespace skyblock\items\itemattribute;

class ItemAttributeInstance {
	public function __construct(
		private ItemAttribute $attribute,
		private float $value
	) {
	}

	/**
	 * @return ItemAttribute
	 */
	public function getAttribute(): ItemAttribute {
		return $this->attribute;
	}

	/**
	 * @return float
	 */
	public function getValue(): float {
		return $this->value;
	}

	/**
	 * @param float $value
	 */
	public function setValue(float $value): void {
		$this->value = $value;
	}
}
