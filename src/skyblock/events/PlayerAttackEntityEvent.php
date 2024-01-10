<?php

declare(strict_types=1);

namespace skyblock\events;

use pocketmine\event\Event;
use skyblock\entity\PveEntity;
use skyblock\entity\projectile\SkyBlockProjectile;
use skyblock\items\ability\ItemAbility;
use skyblock\player\PvePlayer;
use skyblock\utils\PveUtils;

//you can just create and call this event to attack a mob, it will automatically handle everything in PveListener.php
class PlayerAttackEntityEvent extends Event {
	/** @var array<string, int> */
	private array $damageAmplifiers = [];
	/** @var array<string, int> */
	private array $damageReducers = [];
	/** @var array<string, int> */
	private array $damageMultipliers = [];
	/** @var array<string, int> */
	private array $damageDividers = [];

	private bool $isMagicDamage = false;

	private ?SkyBlockProjectile $projectile = null;

	private array $data = []; //this data will be available to do like "cleave: true" so cleave wont just create a chain of reactions

	public function __construct(
		private PvePlayer $player,
		private PveEntity $entity,
		private float $damage,
		private bool $critical = false,
		private float $knockback = 0.4,
		private ?ItemAbility $cause = null
	) {
	}

	public function increaseDamage(float $damage, string $cause): void {
		$this->damageAmplifiers[$cause] = $damage;
	}

	public function setIsMagicDamage(bool $isMagicDamage): void {
		$this->isMagicDamage = $isMagicDamage;
	}

	public function getCause(): ?ItemAbility {
		return $this->cause;
	}

	public function setCause(?ItemAbility $cause): void {
		$this->cause = $cause;
	}

	public function isMagicDamage(): bool {
		return $this->isMagicDamage;
	}

	public function decreaseDamage(float $damage, string $cause): void {
		$this->damageReducers[$cause] = $damage;
	}

	public function multiplyDamage(float $multiplier, string $cause): void {
		$this->damageMultipliers[$cause] = $multiplier;
	}

	public function divideDamage(float $divider, string $cause): void {
		$this->damageDividers[$cause] = $divider;
	}

	public function getFinalDamage(): float {
		$damage = $this->getBaseDamage();
		$damage += array_sum($this->damageAmplifiers);
		$damage -= array_sum($this->damageReducers);
		$damage *= array_sum($this->damageMultipliers) + 1;
		$damage /= array_sum($this->damageDividers) + 1;

		if ($this->isMagicDamage()) {
			$damage += PveUtils::getFinalMagicDamage(
				$damage,
				$this->player->getPveData()
			);
		}

		return max(0, $damage);
	}

	public function getEntity(): PveEntity {
		return $this->entity;
	}

	public function getPlayer(): PvePlayer {
		return $this->player;
	}

	public function getBaseDamage(): float {
		return $this->damage;
	}

	public function setBaseDamage(float $damage): void {
		$this->damage = $damage;
	}

	public function isCritical(): bool {
		return $this->critical;
	}

	public function setCritical(bool $critical): void {
		$this->critical = $critical;
	}

	public function getKnockback(): float {
		return $this->knockback;
	}
	public function setKnockback(float $knockback): void {
		$this->knockback = $knockback;
	}

	public function getData(): array {
		return $this->data;
	}
	public function setData(array $data): void {
		$this->data = $data;
	}

	public function __toString(): string {
		$string = 'Damager:§c ' . $this->player->getName();
		$string .= "\nEntity:§c " . $this->getEntity()->getName();
		$string .=
			"\nBase Damage (without modifiers):§c " . $this->getBaseDamage();
		$string .= "\nFinal Damage:§c " . $this->getFinalDamage();

		$string .= "\nDamage Increases: ";
		foreach ($this->damageMultipliers as $k => $v) {
			$string .= "\n$k:§c +$v x damage";
		}
		foreach ($this->damageAmplifiers as $k => $v) {
			$string .= "\n$k:§c +$v damage";
		}

		$string .= "\nDamage Reducers: ";
		foreach ($this->damageDividers as $k => $v) {
			$string .= "\n$k:§c -$v x damage";
		}
		foreach ($this->damageReducers as $k => $v) {
			$string .= "\n$k:§c -$v damage";
		}

		return $string;
	}

	public function getProjectile(): ?SkyBlockProjectile {
		return $this->projectile;
	}

	public function setProjectile(?SkyBlockProjectile $projectile): void {
		$this->projectile = $projectile;
	}

	public function isCancelled(): bool {
		// TODO: Implement isCancelled() method.
	}
}
