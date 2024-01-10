<?php

declare(strict_types=1);

namespace skyblock\items\weapons;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use skyblock\items\ability\RogueAbility;
use skyblock\items\itemattribute\ItemAttributeInstance;
use skyblock\items\itemattribute\SkyBlockItemAttributes;
use skyblock\items\rarity\Rarity;
use skyblock\items\SkyBlockWeapon;
use skyblock\player\PvePlayer;

class RogueSword extends SkyBlockWeapon implements ItemComponents {
	use ItemComponentsTrait;

	public function __construct(
		ItemIdentifier $identifier,
		string $name = 'Unknown'
	) {
		parent::__construct($identifier, $name);

		$this->initComponent(
			'rogue_sword',
			new CreativeInventoryInfo(
				CreativeInventoryInfo::CATEGORY_EQUIPMENT,
				CreativeInventoryInfo::GROUP_SWORD
			)
		);

		$this->properties->setDescription([
			"§r§6Item Ability: Speed Boost§l§e RIGHT CLICK",
			"§r§7Gain §f+100 Movement Speed §7for",
			"§r§730 seconds",
			"§r§7Mana Cost: §b50"
		]);

		$this->properties->setRarity(Rarity::common());

		$this->setItemAttribute(
			new ItemAttributeInstance(SkyBlockItemAttributes::DAMAGE(), 20)
		);
	}

	public function onClickAir(
		Player $player,
		Vector3 $directionVector,
		array &$returnedItems
	): ItemUseResult {
		parent::onClickAir($player, $directionVector, $returnedItems);

		assert($player instanceof PvePlayer);

		(new RogueAbility(100, '§bRogue Sword', 50, 30))->start($player, $this);

		return ItemUseResult::SUCCESS();
	}
}
