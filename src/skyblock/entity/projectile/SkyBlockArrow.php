<?php

declare(strict_types=1);

namespace skyblock\entity\projectile;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityItemPickupEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\player\Player;
use pocketmine\world\sound\ArrowHitSound;

class SkyBlockArrow extends SkyBlockProjectile {
	public static function getNetworkTypeId(): string {
		return EntityIds::ARROW;
	}

	public const PICKUP_NONE = 0;
	public const PICKUP_ANY = 1;
	public const PICKUP_CREATIVE = 2;

	private const TAG_PICKUP = 'pickup'; //TAG_Byte
	public const TAG_CRIT = 'crit'; //TAG_Byte

	protected $gravity = 0.05;
	protected $drag = 0.01;

	/** @var float */
	protected $damage = 2.0;

	/** @var int */
	protected $pickupMode = self::PICKUP_ANY;

	/** @var float */
	protected $punchKnockback = 0.0;

	/** @var int */
	protected $collideTicks = 0;

	/** @var bool */
	protected $critical = false;

	//used to detect what caused the arrow to launch, e.g. used in runaans bow
	private string $origin = 'unknown';

	public function __construct(
		Location $location,
		?Entity $shootingEntity,
		bool $critical,
		?CompoundTag $nbt = null
	) {
		parent::__construct($location, $shootingEntity, $nbt);
		$this->setCritical($critical);
	}

	protected function getInitialSizeInfo(): EntitySizeInfo {
		return new EntitySizeInfo(0.25, 0.25);
	}

	protected function initEntity(CompoundTag $nbt): void {
		parent::initEntity($nbt);

		$this->pickupMode = $nbt->getByte(self::TAG_PICKUP, self::PICKUP_ANY);
		$this->critical = $nbt->getByte(self::TAG_CRIT, 0) === 1;
		$this->collideTicks = $nbt->getShort('life', $this->collideTicks);
	}

	public function saveNBT(): CompoundTag {
		$nbt = parent::saveNBT();
		$nbt->setByte(self::TAG_PICKUP, $this->pickupMode);
		$nbt->setByte(self::TAG_CRIT, $this->critical ? 1 : 0);
		$nbt->setShort('life', $this->collideTicks);
		return $nbt;
	}

	public function isCritical(): bool {
		return $this->critical;
	}

	public function setCritical(bool $value = true): void {
		$this->critical = $value;
		$this->networkPropertiesDirty = true;
	}

	public function getResultDamage(): int {
		$base = (int) ceil($this->motion->length() * parent::getResultDamage());
		if ($this->isCritical()) {
			return $base + mt_rand(0, (int) ($base / 2) + 1);
		} else {
			return $base;
		}
	}

	public function getPunchKnockback(): float {
		return $this->punchKnockback;
	}

	public function setPunchKnockback(float $punchKnockback): void {
		$this->punchKnockback = $punchKnockback;
	}

	protected function entityBaseTick(int $tickDiff = 1): bool {
		if ($this->closed) {
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if ($this->blockHit !== null) {
			$this->collideTicks += $tickDiff;
			if ($this->collideTicks > 1200) {
				$this->flagForDespawn();
				$hasUpdate = true;
			}
		} else {
			$this->collideTicks = 0;
		}

		return $hasUpdate;
	}

	protected function onHit(ProjectileHitEvent $event): void {
		parent::onHit($event);

		$this->setCritical(false);
		$this->broadcastSound(new ArrowHitSound());
	}

	protected function onHitBlock(
		Block $blockHit,
		RayTraceResult $hitResult
	): void {
		parent::onHitBlock($blockHit, $hitResult);
		//$this->broadcastAnimation(new ArrowShakeAnimation($this, 7));
	}

	protected function onHitEntity(
		Entity $entityHit,
		RayTraceResult $hitResult
	): void {
		parent::onHitEntity($entityHit, $hitResult);
		if ($this->punchKnockback > 0) {
			$horizontalSpeed = sqrt(
				$this->motion->x ** 2 + $this->motion->z ** 2
			);
			if ($horizontalSpeed > 0) {
				$multiplier = ($this->punchKnockback * 0.6) / $horizontalSpeed;
				$entityHit->setMotion(
					$entityHit
						->getMotion()
						->add(
							$this->motion->x * $multiplier,
							0.1,
							$this->motion->z * $multiplier
						)
				);
			}
		}
	}

	public function getPickupMode(): int {
		return $this->pickupMode;
	}

	public function setPickupMode(int $pickupMode): void {
		$this->pickupMode = $pickupMode;
	}

	public function onCollideWithPlayer(Player $player): void {
		if ($this->blockHit === null) {
			return;
		}

		$item = VanillaItems::ARROW();
		$playerInventory = match (true) {
			!$player->hasFiniteResources() => null,
			$player
				->getOffHandInventory()
				->getItem(0)
				->canStackWith($item) &&
				$player->getOffHandInventory()->canAddItem($item)
				=> $player->getOffHandInventory(),
			$player->getInventory()->canAddItem($item)
				=> $player->getInventory(),
			default => null
		};

		$ev = new EntityItemPickupEvent(
			$player,
			$this,
			$item,
			$playerInventory
		);
		if ($player->hasFiniteResources() && $playerInventory === null) {
			$ev->cancel();
		}
		if (
			$this->pickupMode === self::PICKUP_NONE ||
			($this->pickupMode === self::PICKUP_CREATIVE &&
				!$player->isCreative())
		) {
			$ev->cancel();
		}

		$ev->call();
		if ($ev->isCancelled()) {
			return;
		}

		foreach ($this->getViewers() as $viewer) {
			$viewer->getNetworkSession()->onPlayerPickUpItem($player, $this);
		}

		$ev->getInventory()?->addItem($ev->getItem());
		$this->flagForDespawn();
	}

	protected function syncNetworkData(
		EntityMetadataCollection $properties
	): void {
		parent::syncNetworkData($properties);

		$properties->setGenericFlag(
			EntityMetadataFlags::CRITICAL,
			$this->critical
		);
	}

	/**
	 * Changes the entity's yaw and pitch to make it look at the specified Vector3 position. For mobs, this will cause
	 * their heads to turn. Copied from Living.php
	 */
	public function lookAt(Vector3 $target): void {
		$horizontal = sqrt(
			($target->x - $this->location->x) ** 2 +
				($target->z - $this->location->z) ** 2
		);
		$vertical = $target->y - ($this->location->y + $this->getEyeHeight());
		$pitch = (-atan2($vertical, $horizontal) / M_PI) * 180; //negative is up, positive is down

		$xDist = $target->x - $this->location->x;
		$zDist = $target->z - $this->location->z;

		$yaw = (atan2($zDist, $xDist) / M_PI) * 180 - 90;
		if ($yaw < 0) {
			$yaw += 360.0;
		}

		$this->setRotation($yaw, $pitch);
	}

	public function getOrigin(): string {
		return $this->origin;
	}

	public function setOrigin(string $origin): void {
		$this->origin = $origin;
	}
}
