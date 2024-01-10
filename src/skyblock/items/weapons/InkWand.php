<?php

declare(strict_types=1);

namespace skyblock\items\weapons;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use pocketmine\block\utils\DyeColor;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\DustParticle;
use pocketmine\world\sound\ExplodeSound;
use skyblock\entity\projectile\InkBombEntity;
use skyblock\items\ability\AreaDamageAbility;
use skyblock\items\ability\ShootProjectileAbility;
use skyblock\items\itemattribute\ItemAttributeInstance;
use skyblock\items\itemattribute\SkyBlockItemAttributes;
use skyblock\items\rarity\Rarity;
use skyblock\items\SkyBlockWeapon;
use skyblock\player\PvePlayer;

class InkWand extends SkyBlockWeapon implements ItemComponents {
	use ItemComponentsTrait;

	public function __construct(
		ItemIdentifier $identifier,
		string $name = 'Unknown'
	) {
		parent::__construct($identifier, $name);

		$this->initComponent(
			'ink_wand',
			new CreativeInventoryInfo(
				CreativeInventoryInfo::CATEGORY_EQUIPMENT,
				CreativeInventoryInfo::GROUP_SWORD
			)
		);

		$this->properties->setDescription([
			'§r§6Item Ability: Ink Bomb§l§e RIGHT CLICK',
			'§r§7Shoot an ink bomb in front of',
			'§r§7you dealing §a10,000§r§7 base damage',
			"§r§7and giving blindness\"",
			'§r§7Mana Cost: §b60',
			'§r§7Cooldown: §a30s'
		]);

		$this->properties->setRarity(Rarity::epic());

		$this->setItemAttribute(
			new ItemAttributeInstance(SkyBlockItemAttributes::DAMAGE(), 130)
		);
		$this->setItemAttribute(
			new ItemAttributeInstance(SkyBlockItemAttributes::STRENGTH(), 90)
		);
	}

	public function onClickAir(
		Player $player,
		Vector3 $directionVector,
		array &$returnedItems
	): ItemUseResult {
		(new ShootProjectileAbility(
			InkBombEntity::class,
			'§3Ink Bomb',
			60,
			30
		))->start($player, $this);

		return parent::onClickAir($player, $directionVector, $returnedItems);
	}

	public function onProjectileHitEvent(
		PvePlayer $player,
		ProjectileHitEvent $event
	): void {
		parent::onProjectileHitEvent($player, $event);

		$pos = $event->getEntity()->getPosition();
		$pos->getWorld()->addSound($pos, new ExplodeSound());

		$bb = $event
			->getEntity()
			->getBoundingBox()
			->expandedCopy(8, 8, 8);
		for ($i = 0; $i <= 20; $i++) {
			$x = mt_rand((int) $bb->minX, (int) $bb->maxX);
			$z = mt_rand((int) $bb->minZ, (int) $bb->maxZ);
			$y = mt_rand((int) $pos->y, (int) $bb->maxY);

			$pos->getWorld()->addParticle(
				new Vector3($x, $y, $z),
				new DustParticle(DyeColor::LIGHT_BLUE()->getRgbValue())
			);
		}

		(new AreaDamageAbility(
			3,
			10000,
			true,
			'ink_bomb_no_cd',
			0,
			0
		))->start($player, $this);
	}
}
