<?php

namespace skyblock\menus\items;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use skyblock\forms\commands\HotspotForm;
use skyblock\items\special\types\CarrotCandy;
use skyblock\menus\AetherMenu;
use skyblock\menus\collection\CollectionMenu;
use skyblock\menus\commands\PotionsMenu;
use skyblock\menus\profile\ProfileManagementMenu;
use skyblock\menus\recipe\CraftingMenu;
use skyblock\menus\recipe\RecipeByClassMenu;
use skyblock\menus\recipe\RecipesMenu;
use skyblock\menus\skills\SkillsBrowseMenu;
use skyblock\menus\skills\SkillsMenu;
use skyblock\menus\trades\TradesViewMenu;
use skyblock\misc\pve\fishing\HotspotHandler;
use skyblock\misc\recipes\RecipesHandler;
use skyblock\player\AetherPlayer;
use skyblock\player\CachedPlayerPveData;
use skyblock\player\ranks\BaseRank;
use skyblock\sessions\Session;
use skyblock\traits\AwaitStdTrait;
use skyblock\utils\PveUtils;
use skyblock\utils\Utils;
use SOFe\AwaitGenerator\Await;

class SkyblockMenu extends AetherMenu {
	use AwaitStdTrait;

	public function __construct(private AetherPlayer $player, private ?AetherMenu $aetherMenu = null){
		parent::__construct();
	}

	public function constructMenu() : InvMenu{
		$menu = $this->aetherMenu ? $this->aetherMenu->getMenu() : InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
		$menu->getInventory()->clearAll();
		$menu->setName("§r§7Skyblock Menu");
		
		$inv = $menu->getInventory();
		
		$inv->setItem(19, VanillaItems::DIAMOND_SWORD()->setCustomName("§r§l§a» Skills «")->setLore(["§r§7View your skill progression and", "§r§7rewards!", "§r", "§r§eClick to view."]));
		$inv->setItem(20, $this->getCollectionItem());
		$inv->setItem(21, $this->getRecipeItem());
		$inv->setItem(22, $this->getTradesItem());
		$inv->setItem(23, $this->getQuestLog());
		
		$inv->setItem(29, $this->getActiveEffects());
		$inv->setItem(30, $this->getPets());
		$inv->setItem(31, $this->getCraftTable());
		$inv->setItem(53, $this->getAccessoryItem());

		$inv->setItem(13, $this->getStatsItem());

		$inv->setItem(8, $this->getHotspotItem());
		$inv->setItem(48, $this->getProfileItem());

		$i = VanillaBlocks::STAINED_GLASS_PANE()->setColor(DyeColor::GRAY())->asItem()->setCustomName(" ");
		foreach($menu->getInventory()->getContents(true) as $k => $v){
			if($v->isNull()){
				$menu->getInventory()->setItem($k, $i);
			}
		}


		return $menu;
	}

	public function onReadonlyTransaction(InvMenuTransaction $transaction) : void{
		$slot = $transaction->getAction()->getSlot();
		/** @var AetherPlayer $player */
		$player = $transaction->getPlayer();

		if($slot === 8){
			$player->removeCurrentWindow();

			Await::f2c(function() use($player) {
				yield $this->getStd()->sleep(20);

				$player->sendForm(new HotspotForm());
			});
		}

		if($slot === 19){
			(new SkillsBrowseMenu($player, $this))->send($player);
		}

		if($slot === 20){
			(new CollectionMenu($player, $this))->send($player);
		}

		if($slot === 21){
			(new RecipeByClassMenu($player, $this))->send($player);
		}

		if($slot === 29){
			(new PotionsMenu($player, $this))->send($player);
		}

		if($slot === 30){
			(new PetCollectionMenu($player, $this))->send($player);
		}

		if($slot === 53){
			(new AccessoryBagMenu($player, $this))->send($player);
		}

		if($slot === 22){
			(new TradesViewMenu($player, $this))->send($player);
		}

		if($slot === 48){
			(new ProfileManagementMenu($player, $this))->send($player);
		}

		if($slot === 31){
			(new CraftingMenu($player, $this))->send($player);
		}
	}

	public function getAccessoryItem(): Item {
		$item = VanillaBlocks::ELEMENT_ACTINIUM()->asItem();
		$item->setCustomName("§r§a§l» Accessories Bag « §r§7(Click)");

		$item->setLore([
            "§r",
			"§r§7A §aSpecial Bag §7that can hold",
			"§r§7Talismans, Rings, Artifacts, Relics, and",
			"§r§7Orbs within it. All will still work",
			"§r§7while in this bag!",
			"§r",
			"§r§eClick to open!"
		]);

		return $item;
	}

	public function getStatsItem(): Item {
		$data = $this->player->getPveData();

		$item = VanillaItems::DRAGON_HEAD();
		$item->setCustomName("§r§a§l» Your Profile « §r§7(Click)");
		$item->setLore([
			"§r§7View your equipment, stats,",
			"§r§7and more!",
			"§r",
			"§r§6§l» §r" . PveUtils::getHealth() . "§6: {$data->getHealth()}",
			"§r§6§l» §r" . PveUtils::getDefense() . "§6: {$data->getDefense()}",
			"§r§6§l» §r" . PveUtils::getStrength() . "§6: {$data->getStrength()}",
			"§r§6§l» §r" . PveUtils::getSpeed() . "§6: {$data->getSpeed()}",
			"§r§6§l» §r" . PveUtils::getCritChance() . "§6: {$data->getCritChance()}%",
			"§r§6§l» §r" . PveUtils::getCritDamage() . "§6: {$data->getCritDamage()}%",
			"§r§6§l» §r" . PveUtils::getIntelligence() . "§6: {$data->getIntelligence()}",
			"§r§6§l» §r" . PveUtils::getMiningSpeed() . "§6: {$data->getMiningSpeed()}",
			"§r§6§l» §r" . "§6Mining Fortune" . "§6: {$data->getMiningFortune()}",
			"§r§6§l» §r" . "§6Farming Fortune" . "§6: {$data->getFarmingFortune()}",
			"§r§6§l» §r" . "§6Foraging Fortune" . "§6: {$data->getForagingFortune()}",
		]);

		return $item;
	}

	public function getCollectionItem(): Item {
		$item = VanillaItems::PAINTING()->setCustomName("§r§l§a» Collection «");
		$item->setLore(
			[
				"§r§7View all of the items available",
				"§r§7in SkyBlock. Collect more of an item",
				"§r§7to unlock rewards on your way to",
				"§r§8becoming the master of SkyBlock!.",
				"§r",
				"§r§eClick to view!"
			]);
		return $item;
	}

	public function getProfileItem(): Item {
		$session = new Session($this->player);
		$rank = $session->getTopRank(true);


		$item = ItemFactory::getInstance()->get(ItemIds::NAME_TAG)->setCustomName("§r§l§a» Profile Management «");
		$item->setLore(
			[
				"§r§7You can have multiple SkyBlock",
				"§r§7profiles at the same time.",
				"§r§7",
				"§r§7Each profile has its own island,",
				"§r§7inventory, quest log...",
				"§r",
				"§r§7Profiles: §e" . count($this->player->getProfileIds()) . "§6/§e" . (2 + $rank->getExtraProfiles()),
				"§r§7Playing on: §a" . $this->player->getCurrentProfile()->getName(),
				"§r§7",
				"§r§bPlay with friends using /coop!",
				"§r",
				"§r§eClick to manage!"
			]);
		return $item;
	}

	public function getHotspotItem(): Item {
		$boost = HotspotHandler::getInstance()->getBoost();
		$item = VanillaBlocks::ELEMENT_HOLMIUM()->asItem();
		$item->setCustomName("§r§l§a» Hotspot «");
		$item->setLore([
            "§r",
			"§r§7Hotspots are Fishing Areas that can",
			"§r§7happen in the Spawn World or a City.",
            "§r",
			"§r§7Fishing in a Hotspot Area grants bonus",
			"§r§7XP or chances.",
			"§r",
			"§r§l§7» §r§aCurrent Hotspot: §r§c" . HotspotHandler::getInstance()->getCurrentHotspot()->getName(),
			"§r§7§l» §r§a+" . number_format($boost->extraFishingSkillXP, 2) . "§l » §r§aFishing Skill XP",
			"§r§7§l» §r§a+" . number_format($boost->extraSeaBossSpawnEggChance, 2) . "%" . "§l » §r§aSea Boss Egg Chance",
			"§r§7§l» §r§a+" . number_format($boost->extraTreasureLootChance, 2) . "%" . "§l » §r§aTreasure Loot Chance",
			"§r§7§l» §r§a+" . number_format($boost->fasterFishingInTicks / 20, 2) . "s" . "§l » §r§aFaster Fishing Speed",
		]);

		return $item;
	}

	public function getRecipeItem(): Item {
		$item = VanillaItems::BOOK()->setCustomName("§r§l§a» Recipes «");

		$total = count(RecipesHandler::getInstance()->getRecipes());
		$unlocked = count($this->player->getCurrentProfile()->getProfileSession()->getAllUnlockedRecipesIdentifiers());
		$progress = round($total / 100 * $unlocked, 2);

		$item->setLore(
			[
				"§r§7Through your Adventure, you will unlock",
				"§r§7recipes for all kinds of §6Special Items!",
                "§r",
				"§r§7You can view §6How to Craft §7these items through",
				"§r§7this Menu.",
				"§r",
				"§r§7§l» §r§7Recipe Books Unlocked: §e{$progress}%",
				"§r§7§l» §r§e{$unlocked}§7/§e{$total} §r§7§l«",
				"§r",
				"§r§eClick to view!"
			]);

		return $item;
	}

	public function getQuestLog(): Item {
		$item = VanillaItems::WRITABLE_BOOK();
		$item->setCustomName("§r§l§a» Quests «");
		$item->setLore([
			"§r§7View your quests",
			"§r",
			"§r§eClick to view!"
		]);

		return $item;
	}
	
	public function getActiveEffects(): Item {
		$item = VanillaItems::AWKWARD_POTION();
		$item->setCustomName("§r§l§a» Active Effects «");
		$item->setLore([
			"§r§7View and manage all your",
			"§r§7active potion effects.",
			"§r",
			"§r§7Drink potions or splash them",
			"§r§7on the ground to buff yourself",
			"§r",
			"§r§eClick to view!"
		]);
		
		return $item;
	}

	public function getPets(): Item {
		$player = $this->player;

		$slot = $player->getPetData()->getActivePetId();
		$allPets = $player->getPetData()->getPetCollection();


		$item = VanillaItems::BONE();
		$item->setCustomName("§r§l§a» Pets «");
		$item->setLore([
			"§r§7View and manage all your",
			"§r§7pets.",
			"§r",
			"§r§7Level up your pets faster by",
			"§r§7gaining xp in their favourite",
			"§r§7skill.",
			"§r",
			"§r§7Selected Pet: §c" . (isset($allPets[$slot]) ? $allPets[$slot]->buildPetItem()->getCustomName() : "None"),
			"§r",
			"§r§eClick to view!"
		]);

		return $item;
	}

	public function getCraftTable(): Item {
		$item = VanillaBlocks::CRAFTING_TABLE()->asItem();
		$item->setCustomName("§r§l§a» Crafting Table «");
		$item->setLore([
			"§r§7Opens the crafting grid",
			"§r",
			"§r§eClick to view!",
		]);

		return $item;
	}

	public function getTradesItem(): Item {
		$item = VanillaItems::EMERALD();
		$item->setCustomName("§r§l§a» Trades «");
		$item->setLore([
			"§r§7View your available trades.",
            "§r",
			"§r§7These trades are always",
			"§r§7Available and Accessible through",
			"§r§7the Skyblock Menu.",
			"§r",
			"§r§eClick to view!",
		]);

		return $item;
	}
}