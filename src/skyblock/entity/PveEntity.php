<?php

declare(strict_types=1);

namespace skyblock\entity;

use muqsit\random\WeightedRandom;
use pocketmine\entity\Attribute;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\player\Player;
use pocketmine\Server;
use skyblock\entity\projectile\SkyBlockProjectile;
use skyblock\events\PlayerAttackEntityEvent;
use skyblock\events\PlayerKillEntityEvent;
use skyblock\events\EntityAttackPlayerEvent;
use skyblock\items\itemattribute\ItemAttributeHolder;
use skyblock\items\itemattribute\SkyBlockItemAttributes;
use skyblock\utils\WeightedItem;
use skyblock\items\SkyblockItem;
use skyblock\entity\EntityEquipment;
use skyblock\entity\EntityData;
use skyblock\player\PvePlayer;
use skyblock\PveHandler;
use Throwable;

class PveEntity extends Living implements EntityData {
	const ATTACK_COOLDOWN = 20;

	private int $attackCooldown = self::ATTACK_COOLDOWN;

	public int $maxHealth = 0;
	public int $level = 0;
	public int $coins = 0;
	public float $speed = 0.3;
	public bool $hostile = false;
	public float $damage = 0;
	public bool $targets = false; //whether you first have to attack it to target or auto target when nearby
	public string $name;
	public string $displayName;
	public float $combatXp;
	public int $totaldrops; //how many items it should generate from the weightedrandom
	public ?EntityEquipment $equipment = null;
	public ?WeightedRandom $loottable;

	private ?PvePlayer $target = null;

	//private ?CombatZone $zone = null;

	private ?PlayerAttackEntityEvent $lastDamageSource = null;

	public array $abilities = [];

	private ?MobEquipmentPacket $itemPacket = null;
	private ?MobArmorEquipmentPacket $armorPacket = null;

	public function __construct(
		Location $location,
		?CompoundTag $nbt = null
	) {
		$this->maxHealth = $nbt->getInt('custom_health');
		$this->coins = $nbt->getInt('custom_coins');
		$this->damage = $nbt->getFloat('custom_damage');
		$this->hostile = (bool) $nbt->getByte('custom_hostile');
		$this->speed = $nbt->getFloat('custom_speed');
		$this->targets = (bool) $nbt->getByte('custom_targetsFirst');
		$this->name = $nbt->getString('custom_name');
		$this->level = $nbt->getInt('custom_level');
		$this->displayName = $nbt->getString('custom_displayName');
		$this->combatXp = $nbt->getFloat('custom_combat_xp');
		$this->loottable =
			PveHandler::getInstance()->getLoottables()[
				$nbt->getString('custom_loottable')
			] ?? null;
		$this->totaldrops = $nbt->getInt('custom_totaldrops');
		$this->abilities = json_decode($nbt->getString('abilities'), true);

		parent::__construct($location, $nbt);

		if ($nbt->getString('equipment', '') !== '') {
			$this->equipment = EntityEquipment::deserialize(
				json_decode($nbt->getString('equipment'), true)
			);

			$converter = TypeConverter::getInstance();

			$this->armorPacket = MobArmorEquipmentPacket::create(
				$this->getId(),
				ItemStackWrapper::legacy(
					$converter->coreItemStackToNet($this->equipment->helmet)
				),
				ItemStackWrapper::legacy(
					$converter->coreItemStackToNet($this->equipment->chestplate)
				),
				ItemStackWrapper::legacy(
					$converter->coreItemStackToNet($this->equipment->leggings)
				),
				ItemStackWrapper::legacy(
					$converter->coreItemStackToNet($this->equipment->boots)
				)
			);

			$this->itemPacket = MobEquipmentPacket::create(
				$this->getId(),
				ItemStackWrapper::legacy(
					TypeConverter::getInstance()->coreItemStackToNet(
						$this->equipment->hand
					)
				),
				0,
				0,
				ContainerIds::INVENTORY
			);
		}
	}

	public function getTarget(): ?PvePlayer {
		return $this->target;
	}

	public function onUpdate(int $currentTick): bool {
		if ($this->hostile) {
			if ($this->target === null && $this->targets) {
				foreach (
					$this->getWorld()->getNearbyEntities(
						$this->getBoundingBox()->expandedCopy(10, 10, 10)
					)
					as $e
				) {
					if (!$e instanceof PvePlayer) {
						continue;
					}
					if (!$e->isSurvival()) {
						continue;
					}

					$this->target = $e;
					break;
				}
			}

			if ($this->target !== null) {
				$distance = $this->getPosition()->distance(
					$this->target->getPosition()
				);
				if ($distance < 20) {
					if ($this->ticksLived % 10 === 0 && $distance > 1) {
						//set target and starting path finding
					}

					// try pathfinder results
				} else {
					$this->target = null;
				}

				if ($this->target !== null) {
					if ($distance <= 2.98 && --$this->attackCooldown <= 0) {
						if (
							!$this->isFlaggedForDespawn() &&
							!$this->isClosed()
						) {
							$this->attackCooldown = self::ATTACK_COOLDOWN;

							$v = true;
							foreach ($this->abilities as $id) {
								$ability = PveHandler::getInstance()->getAbility(
									$id
								);

								if ($ability === null) {
									continue;
								}

								$value = $ability->attack(
									$this->target,
									$this,
									$this->damage
								);

								if ($value === false) {
									$v = false;
								}
							}

							if ($v) {
								$event = new EntityAttackPlayerEvent(
									$this,
									$this->target,
									$this->damage
								);
								$event->call(); //the event listener will do all the damage handling
							}
						}
					}
				}
			}
		}

		foreach ($this->abilities as $id) {
			$ability = PveHandler::getInstance()->getAbility($id);

			if ($ability === null) {
				continue;
			}

			$ability->onTick($this, $currentTick);
		}

		return parent::onUpdate($currentTick);
	}

	protected function initEntity(CompoundTag $nbt): void {
		$this->setCanSaveWithChunk(false);
		$this->setMaxHealth($this->maxHealth);
		$this->setHealth($this->maxHealth);
		$this->setNameTagAlwaysVisible(true);

		parent::initEntity($nbt);

		$this->setCanSaveWithChunk(false);
	}

	public function getDrops(): array {
		$s = [];

		if ($this->loottable === null) {
			return [];
		}

		/** @var WeightedItem $drop */
		foreach ($this->loottable->generate($this->totaldrops) as $drop) {
			$s[] = $drop
				->getItem()
				->setCount(mt_rand($drop->getMinCount(), $drop->getMaxCount()));
		}
		return $s;
	}

	public function spawnTo(Player $player): void {
		parent::spawnTo($player);

		if ($this->itemPacket !== null) {
			$player->getNetworkSession()->sendDataPacket($this->itemPacket);
		}

		if ($this->armorPacket !== null) {
			$player->getNetworkSession()->sendDataPacket($this->armorPacket);
		}
	}

	public function attack(EntityDamageEvent $source): void {
		if ($source instanceof EntityDamageByEntityEvent) {
			$damager = $source->getDamager();
			if ($damager instanceof PvePlayer) {
				$this->target = $damager;

				$item = $damager->getInventory()->getItemInHand();

				$damage = 0;
				if ($item instanceof ItemAttributeHolder) {
					$damage = $item
						->getItemAttribute(SkyBlockItemAttributes::DAMAGE())
						->getValue();
				}

				$base = $damage === 0 ? $item->getAttackPoints() : $damage;

				$source->setBaseDamage(0);
				foreach ($source->getModifiers() as $k => $v) {
					$source->setModifier(0, $k);
				}

				parent::attack($source);

				if ($source->isCancelled()) {
					return;
				}

				$strength = $damager->getPveData()->getStrength();
				$formula = ($base + 5) * (1 + $strength / 100);
				$crit = false;

				if (
					mt_rand(1, 100) <= $damager->getPveData()->getCritChance()
				) {
					$crit = true;

					$formula *=
						1 +
						max(1, $damager->getPveData()->getCritDamage()) / 100;
				}

				$event = new PlayerAttackEntityEvent(
					$damager,
					$this,
					$formula,
					$crit
				);
				foreach ($this->abilities as $id) {
					$ability = PveHandler::getInstance()->getAbility($id);

					if ($ability === null) {
						continue;
					}

					$ability->onDamage($this, $event);
				}

				if ($source instanceof EntityDamageByChildEntityEvent) {
					$projectile = $source->getChild();

					if ($projectile instanceof SkyBlockProjectile) {
						$event->setProjectile($projectile);

						$sourceItem = $projectile->getSourceItem();
						if ($sourceItem instanceof SkyblockItem) {
							$sourceItem->onProjectileHitEntityEvent($event);
						}
					}
				}

				$event->call();
				return;
			}
		}

		parent::attack($source);
	}

	/**
	 * @param PvePlayer|null $target
	 */
	public function setTarget(?PvePlayer $target): void {
		$this->target = $target;
	}

	/**
	 * @param PlayerAttackEntityEvent|null $lastDamageSource
	 */
	public function setLastDamageSource(
		?PlayerAttackEntityEvent $lastDamageSource
	): void {
		$this->lastDamageSource = $lastDamageSource;
	}

	/**
	 * @return PlayerAttackEntityEvent|null
	 */
	public function getLastDamageSource(): ?PlayerAttackEntityEvent {
		return $this->lastDamageSource;
	}

	public function saveNBT(): CompoundTag {
		$nbt = parent::saveNBT();
		$nbt->setString('networkID38', $this->networkID);

		return $nbt;
	}

	protected function getInitialSizeInfo(): EntitySizeInfo {
		return new EntitySizeInfo(
			2,
			2
		);
	}

	public static function getNetworkTypeId(): string {
		return EntityIds::TRIPOD_CAMERA;
	}

	protected function sendSpawnPacket(Player $player): void {
		parent::sendSpawnPacket($player);
	}

	public function kill(): void {
		parent::kill();

		$last = $this->lastDamageSource;

		if ($last instanceof PlayerAttackEntityEvent) {
			(new PlayerKillEntityEvent(
				$last->getPlayer(),
				$this,
				$last
			))->call();
		}
	}

	public function getName(): string {
		return $this->displayName;
	}

	public function getZoneMobName(): string {
		return $this->name;
	}

	public function updateNametag(): void {
		$health = number_format($this->getHealth());
		$maxHealth = number_format($this->getMaxHealth());
		$this->setNameTag(
			"§8[§7Lv{$this->level}§8] §c{$this->displayName} §a{$health}§f/§a{$maxHealth}"
		);
	}

	public function setHealth(float $amount): void {
		parent::setHealth($amount);
		$this->updateNametag();
	}

	public function getZone(): ?CombatZone {
		return $this->zone;
	}

	public function setZone(?CombatZone $zone): void {
		$this->zone = $zone;
	}

	protected function onDeath(): void {
		$ev = new EntityDeathEvent(
			$this,
			$this->getDrops(),
			$this->getXpDropAmount()
		);

		foreach ($this->abilities as $id) {
			$ability = PveHandler::getInstance()->getAbility($id);

			if ($ability === null) {
				continue;
			}

			$ability->onDeath($this, $ev);
		}

		$ev->call();
		foreach ($ev->getDrops() as $item) {
			$item->getNamedTag()->setByte('collection', 1);
			$this->getWorld()->dropItem($this->location, $item);
		}

		//TODO: check death conditions (must have been damaged by player < 5 seconds from death)
		$this->getWorld()->dropExperience(
			$this->location,
			$ev->getXpDropAmount()
		);

		$this->startDeathAnimation();
	}
}
