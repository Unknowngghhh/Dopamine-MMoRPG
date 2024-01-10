<?php

declare(strict_types=1);

namespace skyblock\player;

use pocketmine\block\BlockTypeIds;
use pocketmine\block\VanillaBlocks;
use pocketmine\entity\animation\ArmSwingAnimation;
use pocketmine\entity\Skin;
use pocketmine\entity\ExperienceManager;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\form\Form;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\lang\Translatable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\permission\PermissionAttachment;
use pocketmine\player\Player;
use pocketmine\timings\Timings;
use pocketmine\world\sound\FireExtinguishSound;
use pocketmine\world\sound\ItemBreakSound;
use skyblock\Main;
use skyblock\PveHandler;
use skyblock\entity\type\Arachne;
use skyblock\traits\AwaitStdTrait;
use skyblock\traits\StringCooldownTrait;

class PvePlayer extends Player {
	use AwaitStdTrait;
	use StringCooldownTrait;

	/** @var ExperienceManager */
	protected ExperienceManager $xpManager;
	private Skin $originalSkin;

	private ?PermissionAttachment $attachment = null;

	private string $rank = '';
	public string $wdXUID = '';
	public bool $frozen = false;
	public bool $initilized = false;
	public bool $inStaffMode = false;
	protected bool $fullyInitialized = false;

	private PlayerPveData $pveData;

	private ?CustomSurvivalBlockBreakHandler $customBlockBreakHandler = null;

	public function getXuid(): string {
		return $this->wdXUID;
	}

	protected function initEntity(CompoundTag $nbt): void {
		$this->setOriginalSkin($this->getSkin());

		parent::initEntity($nbt);

		$this->initilized = true;

		$this->xpManager = new ExperienceManager($this);
		$this->pveData = new PlayerPveData($this);

		//$rank = new OwnerRank();
		//$this->setRank($rank->getColour() . $rank->getName());
		$this->fullyInitialized = true;
		$data = PveHandler::getInstance()->getEntities()['arachne'];
		$entity = new Arachne($this->getLocation(), $data['nbt']);
		$entity->spawnToAll();
	}

	public function getPveData(): PlayerPveData {
		return $this->pveData;
	}

	public function setRank(string $rank): void {
		$this->rank = $rank;
		$this->setNameTag($this->getNameTag());
	}

	public function getRank(): string {
		return $this->rank;
	}

	public function sendForm(Form $form): void {
		if ($this->isOnline()) {
			parent::sendForm($form);
		} else {
			$this->getServer()
				->getLogger()
				->critical(
					"Tried to send a form to offline user ({$this->getName()}",
				);
		}
	}

	public function fixAfterTeleport() {
		$this->broadcastMovement(true);
		$this->broadcastMotion();
	}

	public function sendMessage(Translatable|string|array $message): void {
		if ($this->isOnline()) {
			if (is_array($message)) {
				$message = implode("\n", $message);
			}

			parent::sendMessage($message);
		} else {
			$this->getServer()
				->getLogger()
				->critical(
					"Tried to send a message to offline user ({$this->getName()}",
				);
		}
	}

	public function getXpManager(): ExperienceManager {
		return $this->xpManager;
	}

	public function attack(EntityDamageEvent $source): void {
		if ($this->initilized === false) {
			return;
		}
		if ($this->effectManager === null) {
			return;
		}

		parent::attack($source);
	}

	public function attackBlock(Vector3 $pos, int $face): bool {
		if ($pos->distanceSquared($this->location) > 10000) {
			return false; //TODO: maybe this should throw an exception instead?
		}

		$target = $this->getWorld()->getBlock($pos);

		$ev = new PlayerInteractEvent(
			$this,
			$this->inventory->getItemInHand(),
			$target,
			null,
			$face,
			PlayerInteractEvent::LEFT_CLICK_BLOCK,
		);
		if ($this->isSpectator()) {
			$ev->cancel();
		}
		$ev->call();
		if ($ev->isCancelled()) {
			return false;
		}
		$this->broadcastAnimation(
			new ArmSwingAnimation($this),
			$this->getViewers(),
		);
		if (
			$target->onAttack($this->inventory->getItemInHand(), $face, $this)
		) {
			return true;
		}

		$block = $target->getSide($face);
		if ($block->getTypeId() === BlockTypeIds::FIRE) {
			$this->getWorld()->setBlock(
				$block->getPosition(),
				VanillaBlocks::AIR(),
			);
			$this->getWorld()->addSound(
				$block->getPosition()->add(0.5, 0.5, 0.5),
				new FireExtinguishSound(),
			);
			return true;
		}

		if (
			!$this->isCreative() &&
			!$block->getBreakInfo()->breaksInstantly()
		) {
			$this->customBlockBreakHandler = new CustomSurvivalBlockBreakHandler(
				$this,
				$pos,
				$target,
				$face,
				16,
			);
		}

		return true;
	}

	public function hasLoaded(): bool {
		return $this->fullyInitialized ?? false;
	}

	protected function destroyCycles(): void {
		parent::destroyCycles();
		$this->customBlockBreakHandler = null;
	}

	public function onPostDisconnect(
		Translatable|string|null $reason,
		Translatable|string|null $quitMessage,
	): void {
		parent::onPostDisconnect($reason, $quitMessage);
		$this->customBlockBreakHandler = null;
	}

	public function stopBreakBlock(Vector3 $pos): void {
		if (
			$this->customBlockBreakHandler !== null &&
			$this->customBlockBreakHandler
				->getBlockPos()
				->distanceSquared($pos) < 0.0001
		) {
			$this->customBlockBreakHandler = null;
		}
	}

	public function continueBreakBlock(Vector3 $pos, int $face): void {
		if (
			$this->customBlockBreakHandler !== null &&
			$this->customBlockBreakHandler
				->getBlockPos()
				->distanceSquared($pos) < 0.0001
		) {
			$this->customBlockBreakHandler->setTargetedFace($face);
		}
	}

	public function teleport(
		Vector3 $pos,
		?float $yaw = null,
		?float $pitch = null,
	): bool {
		$this->customBlockBreakHandler = null;
		return parent::teleport($pos, $yaw, $pitch);
	}

	public function doHitAnimationCustom(): void {
		$this->doHitAnimation();
	}

	public function damageArmor(float $damage): void {
		return; // todo: make armors unbreakable
	}

	private function damageItem(Durable $item, int $durabilityRemoved): void {
		return; // todo: make items unbreakable instead
	}

	public function dropItem(Item $item): void {
		if (!$this->isOnCooldown('drop-alert')) {
			$this->sendMessage(
				Main::PREFIX .
					'Players that are not in the same profile as you will not be able to pick up or see your items.',
			);
		} else {
			$this->setCooldown('drop-alert', 1200);
		}

		//$item->getNamedTag()->setString(PveItemEntity::TAG_OWNING_PROFILE, $profileId);

		parent::dropItem($item);
	}

	public function setHealth(float $amount): void {
		if ($amount > $this->getMaxHealth()) {
			$amount = $this->getMaxHealth();
		}
		parent::setHealth($amount);
		$this->setNameTag($this->getNameTag());
	}

	public function onUpdate(int $currentTick): bool {
		$tickDiff = $currentTick - $this->lastUpdate;

		if ($tickDiff <= 0) {
			return true;
		}

		$this->messageCounter = 2;

		$this->lastUpdate = $currentTick;

		if ($this->justCreated) {
			$this->onFirstUpdate($currentTick);
		}

		if (!$this->isAlive() && $this->spawned) {
			$this->onDeathUpdate($tickDiff);
			return true;
		}

		$this->timings->startTiming();

		if ($this->spawned) {
			$this->processMostRecentMovements();
			$this->motion = new Vector3(0, 0, 0); //TODO: HACK! (Fixes player knockback being messed up)
			if ($this->onGround) {
				$this->inAirTicks = 0;
			} else {
				$this->inAirTicks += $tickDiff;
			}

			Timings::$entityBaseTick->startTiming();
			$this->entityBaseTick($tickDiff);
			Timings::$entityBaseTick->stopTiming();

			if (!$this->isSpectator() && $this->isAlive()) {
				Timings::$playerCheckNearEntities->startTiming();
				$this->checkNearEntities();
				Timings::$playerCheckNearEntities->stopTiming();
			}

			if (
				$this->customBlockBreakHandler !== null &&
				!$this->customBlockBreakHandler->update()
			) {
				$this->breakBlock(
					$this->customBlockBreakHandler->getBlockPos(),
				);
				$this->customBlockBreakHandler = null;
			}
		}

		$this->timings->stopTiming();

		return true;
	}

	public function hasPermission($name): bool {
		return parent::hasPermission($name);
	}

	public function sendTip(string $message): void {
		if ($this->isOnline()) {
			parent::sendTip($message);
		}
	}

	/**
	 * @return PermissionAttachment|null
	 */
	public function getAttachment(): ?PermissionAttachment {
		return $this->attachment;
	}

	/**
	 * @param PermissionAttachment|null $attachment
	 */
	public function setAttachment(?PermissionAttachment $attachment): void {
		$this->attachment = $attachment;
	}

	public function setMovementSpeed(float $v, bool $fit = false): void {
		$this->moveSpeedAttr->setValue($v, $fit);

		$this->networkPropertiesDirty = true;
	}

	public function setOriginalSkin(Skin $originalSkin): void {
		$this->originalSkin = $originalSkin;
	}

	public function getOriginalSkin(): Skin {
		return $this->originalSkin;
	}

	public function canSee(Player $player): bool {
		if ($player instanceof PvePlayer) {
			if ($player->inStaffMode && !$this->inStaffMode) {
				return false;
			}
		}

		return parent::canSee($player);
	}
}
