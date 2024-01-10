<?php

declare(strict_types=1);

namespace skyblock\player;

use pocketmine\block\BrewingStand;
use pocketmine\crafting\BrewingRecipe;
use pocketmine\entity\effect\SpeedEffect;
use pocketmine\player\Player;
use pocketmine\Server;

class PlayerPveData {
	private float $health;
	private float $defense;
	private float $mana;
	private float $absorption;
	private float $strength;
	private float $speed;
	private float $critChance;
	private float $critDamage;
	private float $intelligence;
	private float $seacreatureChance;
	private float $miningFortune;
	private float $farmingFortune;
	private float $foragingFortune;
	private float $magicDamage;
	private string $username;

	private float $maxHealth;
	private float $maxMana;

	//below here will be stats that do not need to be saved but just cached.
	private float $miningWisdom = 0; //At 20 Mining Wisdom, +120 Mining XP would be gained instead of +100 Mining XP.
	private float $combatWisdom = 0; //At 20 Mining Wisdom, +120 Mining XP would be gained instead of +100 Mining XP.
	private float $foragingWisdom = 0; //At 20 Mining Wisdom, +120 Mining XP would be gained instead of +100 Mining XP.
	private float $fishingSpeed = 0;
	private float $miningSpeed = 0;

	public function __construct(Player|string $username) {
		$this->username = strtolower(
			$username instanceof Player ? $username->getName() : $username
		);

		$this->health = 100.0;
		$this->intelligence = 100.0;
		$this->maxMana = 100.0;
		$this->mana = 100.0;
		$this->absorption = 0.0;

		$this->setDefense(0.0);
		$this->setStrength(0.0);
		$this->setSpeed(100.0);
		$this->setCritChance(30.0);
		$this->setCritDamage(50.0);
		$this->setSeacreatureChance(20.0);
		$this->setMiningFortune(0.0);
		$this->setFarmingFortune(0.0);
		$this->setForagingFortune(0.0);
		$this->setMagicDamage(0.0);

		$this->setMaxHealth($this->health);
		$this->setHealth($this->getHealth());
		$this->setIntelligence($this->getIntelligence());
		$this->setAbsorption($this->getAbsorption());
		$this->setMana($this->getMana());
		$this->setMaxMana($this->getMaxMana());
	}

	public function getMaxHealth(): float {
		return $this->maxHealth;
	}

	public function getMaxMana(): float {
		return $this->maxMana;
	}

	public function getMiningWisdom(): float {
		return $this->miningWisdom;
	}

	public function setMiningWisdom(float $miningWisdom): void {
		$this->miningWisdom = $miningWisdom;
	}

	public function setMaxHealth(float $maxHealth): void {
		$this->maxHealth = $maxHealth;
	}

	public function setMaxMana(float $maxMana): void {
		$this->maxMana = $maxMana;
	}

	public function getHealth(): float {
		return $this->health;
	}

	public function setHealth(float $health): void {
		$this->health = $health = min($this->getMaxHealth(), $health);

		//TODO: add grinding world check, this is for now for testing
		if ($p = $this->getPlayer()) {
			$visibleMaxHealth = match (true) {
				$health < 125 => 10 * 2,
				$health < 165 => 11 * 2,
				$health < 230 => 12 * 2,
				$health < 300 => 13 * 2,
				$health < 400 => 14 * 2,
				$health < 500 => 15 * 2,
				$health < 650 => 16 * 2,
				$health < 800 => 17 * 2,
				$health < 1000 => 18 * 2,
				$health < 1250 => 19 * 2,
				default => 20
			};
			$p->setMaxHealth($visibleMaxHealth);

			$left = (100 / $this->getMaxHealth()) * $health;

			$visibleHealth = 20 * ($left / 100);
			if ($visibleHealth > 0) {
				$p->setHealth($visibleHealth);
			}
		}
	}

	public function getAbsorption(): float {
		return $this->absorption;
	}

	public function setAbsorption(float $absorption): void {
		$this->absorption = $absorption;

		if ($p = $this->getPlayer()) {
			$visibleAbsorption = (int) min(20, floor($absorption / 50) * 2);
			$p->setAbsorption($visibleAbsorption);
		}
	}

	public function getMana(): float {
		return $this->mana;
	}

	public function setMana(float $mana): void {
		if ($this->getMaxMana() < $mana) {
			$this->mana = $this->getMaxMana();
			return;
		}
		$this->mana = $mana;
	}

	public function getDefense(): float {
		return $this->defense;
	}

	public function setDefense(float $defense): void {
		$this->defense = $defense;
	}

	public function getMiningSpeed(): float {
		return $this->miningSpeed;
	}

	public function setMiningSpeed(float $v): void {
		$this->miningSpeed = $v;
	}

	public function getStrength(): float {
		return $this->strength;
	}

	public function setStrength(float $strength): void {
		$this->strength = $strength;
	}

	public function getSpeed(): float {
		return $this->speed;
	}

	public function setSpeed(float $speed): void {
		if ($speed === 0.0) {
			return;
		}

		$this->speed = $speed;

		//TODO: add grinding world check, this is for now for testing
		if ($p = $this->getPlayer()) {
			$p->setMovementSpeed($speed / 1000);
		}
	}

	public function setMagicDamage(float $v): void {
		$this->magicDamage = $v;
	}

	public function getMagicDamage(): float {
		return $this->magicDamage;
	}

	public function getCritChance(): float {
		return $this->critChance;
	}

	public function setCritChance(float $critChance): void {
		$this->critChance = $critChance;
	}

	public function getCritDamage(): float {
		return $this->critDamage;
	}

	public function setCritDamage(float $critDamage): void {
		$this->critDamage = $critDamage;
	}

	public function getIntelligence(): float {
		return $this->intelligence;
	}

	public function setIntelligence(float $intelligence): void {
		$this->intelligence = $intelligence;
		$this->setMaxMana($intelligence);
	}

	public function getSeacreatureChance(): float {
		return $this->seacreatureChance;
	}

	public function setSeacreatureChance(float $seacreatureChance): void {
		$this->seacreatureChance = $seacreatureChance;
	}

	public function getMiningFortune(): float {
		return $this->miningFortune;
	}

	public function setMiningFortune(float $miningFortune): void {
		$this->miningFortune = $miningFortune;
	}

	public function getFarmingFortune(): float {
		return $this->farmingFortune;
	}

	public function setFarmingFortune(float $farmingFortune): void {
		$this->farmingFortune = $farmingFortune;
	}

	public function getForagingFortune(): float {
		return $this->foragingFortune;
	}

	public function setForagingFortune(float $foragingFortune): void {
		$this->foragingFortune = $foragingFortune;
	}

	public function getFishingSpeed(): float {
		return $this->fishingSpeed;
	}

	public function setFishingSpeed(float $fishingSpeed): void {
		$this->fishingSpeed = $fishingSpeed;
	}

	public function getCombatWisdom(): float {
		return $this->combatWisdom;
	}

	public function setCombatWisdom(float $combatWisdom): void {
		$this->combatWisdom = $combatWisdom;
	}

	public function getForagingWisdom(): float {
		return $this->foragingWisdom;
	}

	public function setForagingWisdom(float $foragingWisdom): void {
		$this->foragingWisdom = $foragingWisdom;
	}

	public function getUsername(): string {
		return $this->username;
	}

	public function setUsername(string $username): void {
		$this->username = $username;
	}

	public function getPlayer(): ?Player {
		return Server::getInstance()->getPlayerExact($this->username);
	}
}
