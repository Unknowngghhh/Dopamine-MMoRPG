<?php

declare(strict_types=1);

namespace skyblock\items;

use skyblock\items\itemattribute\ItemAttributeHolder;
use skyblock\items\itemattribute\ItemAttributeTrait;

abstract class Equipment extends SkyblockItem implements ItemAttributeHolder{
	use ItemAttributeTrait;

	public function resetLore(array $lore = []) : void{
		foreach($this->getItemAttributes() as $attributeInstance) {
			$v = $attributeInstance->getValue();
			$attribute = $attributeInstance->getAttribute();
			$unit = $attribute->isPercentage() ? "%" : "";

			$lore[] = "§r§7{$attribute->getName()}: " . $attribute->getColor() . ($v > 0 ? "+" : "-") . number_format($v, ((((float) ((int) $v)) === $v ? 0 : 1))) . "$unit";
		}

		parent::resetLore($lore);
	}
}