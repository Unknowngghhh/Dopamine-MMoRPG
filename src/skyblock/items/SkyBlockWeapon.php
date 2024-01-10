<?php

declare(strict_types=1);

namespace skyblock\items;

use pocketmine\block\BlockToolType;
use pocketmine\item\ItemIdentifier;

class SkyBlockWeapon extends SkyblockTool {
	public function __construct(
		ItemIdentifier $identifier,
		string $name = 'Unknown'
	) {
		parent::__construct($identifier, $name);

		$this->setProperties(
			$this->getProperties()->setType(
				SkyblockItemProperties::ITEM_TYPE_WEAPON
			)
		);
	}

	public function getBlockToolType(): int {
		return BlockToolType::SWORD;
	}
}
