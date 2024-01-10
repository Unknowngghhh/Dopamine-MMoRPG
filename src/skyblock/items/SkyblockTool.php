<?php

declare(strict_types=1);

namespace skyblock\items;

use customiesdevs\customies\item\component\HandEquippedComponent;
use customiesdevs\customies\item\ItemComponents;
use pocketmine\item\ItemIdentifier;

class SkyblockTool extends Equipment {
	public function __construct(
		ItemIdentifier $identifier,
		string $name = 'Unknown'
	) {
		parent::__construct($identifier, $name);

		$this->setProperties(
			$this->getProperties()->setType(
				SkyblockItemProperties::ITEM_TYPE_TOOL
			)
		);

		if ($this instanceof ItemComponents) {
			$this->addComponent(new HandEquippedComponent());
		}
	}

	public function buildProperties(): SkyblockItemProperties {
		return new SkyblockItemProperties();
	}

	public function getMaxStackSize(): int {
		return 1;
	}

	//TODO: This needs to handle and tool related tasks.
}
