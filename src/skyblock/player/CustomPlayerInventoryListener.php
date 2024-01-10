<?php

declare(strict_types=1);

namespace skyblock\player;

use Exception;
use pocketmine\block\BlockFactory;
use pocketmine\block\Coal;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryListener;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Book;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\Server;
use skyblock\items\itemattribute\ItemAttributeHolder;
use skyblock\items\itemattribute\SkyBlockItemAttributes as ATTR;
use skyblock\items\PvEItemEditor;
use skyblock\items\SkyblockItem;

class CustomPlayerInventoryListener implements InventoryListener {

	public function __construct(private PvePlayer $player){
		self::checkPvE($this->player, VanillaItems::AIR(), $this->player->getInventory()->getItemInHand());
	}

	public function onHeldItemIndexChange(int $oldIndex): void {
		$oldItem = $this->player->getInventory()->getItem($oldIndex);
		$newItem = $this->player->getInventory()->getItemInHand();

		if($newItem instanceof Book){
			return;
		}

		self::checkPvE($this->player, $oldItem, $newItem);
	}

	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem) : void{
		$newItem = $inventory->getItem($slot);

		if($slot !== $this->player->getInventory()->getHeldItemIndex()) return;

		/*if($newItem->getId() === ItemIds::FISHING_ROD && CustomFishingRod::getLevel($newItem) === -1){
			$inventory->setItem($slot, CustomFishingRod::getItem(1, 0));
			return;
		}*/

		if($oldItem->equals($newItem, false, true)){
			return;
		}

		if($newItem instanceof Book){
			return;
		}

		self::checkPvE($this->player, $oldItem, $newItem);
	}

	public static function checkPvE(PvePlayer $player, Item $old, Item $new): void {
		$pve = $player->getPveData();

		try{
			if($old instanceof ItemAttributeHolder){
				$pve->setIntelligence($pve->getIntelligence() - $old->getItemAttribute(ATTR::INTELLIGENCE())->getValue());
				$pve->setMiningSpeed($pve->getMiningSpeed() - $old->getItemAttribute(ATTR::MINING_SPEED())->getValue());
				$pve->setStrength($pve->getStrength() - $old->getItemAttribute(ATTR::STRENGTH())->getValue());
				$pve->setMaxHealth($pve->getMaxHealth() - $old->getItemAttribute(ATTR::HEALTH())->getValue());
				$pve->setDefense($pve->getDefense() - $old->getItemAttribute(ATTR::DEFENSE())->getValue());
				$pve->setSpeed($pve->getSpeed() - $old->getItemAttribute(ATTR::SPEED())->getValue());
				$pve->setCritDamage($pve->getCritDamage() - $old->getItemAttribute(ATTR::CRITICAL_DAMAGE())->getValue());
				$pve->setCritChance($pve->getCritChance() - $old->getItemAttribute(ATTR::CRITICAL_CHANCE())->getValue());
				$pve->setFishingSpeed($pve->getFishingSpeed() - $old->getItemAttribute(ATTR::FISHING_SPEED())->getValue());
			}

			if($new instanceof ItemAttributeHolder){
				$pve->setIntelligence($pve->getIntelligence() + $new->getItemAttribute(ATTR::INTELLIGENCE())->getValue());
				$pve->setMiningSpeed($pve->getMiningSpeed() + $new->getItemAttribute(ATTR::MINING_SPEED())->getValue());
				$pve->setStrength($pve->getStrength() + $new->getItemAttribute(ATTR::STRENGTH())->getValue());
				$pve->setMaxHealth($pve->getMaxHealth() + $new->getItemAttribute(ATTR::HEALTH())->getValue());
				$pve->setDefense($pve->getDefense() + $new->getItemAttribute(ATTR::DEFENSE())->getValue());
				$pve->setSpeed($pve->getSpeed() + $new->getItemAttribute(ATTR::SPEED())->getValue());
				$pve->setCritDamage($pve->getCritDamage() + $new->getItemAttribute(ATTR::CRITICAL_DAMAGE())->getValue());
				$pve->setCritChance($pve->getCritChance() + $new->getItemAttribute(ATTR::CRITICAL_CHANCE())->getValue());
				$pve->setFishingSpeed($pve->getFishingSpeed() + $new->getItemAttribute(ATTR::FISHING_SPEED())->getValue());
			}
		} catch(Exception $e){
			Server::getInstance()->getLogger()->logException($e);
		}
	}

	public function onContentChange(Inventory $inventory, array $oldContents) : void{

		//NOOP
	}
}