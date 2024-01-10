<?php

declare(strict_types=1);

namespace skyblock\items\weapons;

use customiesdevs\customies\item\component\ArmorComponent;
use customiesdevs\customies\item\component\IconComponent;
use customiesdevs\customies\item\component\ItemComponent;
use customiesdevs\customies\item\component\WearableComponent;
use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use skyblock\events\PlayerAttackPveEvent;
use skyblock\items\ability\AreaDamagePercentageAbility;
use skyblock\items\ability\TeleportAbility;
use skyblock\items\itemattribute\ItemAttributeInstance;
use skyblock\items\itemattribute\SkyBlockItemAttributes;
use skyblock\items\rarity\Rarity;
use skyblock\items\SkyBlockWeapon;
use skyblock\player\PvePlayer;
use skyblock\utils\PveUtils;

class AspectOfTheEndSword extends SkyBlockWeapon implements ItemComponents {
	use ItemComponentsTrait;

	public function __construct(
		ItemIdentifier $identifier,
		string $name = 'Unknown'
	) {
		parent::__construct($identifier, $name);

		$this->initComponent(
			'aspect_of_the_end',
			new CreativeInventoryInfo(
				CreativeInventoryInfo::CATEGORY_EQUIPMENT,
				CreativeInventoryInfo::GROUP_SWORD
			)
		);
		$this->addComponent(new IconComponent('minecraft:diamond_sword'));

		$this->properties->setDescription([
			"§r§6Item Ability: Instant Transmission§l§e RIGHT CLICK",
			"§r§7Teleport §a8§7 blocks ahead of",
			'§r§7you and gain §a+50 ' . PveUtils::getSpeed(),
			"§r§7for §a3 seconds.",
			"§r§7Mana Cost: §b50"
		]);

		$this->properties->setRarity(Rarity::rare());

		$this->setItemAttribute(
			new ItemAttributeInstance(SkyBlockItemAttributes::DAMAGE(), 100)
		);
		$this->setItemAttribute(
			new ItemAttributeInstance(SkyBlockItemAttributes::STRENGTH(), 100)
		);
	}

	public function onClickAir(
		Player $player,
		Vector3 $directionVector,
		array &$returnedItems
	): ItemUseResult {
		(new TeleportAbility(8, 'Instant Transmission', 1, 0))->start(
			$player,
			$this
		);

		return parent::onClickAir($player, $directionVector, $returnedItems);
	}
}
