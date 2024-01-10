<?php

declare(strict_types=1);

namespace skyblock\items\armor\speedster;

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

class SpeedsterSet extends ArmorSet {
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
					65
				);
				break;
			case self::PIECE_CHESTPLATE:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					120
				);
				break;

			case self::PIECE_LEGGINGS:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					95
				);
				break;
			case self::PIECE_BOOTS:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					70
				);
				break;
		}

		return $arr;
	}

	public function onWear(PvePlayer $player): void {
		parent::onWear($player);

		$player->getPveData()->setSpeed($player->getPveData()->getSpeed() + 20);
	}

	public function onTakeoff(PvePlayer $player): void {
		parent::onTakeoff($player);

		$player->getPveData()->setSpeed($player->getPveData()->getSpeed() - 20);
	}

	public function getAbilities(): array {
		return [];
	}

	public function getIdentifier(): string {
		return 'speedster_set';
	}

	public function getName(string $piece = null): string {
		return '§r§5Speedster ' . $piece;
	}

	public function getLore(Item $item): array {
		return [
			'§r§l§5Set Bonus: §5Bonus Speed',
			'§r§l§5 §r§5+20 ' . PveUtils::getSpeed() . ' §r§l§5«'
		];
	}

	public function getPieceItems(): array {
		return [
			self::PIECE_BOOTS => SkyblockItems::SPEEDSTER_BOOTS(),
			self::PIECE_HELMET => SkyblockItems::SPEEDSTER_HELMET(),
			self::PIECE_LEGGINGS => SkyblockItems::SPEEDSTER_LEGGINGS(),
			self::PIECE_CHESTPLATE => SkyblockItems::SPEEDSTER_CHESTPLATE()
		];
	}

	public function getRarity(): Rarity {
		return Rarity::epic();
	}
}
