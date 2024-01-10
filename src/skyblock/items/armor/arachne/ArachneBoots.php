<?php

declare(strict_types=1);

namespace skyblock\items\armor\arachne;

use customiesdevs\customies\item\component\ArmorComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\player\Player;
use skyblock\items\armor\ArmorSet;
use skyblock\items\sets\types\ArachnesArmorSet;
use skyblock\items\SkyblockArmor;
use skyblock\items\SkyBlockArmorInfo;
use skyblock\items\SkyblockItemProperties;
use skyblock\Main;

class ArachneBoots extends SkyblockArmor implements ItemComponents {
	use ItemComponentsTrait;

	public function __construct(
		ItemIdentifier $identifier,
		string $name = 'Unknown'
	) {
		parent::__construct(
			$identifier,
			$name,
			new SkyBlockArmorInfo(4, ArmorInventory::SLOT_FEET)
		);

		$this->initComponent(
			'leather_boots',
			new CreativeInventoryInfo(
				CreativeInventoryInfo::CATEGORY_EQUIPMENT,
				CreativeInventoryInfo::GROUP_BOOTS
			)
		);

		$this->properties->setType(SkyblockItemProperties::ITEM_TYPE_ARMOR);

		$this->addComponent(new ArmorComponent(4, 'diamond'));
	}

	public function onTransaction(
		Player $player,
		Item $itemClicked,
		Item $itemClickedWith,
		SlotChangeAction $itemClickedAction,
		SlotChangeAction $itemClickedWithAction,
		InventoryTransactionEvent $event
	): void {
		parent::onTransaction(
			$player,
			$itemClicked,
			$itemClickedWith,
			$itemClickedAction,
			$itemClickedWithAction,
			$event
		);

		if (
			$itemClickedWith instanceof ArachneBoots &&
			$itemClicked instanceof ArachneBoots
		) {
			$stacked = $itemClicked
				->getNamedTag()
				->getInt(ArachneSet::TAG_ARACHNES_STACK, 1);
			$total =
				$stacked +
				$itemClickedWith
					->getNamedTag()
					->getInt(ArachneSet::TAG_ARACHNES_STACK, 1);

			if ($total > 8) {
				$player->sendMessage(
					Main::PREFIX .
						"There's already 8 armor pieces stacked on this piece"
				);
				return;
			}

			$itemClicked
				->getNamedTag()
				->setInt(ArachneSet::TAG_ARACHNES_STACK, $total);

			$player->sendMessage(
				Main::PREFIX .
					'Successfully stacked ' .
					$itemClicked->getCustomName()
			);

			$itemClicked
				->getProperties()
				->setDescription(
					ArachneSet::getInstance()->getLore($itemClicked)
				);
			$itemClicked->resetLore();

			$itemClickedWith->pop();
			$event->cancel();
			$itemClickedWithAction
				->getInventory()
				->setItem($itemClickedWithAction->getSlot(), $itemClickedWith);
			$itemClickedAction
				->getInventory()
				->setItem($itemClickedAction->getSlot(), $itemClicked);
		}
	}

	public function getArmorSet(): ?ArmorSet {
		return ArachneSet::getInstance();
	}
}
