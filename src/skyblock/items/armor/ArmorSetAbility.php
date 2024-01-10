<?php

declare(strict_types=1);

namespace skyblock\items\armor;

use pocketmine\event\Event;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\player\Player;

abstract class ArmorSetAbility implements Listener{

	public function __construct(private ArmorSet $set){ }

	public abstract function getDesiredEvents(): array;

	public abstract function tryCall(Event $event): void;

	public abstract function onActivate(Player $player, Event $event): void;

	public function getEventPriority(): int {
		return EventPriority::HIGHEST;
	}

	public function getSet() : ArmorSet{
		return $this->set;
	}
}