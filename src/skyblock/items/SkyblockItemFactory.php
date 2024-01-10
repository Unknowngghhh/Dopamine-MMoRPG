<?php

declare(strict_types=1);

namespace skyblock\items;

use customiesdevs\customies\item\CustomiesItemFactory;
use pocketmine\block\BlockFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\CreativeInventory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use skyblock\items\armor\arachne\ArachneBoots;
use skyblock\items\armor\arachne\ArachneChestplate;
use skyblock\items\armor\arachne\ArachneHelmet;
use skyblock\items\armor\arachne\ArachneLeggings;
use skyblock\items\armor\miner\MinerBoots;
use skyblock\items\armor\miner\MinerChestplate;
use skyblock\items\armor\miner\MinerHelmet;
use skyblock\items\armor\miner\MinerLeggings;
use skyblock\items\armor\speedster\SpeedsterBoots;
use skyblock\items\armor\speedster\SpeedsterChestplate;
use skyblock\items\armor\speedster\SpeedsterHelmet;
use skyblock\items\armor\speedster\SpeedsterLeggings;
use skyblock\items\armor\zombie\ZombieBoots;
use skyblock\items\armor\zombie\ZombieChestplate;
use skyblock\items\armor\zombie\ZombieHelmet;
use skyblock\items\armor\zombie\ZombieLeggings;
use skyblock\items\crafting\SkyBlockEnchantedItem;
use skyblock\items\itemattribute\ItemAttributeInstance;
use skyblock\items\itemattribute\SkyBlockItemAttributes;
use skyblock\items\misc\SkyblockMenuItem;
use skyblock\items\rarity\Rarity;
//use skyblock\items\tools\GraplingHook;
use skyblock\items\tools\JungleAxe;
use skyblock\items\weapons\AspectOfTheEndSword;
use skyblock\items\weapons\DreadlordSword;
use skyblock\items\weapons\InkWand;
use skyblock\items\weapons\RogueSword;
use skyblock\items\weapons\RunaansBow;
use skyblock\items\weapons\YetiSword;
use skyblock\misc\recipes\RecipesHandler;
use skyblock\traits\HandlerTrait;
use skyblock\traits\AwaitStdTrait;
use skyblock\Main;
use SOFe\AwaitGenerator\Await;

class SkyblockItemFactory {
	use HandlerTrait;
	use AwaitStdTrait;

	public function onEnable(): void {
		$this->registerEquipment();
		$this->registerArmor();
		$this->registerMisc();
	}

	public function registerMisc(): void {
		/*$this->register(
			SkyblockMenuItem::class,
			'skyblock_menu',
			'§fSkyBlock Menu'
		);*/
	}

	public function registerArmor(): void {
		$this->register(MinerBoots::class, 'miner_boots');
		$this->register(MinerLeggings::class, 'miner_leggings');
		$this->register(MinerChestplate::class, 'miner_chestplate');
		$this->register(MinerHelmet::class, 'miner_helmet');

		$this->register(SpeedsterBoots::class, 'speedster_boots');
		$this->register(SpeedsterLeggings::class, 'speedster_leggings');
		$this->register(SpeedsterChestplate::class, 'speedster_chestplate');
		$this->register(SpeedsterHelmet::class, 'speedster_helmet');

		$this->register(ArachneBoots::class, 'arachne_boots');
		$this->register(ArachneLeggings::class, 'arachne_leggings');
		$this->register(ArachneChestplate::class, 'arachne_chestplate');
		$this->register(ArachneHelmet::class, 'arachne_helmet');

		$this->register(ZombieBoots::class, 'zombie_boots');
		$this->register(ZombieLeggings::class, 'zombie_leggings');
		$this->register(ZombieChestplate::class, 'zombie_chestplate');
		$this->register(ZombieHelmet::class, 'zombie_helmet');
	}

	public function registerEquipment(): void {
		$this->register(
			RogueSword::class,
			'rogue_sword',
			'Rogue Sword'
		);
		$this->register(
			YetiSword::class,
			'yeti_sword',
			'Yeti Sword'
		);
		$this->register(
			DreadlordSword::class,
			'dreadlord_sword',
			'Dreadlord Sword'
		);
		$this->register(
			AspectOfTheEndSword::class,
			'aspect_of_the_end_sword',
			'Aspect Of The End Sword'
		);

		/*$this->register(
			GraplingHook::class,
			'grapling_hook',
			'§aGrapling Hook'
		);*/

		$this->register(JungleAxe::class, 'jungle_axe', 'Jungle Axe');

		$this->register(RunaansBow::class, 'runaans_bow', "Runaan's Bow");

		$this->register(InkWand::class, 'ink_wand', 'Ink Wand');
	}

	public function get(string $id, int $count = 1): SkyblockItem {
		$item = clone CustomiesItemFactory::getInstance()->get($id, $count);
		if (!$item instanceof SkyblockItem) {
			throw new \InvalidArgumentException(
				$item::class . ' is not a SkyblockItem.'
			);
		}

		return $item;
	}

	public function getOrNull(int $id, int $count = 0): ?SkyblockItem {
		$item = CustomiesItemFactory::getInstance()->get($id, $count);
		if (!$item instanceof SkyblockItem) {
			return null;
		}

		return $item;
	}

	public function register(
		string $class,
		string $id,
		?string $name = null,
		?string $exceptionId = null
	): void {
		$identifier = 'hypertex:' . $id;

		CustomiesItemFactory::getInstance()->registerItem(
			$class,
			$identifier,
			$name ?? $id
		);
		/*SkyblockItems::registerPublic(
			$exceptionId ?? $id,
			CustomiesItemFactory::getInstance()->get($identifier)
		);*/
		/*$data = file_get_contents(
			Main::getInstance()->getDataFolder() . 'test.txt'
		); //GENERATE CONSTANTS FOR SKYBLOCKITEMS::
		if ($data === false) {
			$data = '';
		}
		$data .=
			"self::register('". $id . "', \$factory->get('" . $id . "'));" . "\n";
		file_put_contents(
			Main::getInstance()->getDataFolder() . 'test.txt',
			$data
		);*/
	}
}
