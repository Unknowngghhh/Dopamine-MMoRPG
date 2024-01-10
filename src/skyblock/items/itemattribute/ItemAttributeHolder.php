<?php

declare(strict_types=1);

namespace skyblock\items\itemattribute;

interface ItemAttributeHolder {
	/**
	 * @return ItemAttributeInstance[]
	 */
	public function getItemAttributes(): array;
	public function getItemAttribute(
		ItemAttribute $attribute
	): ItemAttributeInstance;
	public function setItemAttribute(ItemAttributeInstance $instance): self;
}
