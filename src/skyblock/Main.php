<?php

declare(strict_types=1);

namespace skyblock;

use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\utils\SingletonTrait;
use pocketmine\plugin\PluginBase;
use skyblock\items\SkyblockItemFactory;
use skyblock\entity\EntityHandler;
use skyblock\listeners\EventListener;
use SOFe\AwaitStd\AwaitStd;

class Main extends PluginBase {
	public static Main $instance;

	public AwaitStd $std;

	public function onEnable(): void {
		self::$instance = $this;
		$this->getServer()
		->getPluginManager()
		->registerEvents(new EventListener(), Main::getInstance());
		EnchantmentIdMap::getInstance()->register(
			138,
			new Enchantment(
				'GlowItem',
				Rarity::MYTHIC,
				ItemFlags::ALL,
				ItemFlags::ALL,
				5,
			),
		);
		$this->std = AwaitStd::init($this);
		new EntityHandler();
		PveHandler::initialise();
		SkyblockItemFactory::initialise();
	}

	public function getStd(): AwaitStd {
		return $this->std;
	}

	public static function getInstance(): self {
		return self::$instance;
	}
}
