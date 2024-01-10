<?php

declare(strict_types=1);

namespace skyblock\items;

use pocketmine\color\Color;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\item\Armor;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

class ItemEditor {
	public static function setNotTradable(Item $item): void {
		$item->getNamedTag()->setByte('ban_trading', 1);
	}

	public static function isTradable(Item $item): bool {
		return $item->getNamedTag()->getByte('ban_trading', 0) === 0;
	}

	public static function setNotAuctionable(Item $item): void {
		$item->getNamedTag()->setByte('ban_auction', 1);
	}

	public static function isAuctionable(Item $item): bool {
		return $item->getNamedTag()->getByte('ban_auction', 0) === 0;
	}

	public static function glow(Item $item): Item {
		return $item->addEnchantment(
			new EnchantmentInstance(
				EnchantmentIdMap::getInstance()->fromId(138)
			)
		);
	}

	public static function isGlowing(Item $item): bool {
		return $item->hasEnchantment(
			EnchantmentIdMap::getInstance()->fromId(138)
		);
	}

	public static function removeGlow(Item $item): Item {
		return $item->removeEnchantment(
			EnchantmentIdMap::getInstance()->fromId(138)
		);
	}

	public static function addUniqueID(Item $item): void {
		$item->getNamedTag()->setString('uniqueid', uniqid());
	}

	public static function getUniqueId(Item $item): ?string {
		return ($val = $item->getNamedTag()->getString('UniqueId', '')) === ''
			? null
			: $val;
	}

	public static function hasUniqueId(Item $item): bool {
		return $item->getNamedTag()->getString('UniqueId', 'error38') !==
			'error38';
	}

	public static function getDescription(Item $item): string {
		return $item->getNamedTag()->getString('description', '');
	}

	public function setDescription(Item $item, string $description): void {
		$item->getNamedTag()->setString('description', $description);
	}

	public static function isProtected(Item $item): bool {
		return $item->getNamedTag()->getByte('protected', 0) === 1;
	}

	public static function setProtected(Item $item, bool $value = true): void {
		if ($value === true) {
			$item->getNamedTag()->setByte('protected', (int) $value);
		} else {
			$item->getNamedTag()->removeTag('protected');
		}
	}

	public static function isEnhanced(Item $item): bool {
		return (bool) $item->getNamedTag()->getByte('enhanced', 0);
	}

	public static function setEnhanced(Item $item, bool $value = true): void {
		$item->getNamedTag()->setByte('enhanced', (int) $value);
	}

	public static function updateCosmetics(Item $item): void {
		$item->resetLore();
	}
}
