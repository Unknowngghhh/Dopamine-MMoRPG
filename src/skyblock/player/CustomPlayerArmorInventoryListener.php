<?php

declare(strict_types=1);

namespace skyblock\player;

use Exception;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryListener;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\Server;
use skyblock\items\armor\ArmorSet;
use skyblock\items\itemattribute\ItemAttributeHolder;
use skyblock\items\itemattribute\SkyBlockItemAttributes as ATTR;
use skyblock\items\ItemEditor;
use skyblock\items\PvEItemEditor;

//TODO: same for held item so normal inventory
class CustomPlayerArmorInventoryListener implements InventoryListener {
	public function __construct(private PvePlayer $player) {
	}

	public function onSlotChange(
		Inventory $inventory,
		int $slot,
		Item $oldItem
	): void {
		$newItem = $inventory->getItem($slot);

		if ($oldItem->equals($newItem)) {
			return;
		}

		ArmorSet::check($this->player);

		$this->checkPvE($oldItem, $newItem);
	}

	public function checkPvE(Item $old, Item $new): void {
		$pve = $this->player->getPveData();

		try {
			if ($old instanceof ItemAttributeHolder) {
				$pve->setIntelligence(
					$pve->getIntelligence() -
						$old->getItemAttribute(ATTR::INTELLIGENCE())->getValue()
				);
				$pve->setMiningSpeed(
					$pve->getMiningSpeed() -
						$old->getItemAttribute(ATTR::MINING_SPEED())->getValue()
				);
				$pve->setStrength(
					$pve->getStrength() -
						$old->getItemAttribute(ATTR::STRENGTH())->getValue()
				);
				$pve->setMaxHealth(
					$pve->getMaxHealth() -
						$old->getItemAttribute(ATTR::HEALTH())->getValue()
				);
				$pve->setDefense(
					$pve->getDefense() -
						$old->getItemAttribute(ATTR::DEFENSE())->getValue()
				);
				$pve->setSpeed(
					$pve->getSpeed() -
						$old->getItemAttribute(ATTR::SPEED())->getValue()
				);
				$pve->setCritDamage(
					$pve->getCritDamage() -
						$old
							->getItemAttribute(ATTR::CRITICAL_DAMAGE())
							->getValue()
				);
				$pve->setCritChance(
					$pve->getCritChance() -
						$old
							->getItemAttribute(ATTR::CRITICAL_CHANCE())
							->getValue()
				);
				$pve->setFishingSpeed(
					$pve->getFishingSpeed() -
						$old
							->getItemAttribute(ATTR::FISHING_SPEED())
							->getValue()
				);
			}

			if ($new instanceof ItemAttributeHolder) {
				$pve->setIntelligence(
					$pve->getIntelligence() +
						$new->getItemAttribute(ATTR::INTELLIGENCE())->getValue()
				);
				$pve->setMiningSpeed(
					$pve->getMiningSpeed() +
						$new->getItemAttribute(ATTR::MINING_SPEED())->getValue()
				);
				$pve->setStrength(
					$pve->getStrength() +
						$new->getItemAttribute(ATTR::STRENGTH())->getValue()
				);
				$pve->setMaxHealth(
					$pve->getMaxHealth() +
						$new->getItemAttribute(ATTR::HEALTH())->getValue()
				);
				$pve->setDefense(
					$pve->getDefense() +
						$new->getItemAttribute(ATTR::DEFENSE())->getValue()
				);
				$pve->setSpeed(
					$pve->getSpeed() +
						$new->getItemAttribute(ATTR::SPEED())->getValue()
				);
				$pve->setCritDamage(
					$pve->getCritDamage() +
						$new
							->getItemAttribute(ATTR::CRITICAL_DAMAGE())
							->getValue()
				);
				$pve->setCritChance(
					$pve->getCritChance() +
						$new
							->getItemAttribute(ATTR::CRITICAL_CHANCE())
							->getValue()
				);
				$pve->setFishingSpeed(
					$pve->getFishingSpeed() +
						$new
							->getItemAttribute(ATTR::FISHING_SPEED())
							->getValue()
				);
			}
		} catch (Exception $e) {
			Server::getInstance()
				->getLogger()
				->logException($e);
		}
	}

	/**
	 * @param Inventory $inventory
	 * @param Item[]     $oldContents
	 */
	public function onContentChange(
		Inventory $inventory,
		array $oldContents
	): void {
		ArmorSet::check($this->player);

		foreach ($oldContents as $k => $oldItem) {
			if ($oldItem->isNull()) {
				continue;
			}

			$newItem = $inventory->getItem($k);

			$this->checkPvE($oldItem, $newItem);
		}
	}
}
