<?php

declare(strict_types=1);

namespace skyblock\entity;

use pocketmine\block\BlockFactory;
use pocketmine\data\bedrock\PotionTypeIdMap;
use pocketmine\data\bedrock\PotionTypeIds;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\entity\object\FallingBlock;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Utils;
use pocketmine\world\World;
use skyblock\entity\object\LightningEntity;
use skyblock\entity\object\TextEntity;
use skyblock\entity\projectile\DreadlordSkullEntity;
use skyblock\entity\projectile\InkBombEntity;
use skyblock\entity\type\Arachne;
use skyblock\traits\AwaitStdTrait;
use skyblock\traits\InstanceTrait;

class EntityHandler {
	use InstanceTrait;
	use AwaitStdTrait;

	private array $clearlagEntities = [];

	private array $map;

	public function __construct() {
		self::$instance = $this;

		$this->register(Arachne::class, ['Arachne']);
		$this->register(LightningEntity::class, ['Lightning']);
		$this->register(DreadlordSkullEntity::class, ['DreadlordSkull']);
		$this->register(InkBombEntity::class, ['InkBomb']);
		$this->register(TextEntity::class, ['TextEntity']);
	}

	/**
	 * @param class-string<Entity> $className
	 */
	public function register(
		string $className,
		array $saveNames = [],
		?callable $creationFunc = null
	): void {
		if ($creationFunc === null) {
			Utils::testValidInstance($className, Entity::class);
			$creationFunc = function (World $world, CompoundTag $nbt) use (
				$className
			): Entity {
				return new $className(
					EntityDataHelper::parseLocation($nbt, $world),
					$nbt
				);
			};
		}

		EntityFactory::getInstance()->register(
			$className,
			$creationFunc,
			empty($saveNames)
				? ['hypertex:' . $className::getNetworkTypeId()]
				: $saveNames
		);
	}

	public function get(
		string $identifier,
		Location $location,
		CompoundTag $nbt = null
	): ?Entity {
		if (isset($this->clearlagEntities[$identifier])) {
			return new ($this->clearlagEntities[$identifier])($location, $nbt);
		}

		return null;
	}
}
