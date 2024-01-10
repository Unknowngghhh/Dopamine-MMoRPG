<?php

declare(strict_types=1);

namespace skyblock\items\armor\zombie;

use pocketmine\item\Item;
use skyblock\items\armor\ArmorSet;
use skyblock\items\itemattribute\ItemAttributeInstance;
use skyblock\items\itemattribute\SkyBlockItemAttributes;
use skyblock\items\rarity\Rarity;
use skyblock\items\SkyblockItems;
use skyblock\traits\HandlerTrait;
use skyblock\utils\PveUtils;

class ZombieSet extends ArmorSet {
	use HandlerTrait;

	public function onEnable(): void {
		ArmorSet::registerSet($this);
	}

	public function getItemAttributes(string $piece): array {
		$arr = [new ItemAttributeInstance(SkyBlockItemAttributes::SPEED(), 15)];

		switch ($piece) {
			case self::PIECE_HELMET:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					25
				);
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::HEALTH(),
					120
				);
				break;
			case self::PIECE_CHESTPLATE:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					40
				);
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::HEALTH(),
					200
				);
				break;

			case self::PIECE_LEGGINGS:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					30
				);
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::HEALTH(),
					160
				);
				break;
			case self::PIECE_BOOTS:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					25
				);
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::HEALTH(),
					120
				);
				break;
		}

		return $arr;
	}

	public function getAbilities(): array {
		return [new ZombieSetAbility($this)];
	}

	public function getIdentifier(): string {
		return 'zombie_set';
	}

	public function getName(string $piece = null): string {
		return '§r§5Zombie ' . $piece;
	}

	public function getLore(Item $item): array {
		return [
			//todo ability of this
			'§r§l§5Set Bonus: §5Projectile Absorption',
			'§r§l§5 » §r§5When hit by a projectile, Heal §a+10 ' .
			PveUtils::getHealth(),
			'§r§l§5   §r§5for 5 seconds'
		];
	}

	public function getRarity(): Rarity {
		return Rarity::epic();
	}

	public function getPieceItems(): array {
		return [
			self::PIECE_BOOTS => SkyblockItems::ZOMBIE_BOOTS(),
			self::PIECE_HELMET => SkyblockItems::ZOMBIE_HELMET(),
			self::PIECE_LEGGINGS => SkyblockItems::ZOMBIE_LEGGINGS(),
			self::PIECE_CHESTPLATE => SkyblockItems::ZOMBIE_CHESTPLATE()
		];
	}
}
