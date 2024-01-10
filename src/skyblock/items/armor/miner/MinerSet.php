<?php

declare(strict_types=1);

namespace skyblock\items\armor\miner;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\item\Item;
use skyblock\items\armor\ArmorSet;
use skyblock\items\itemattribute\ItemAttributeInstance;
use skyblock\items\itemattribute\SkyBlockItemAttributes;
use skyblock\items\rarity\Rarity;
use skyblock\items\sets\SpecialSet;
use skyblock\items\SkyblockItems;
use skyblock\player\PvePlayer;
use skyblock\traits\HandlerTrait;
use skyblock\utils\PveUtils;

class MinerSet extends ArmorSet {
	use HandlerTrait;

	public function onEnable(): void {
		ArmorSet::registerSet($this);
	}

	public function getItemAttributes(string $piece): array {
		$arr = [];

		switch ($piece) {
			case self::PIECE_HELMET:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					15
				);
				break;
			case self::PIECE_CHESTPLATE:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					40
				);
				break;

			case self::PIECE_LEGGINGS:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					30
				);
				break;
			case self::PIECE_BOOTS:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					15
				);
				break;
		}

		return $arr;
	}

	public function onWear(PvePlayer $player): void {
		parent::onWear($player);

		$player
			->getPveData()
			->setMiningSpeed($player->getPveData()->getMiningSpeed() + 100);
	}

	public function onTakeoff(PvePlayer $player): void {
		parent::onTakeoff($player);

		$player
			->getPveData()
			->setMiningSpeed($player->getPveData()->getMiningSpeed() - 100);
	}

	public function getAbilities(): array {
		return [];
	}

	public function getIdentifier(): string {
		return 'miner_set';
	}

	public function getName(string $piece = null): string {
		return '§r§aMiners Outfit ' . $piece;
	}

	public function getLore(Item $item): array {
		return [
			'§r§l§aSet Bonus: §aApace Digger',
			'§r§l§a §r§a+100 ' . PveUtils::getMiningSpeed()
		];
	}

	public function getPieceItems(): array {
		return [
			self::PIECE_BOOTS => SkyblockItems::MINER_BOOTS(),
			self::PIECE_HELMET => SkyblockItems::MINER_HELMET(),
			self::PIECE_LEGGINGS => SkyblockItems::MINER_LEGGINGS(),
			self::PIECE_CHESTPLATE => SkyblockItems::MINER_CHESTPLATE()
		];
	}

	public function getRarity(): Rarity {
		return Rarity::uncommon();
	}
}
