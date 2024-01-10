<?php

declare(strict_types=1);

namespace skyblock\items\armor\arachne;

use pocketmine\item\Item;
use skyblock\items\armor\ArmorSet;
use skyblock\items\itemattribute\ItemAttributeInstance;
use skyblock\items\itemattribute\SkyBlockItemAttributes;
use skyblock\items\rarity\Rarity;
use skyblock\items\SkyblockItems;
use skyblock\player\PvePlayer;
use skyblock\traits\HandlerTrait;
use skyblock\utils\PveUtils;

class ArachneSet extends ArmorSet {
	use HandlerTrait;

	private array $data = [];

	const TAG_ARACHNES_STACK = 'tag_arachnes_stack';

	public function getItemAttributes(string $piece): array {
		$arr = [];

		switch ($piece) {
			case self::PIECE_HELMET:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					40
				);
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::HEALTH(),
					55
				);
				break;
			case self::PIECE_CHESTPLATE:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					60
				);
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::HEALTH(),
					85
				);
				break;

			case self::PIECE_LEGGINGS:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					50
				);
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::HEALTH(),
					75
				);
				break;
			case self::PIECE_BOOTS:
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::DEFENSE(),
					30
				);
				$arr[] = new ItemAttributeInstance(
					SkyBlockItemAttributes::HEALTH(),
					45
				);
				break;
		}

		return $arr;
	}

	public function onWear(PvePlayer $player): void {
		parent::onWear($player);
		$health = 0;
		$def = 0;
		foreach ($player->getArmorInventory()->getContents() as $content) {
			$stat = $this->getStats($content);

			if ($stat === 0) {
				continue;
			}

			$health += $stat;
			$def += $stat;

			$player
				->getPveData()
				->setDefense($player->getPveData()->getDefense() + $stat);
			$player
				->getPveData()
				->setMaxHealth($player->getPveData()->getMaxHealth() + $stat);
		}

		$this->data[$player->getName()] = $health;
	}

	public function onTakeoff(PvePlayer $player): void {
		parent::onTakeoff($player);

		if (isset($this->data[$player->getName()])) {
			$stat = $this->data[$player->getName()];
			$player
				->getPveData()
				->setDefense($player->getPveData()->getDefense() - $stat);
			$player
				->getPveData()
				->setMaxHealth($player->getPveData()->getMaxHealth() - $stat);

			unset($this->data[$player->getName()]);
		}
	}

	public function onEnable(): void {
		ArmorSet::registerSet($this);
	}

	public function getAbilities(): array {
		return [];
	}

	public function getIdentifier(): string {
		return 'arachne_set';
	}

	public function getName(string $piece = null): string {
		return "§r§3Arachne's " . $piece;
	}

	public function getLore(Item $item): array {
		$stack = $item->getNamedTag()->getInt(self::TAG_ARACHNES_STACK, 1);

		$item->getNamedTag()->setInt(self::TAG_ARACHNES_STACK, $stack);
		$item->makeUnique();

		$stat = $this->getStats($item);

		return [
			'§r§l§3Set Bonus: §3Stackable Strength',
			" §r§a+$stat " . PveUtils::getHealth(),
			" §r§a+$stat " . PveUtils::getDefense(),
			'§r',
			"§r§3§lSpecial Ability: Arachne's Faithful ($stack/8)",
			' §r§3Stacking multiple armor pieces of this',
			"§r§l§3   §r§3will increase the §l\"§3Stackable Strength\"",
			'§r§l§3   §r§3stats.'
		];
	}

	public function getStats(Item $item): int {
		$stack = $item->getNamedTag()->getInt(self::TAG_ARACHNES_STACK, 1);

		return match ($stack) {
			1, 2 => 5,
			3 => 10,
			4 => 20,
			5 => 35,
			6 => 50,
			7 => 70,
			8 => 100,
			0 => 0
		};
	}

	public function getPieceItems(): array {
		return [
			self::PIECE_BOOTS => SkyblockItems::ARACHNE_BOOTS(),
			self::PIECE_HELMET => SkyblockItems::ARACHNE_HELMET(),
			self::PIECE_LEGGINGS => SkyblockItems::ARACHNE_LEGGINGS(),
			self::PIECE_CHESTPLATE => SkyblockItems::ARACHNE_CHESTPLATE()
		];
	}

	public function getRarity(): Rarity {
		return Rarity::rare();
	}
}
