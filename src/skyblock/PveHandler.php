<?php

declare(strict_types = 1);

namespace skyblock;

use muqsit\random\WeightedRandom;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\EntityDataHelper;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\Server;
use pocketmine\world\World;
use skyblock\utils\WeightedItem;
use skyblock\tasks\DataRegenerator;
use skyblock\tasks\TipUpdater;
use skyblock\listeners\PveListener;
use skyblock\entity\PveEntity;
use skyblock\entity\EntityEquipment;
use skyblock\entity\ability\MobAbility;
use skyblock\entity\ability\MobThrowAbility;
use skyblock\entity\ability\RandomJumpAbility;
use skyblock\entity\ability\ArachneBossAbility;
use skyblock\entity\ability\SplitterSpiderAbility;
use skyblock\entity\ability\PveMagicImmuneAbility;
use skyblock\entity\ability\SkeletonBowShootAbility;
use skyblock\traits\HandlerTrait;
use function bin2hex;
use function json_encode;
use const JSON_THROWN_ON_ERROR;

class PveHandler {
	use HandlerTrait;

	public array $entities = [];

	private World $world;

	private array $mobAbilities = [];

	private array $loottables = [];

	public function onEnable(): void {
		Server::getInstance()
		->getPluginManager()
		->registerEvents(new PveListener(), Main::getInstance());

		Server::getInstance()
		->getWorldManager()
		->loadWorld('hypixel');
		$this->world = Server::getInstance()
		->getWorldManager()
		->getWorldByName('hypixel');

		$this->registerMobAbility(new MobThrowAbility());
		$this->registerMobAbility(new RandomJumpAbility());
		$this->registerMobAbility(new SplitterSpiderAbility());
		$this->registerMobAbility(new ArachneBossAbility());
		$this->registerMobAbility(new SkeletonBowShootAbility());
		$this->registerEntityTypes();

		//Pathfinder::initialise();
		//ZoneHandler::initialise();
		//LaunchpadHandler::initialise();
		//HotspotHandler::initialise();

		Main::getInstance()
		->getScheduler()
		->scheduleRepeatingTask(new DataRegenerator(), 20);
		Main::getInstance()
		->getScheduler()
		->scheduleRepeatingTask(new TipUpdater(), 10);
	}

	public function registerMobAbility(MobAbility $ability): void {
		$this->mobAbilities[$ability::getId()] = $ability;
	}

	public function registerEntityTypes(): void {
		$this->addPveEntityType(
			'floor-one-iron-golem',
			'Iron Golem',
			10,
			40,
			EntityIds::IRON_GOLEM,
			25000,
			true,
			700,
			[],
			250,
			true,
			0.65,
			[MobThrowAbility::getId()],
		);

		$this->addPveEntityType(
			'crypt-ghoul',
			'Crypt Ghoul',
			13,
			30,
			EntityIds::ZOMBIE,
			2000,
			true,
			32,
			[new WeightedItem(VanillaItems::ROTTEN_FLESH(), 100, 1, 2)],
			350,
			true,
			0.65,
			[],
			new EntityEquipment(
				VanillaItems::IRON_SWORD(),
				VanillaItems::CHAINMAIL_HELMET(),
				VanillaItems::CHAINMAIL_CHESTPLATE(),
				VanillaItems::CHAINMAIL_LEGGINGS(),
				VanillaItems::CHAINMAIL_BOOTS(),
			),
		);

		$this->addPveEntityType(
			'splitter-silverfish',
			'Silverfish',
			1,
			2,
			EntityIds::SILVERFISH,
			50,
			true,
			12,
			[new WeightedItem(VanillaItems::STRING(), 80)],
			25,
			true,
			0.72
		);

		$this->addPveEntityType(
			'splitter-spider-42',
			'Splitter Spider',
			10,
			42,
			EntityIds::SPIDER,
			4500,
			true,
			28,
			[
				new WeightedItem(VanillaItems::STRING(), 100),
				new WeightedItem(VanillaItems::SPIDER_EYE(), 10),
			], //todo: coins
			550,
			true,
			0.72,
			[SplitterSpiderAbility::getId()],
		);

		$this->addPveEntityType(
			'arachnes-brood',
			"Arachne's Brood",
			800,
			100,
			EntityIds::SPIDER,
			5000,
			true,
			80,
			[
				new WeightedItem(VanillaItems::STRING(), 100),
				new WeightedItem(VanillaItems::SPIDER_EYE(), 50),
			],
			200,
			true,
			0.9,
			[],
		);

		$this->addPveEntityType(
			'arachne',
			'Arachne Boss',
			2000,
			300,
			EntityIds::SPIDER,
			40000,
			true,
			200,
			[
				new WeightedItem(VanillaItems::SPIDER_EYE(), 100, 10, 20),
				new WeightedItem(
					VanillaItems::STRING(),
					100,
					10,
					20,
				) ,
			//new WeightedItem(SkyblockItems::ARACHNE_HELMET(), 10),
			//new WeightedItem(SkyblockItems::ARACHNE_CHESTPLATE(), 10),
			//new WeightedItem(SkyblockItems::ARACHNE_LEGGINGS(), 10),
			//new WeightedItem(SkyblockItems::ARACHNE_BOOTS(), 10)
			],
			1,
			true,
			0.6,
			[ArachneBossAbility::getId()],
		);

		$this->addPveEntityType(
			'skeleton-8',
			'Skeleton',
			4,
			8,
			EntityIds::SKELETON,
			325,
			true,
			8,
			[new WeightedItem(VanillaItems::BONE(), 100, 1, 5)],
			39,
			true,
			0.1,
			[SkeletonBowShootAbility::getId()],
			new EntityEquipment(VanillaItems::BOW()),
		);
	}

	public function addPveEntityType(
		string $name,
		string $displayName,
		int $coins,
		int $level,
		string $entityID,
		int $health,
		bool $hostile,
		float $combatXp,
		array $drops,
		float $damage = 0,
		bool $targetsFirst = false,
		float $speed = 0.3,
		array $abilities = [],
		?EntityEquipment $equipment = null,
	): void {
		$id = '';
		$totalDrops = 0;

		if (!empty($drops)) {
			$this->loottables[($id = uniqid())] = $r = new WeightedRandom();
			/** @var WeightedItem $lootboxItem */
			foreach ($drops as $lootboxItem) {
				if ((float) $lootboxItem->getChance() === (float) 100.0) {
					$totalDrops++;
				}

				$r->add($lootboxItem, $lootboxItem->getChance());
			}

			$r->setup();
		}

		$compound = new CompoundTag();
		$compound->setString('custom_name', $name);
		$compound->setString('custom_entity_id', $entityID);
		$compound->setInt('custom_health', $health);
		$compound->setInt('custom_coins', $coins);
		$compound->setByte('custom_hostile', (int) $hostile);
		$compound->setFloat('custom_damage', $damage);
		$compound->setByte('custom_targetsFirst', (int) $targetsFirst);
		$compound->setFloat('custom_speed', $speed);
		$compound->setString('custom_displayName', $displayName);
		$compound->setInt('custom_level', $level);
		$compound->setInt('custom_totaldrops', max(1, $totalDrops));
		$compound->setFloat('custom_combat_xp', $combatXp);
		$compound->setString('custom_loottable', $id);
		$compound->setString('abilities', json_encode($abilities));
		if ($equipment !== null) {
			$compound->setString('equipment',
			json_encode($equipment->serialize()));
		}

		$this->entities[$name]['nbt'] = $compound;
		$this->entities[$name]['networkId'] = $entityID;

		/*$creationFunc = function (World $world, CompoundTag $nbt): Entity {
			return new PveEntity(
				$nbt->getString('custom_entity_id'),
				EntityDataHelper::parseLocation($nbt, $world),
				$nbt
			);
		};
		EntityFactory::getInstance()->register(
			PveEntity::class,
			$creationFunc,
			['hypertex:' . $name]
		);*/
	}

	public function getAbility(string $id): ?MobAbility {
		return $this->mobAbilities[$id] ?? null;
	}

	/**
	* @return array
	*/
	public function getEntities(): array {
		return $this->entities;
	}

	/**
	* @return WeightedRandom[]
	*/
	public function getLoottables(): array {
		return $this->loottables;
	}

	/**
	* @return World
	*/
	public function getPveWorld(): World {
		return $this->world;
	}
}