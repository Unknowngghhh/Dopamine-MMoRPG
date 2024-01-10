<?php

declare(strict_types=1);

namespace skyblock\events;

use pocketmine\event\Event;
use skyblock\entity\PveEntity;
use skyblock\player\PvePlayer;

class EntityKillPlayerEvent extends Event {
	public function __construct(
		private PvePlayer $player,
		private PveEntity $entity,
		private EntityAttackPlayerEvent $source
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
	 * @return EntityAttackPlayerEvent
	 */
	public function getSource(): EntityAttackPlayerEvent {
		return $this->source;
	}
}
