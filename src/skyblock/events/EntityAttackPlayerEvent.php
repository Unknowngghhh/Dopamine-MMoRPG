<?php

declare(strict_types=1);

namespace skyblock\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use skyblock\entity\PveEntity;
use skyblock\player\PvePlayer;

class EntityAttackPlayerEvent extends Event implements Cancellable{
	use CancellableTrait;

	/** @var array<string, int> */
	private array $damageAmplifiers = [];
	/** @var array<string, int> */
	private array $damageReducers = [];
	/** @var array<string, int> */
	private array $damageMultipliers = [];
	/** @var array<string, int> */
	private array $damageDividers = [];

	public function __construct(
		protected PveEntity $entity,
		protected PvePlayer $player,
		protected float $baseDamage,
		protected float $knockback = 0.4,
	){ }


	public function getKnockback() : float{
		return $this->knockback;
	}

	public function setKnockback(float $knockback) : void{
		$this->knockback = $knockback;
	}

	public function increaseDamage(float $damage, string $cause): void {
		$this->damageAmplifiers[$cause] = $damage;
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

	public function getFinalDamage() : float{
		$damage = $this->getBaseDamage();
		$damage += array_sum($this->damageAmplifiers);
		$damage -= array_sum($this->damageReducers);
		$damage *= array_sum($this->damageMultipliers) + 1;
		$damage /= array_sum($this->damageDividers) + 1;

		return $damage;
	}

	/**
	 * @return PveEntity
	 */
	public function getEntity() : PveEntity{
		return $this->entity;
	}




	/**
	 * @return PvePlayer
	 */
	public function getPlayer() : PvePlayer{
		return $this->player;
	}


	/**
	 * @return float
	 */
	public function getBaseDamage() : float{
		return $this->baseDamage;
	}

	/**
	 * @param float $damage
	 */
	public function setBaseDamage(float $damage) : void{
		$this->baseDamage = $damage;
	}
}