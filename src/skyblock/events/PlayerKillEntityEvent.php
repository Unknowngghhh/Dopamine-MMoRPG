<?php

declare(strict_types=1);

namespace skyblock\events;

use pocketmine\event\Event;
use skyblock\entity\PveEntity;
use skyblock\player\PvePlayer;

class PlayerKillEntityEvent extends Event {
	public function __construct(
		private PvePlayer $player,
		private PveEntity $entity,
		private PlayerAttackEntityEvent $source
	) {
	}

	/**
	 * @return PvePlayer
	 */
	public function getPlayer(): PvePlayer {
		return $this->player;
	}

	/**
	 * @return PveEntity
	 */
	public function getEntity(): PveEntity {
		return $this->entity;
	}

	/**
	 * @return PlayerAttackEntityEvent
	 */
	public function getSource(): PlayerAttackEntityEvent {
		return $this->source;
	}
}
