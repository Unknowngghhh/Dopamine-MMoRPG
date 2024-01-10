<?php

declare(strict_types=1);

namespace skyblock\items\weapons;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\BlockBreakParticle;
use skyblock\items\ability\ParticleBeamAbility;
use skyblock\items\itemattribute\ItemAttributeInstance;
use skyblock\items\itemattribute\SkyBlockItemAttributes;
use skyblock\items\rarity\Rarity;
use skyblock\items\SkyBlockWeapon;
use skyblock\player\PvePlayer;

class YetiSword extends SkyBlockWeapon implements ItemComponents{
	use ItemComponentsTrait;

	public function __construct(ItemIdentifier $identifier, string $name = "Unknown"){
		parent::__construct($identifier, $name);

		$this->initComponent("yeti_sword", new CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_EQUIPMENT, CreativeInventoryInfo::GROUP_SWORD));

		$this->properties->setDescription([
			"§r§6Item Ability: Terrain Toss§l§e RIGHT CLICK",
			"§r§7Throws a chunk of terrain in the",
			"§r§7in the direction you are facing! Deals",
			"§r§7up to §f15,000 §r§7damage",
			"§r§7Mana Cost: §b250",
		]);

		$this->properties->setRarity(Rarity::legendary());

		$this->setItemAttribute(new ItemAttributeInstance(SkyBlockItemAttributes::DAMAGE(), 150));
		$this->setItemAttribute(new ItemAttributeInstance(SkyBlockItemAttributes::STRENGTH(), 170));
		$this->setItemAttribute(new ItemAttributeInstance(SkyBlockItemAttributes::INTELLIGENCE(), 50));
	}

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
		parent::onClickAir($player, $directionVector, $returnedItems);

		assert($player instanceof PvePlayer);

		$a = new ParticleBeamAbility(
			new BlockBreakParticle(VanillaBlocks::COBBLESTONE()),
			15000,
			10,
			$player->getPosition(),
			null,
			"§6Terrain Toss",
			250,
			1,
		);

		$a->start($player, $this);

		return ItemUseResult::SUCCESS();
	}
}