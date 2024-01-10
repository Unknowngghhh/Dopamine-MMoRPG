<?php

declare(strict_types=1);

namespace skyblock\items\weapons;

use customiesdevs\customies\item\CreativeInventoryInfo;
use customiesdevs\customies\item\ItemComponents;
use customiesdevs\customies\item\ItemComponentsTrait;
use customiesdevs\customies\item\component\ChargeableComponent;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow as ArrowEntity;
use pocketmine\item\ItemIdentifier;
use skyblock\entity\PveEntity;
use skyblock\entity\projectile\SkyBlockArrow;
use skyblock\events\PlayerAttackEntityEvent;
use skyblock\items\components\ShooterComponent;
use skyblock\items\components\FrameComponent;
use skyblock\items\components\UseAnimationComponent;
use skyblock\items\components\ToolbarAnimComponent;
use skyblock\items\itemattribute\ItemAttributeInstance;
use skyblock\items\itemattribute\SkyBlockItemAttributes;
use skyblock\items\rarity\Rarity;
use skyblock\player\PvePlayer;

class RunaansBow extends SkyBlockBow implements ItemComponents{
	use ItemComponentsTrait;

	public function __construct(ItemIdentifier $identifier, string $name = "Unknown"){
		parent::__construct($identifier, $name);

		$this->initComponent("runaan_bow", new
		CreativeInventoryInfo(CreativeInventoryInfo::CATEGORY_EQUIPMENT,
		CreativeInventoryInfo::GROUP_SWORD));
		$this->addComponent(new ChargeableComponent(0.4));
		//$this->addComponent(new ShooterComponent());
		$this->addComponent(new FrameComponent(3));
		$this->addComponent(new UseAnimationComponent('bow'));
		$this->addComponent(new ToolbarAnimComponent(true));

		$this->properties->setRarity(Rarity::legendary());

		$this->properties->setDescription([
			"§r§6Item Ability: Triple Shot",
			"§r§7Shoots 3 arrows at a time! The 2",
			"§r§7extra arrows deal §a40%",
			"§r§7percent of the damage and home",
			"§r§7to targets.",
		]);

		$this->setCustomName("§r" . $this->getProperties()->getRarity()->getColor() . "Runaan's Bow");
		$this->setItemAttribute(new ItemAttributeInstance(SkyBlockItemAttributes::DAMAGE(), 120));
		$this->setItemAttribute(new ItemAttributeInstance(SkyBlockItemAttributes::STRENGTH(), 50));

		$this->resetLore();
	}

	public function onAttackPve(PvePlayer $player, PlayerAttackEntityEvent $event) : void{
		parent::onAttackPve($player, $event);

		$projectile = $event->getProjectile();

		if($projectile instanceof SkyBlockArrow){
			if($projectile->getOrigin() === "runaan"){
				$event->divideDamage(0.4, "runaan's arrow");
			}
		}
	}

	public function onShootArrow(PvePlayer $player, SkyBlockArrow $arrow) : void{
		parent::onShootArrow($player, $arrow);

		if(($e = $player->getWorld()->getNearestEntity($player->getPosition(), 30, PveEntity::class))){
			for($i = 1; $i <= 2; $i++){
				$arrow = new SkyBlockArrow(Location::fromObject($player->getEyePos(), $player->getWorld()), $player, $arrow->isCritical());
				$arrow->setPickupMode(ArrowEntity::PICKUP_CREATIVE);
				$arrow->setSourceItem($this);
				$arrow->lookAt($e->getPosition());
				$arrow->setMotion($arrow->getDirectionVector()->multiply(3));
				$arrow->setOrigin("runaan");
				$arrow->spawnToAll();
			}
		}
	}
}