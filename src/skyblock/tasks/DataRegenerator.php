<?php

declare(strict_types=1);

namespace skyblock\tasks;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use skyblock\player\PvePlayer;

class DataRegenerator extends Task {
	public function onRun() : void{
		/** @var PvePlayer $player */
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			if (!$player->hasLoaded()) return;
			self::regenerateHealth($player);
			self::regenerateIntelligence($player);
		}
	}

	public static function regenerateHealth(PvePlayer $player): void {
		$gainedHealth = (($player->getPveData()->getMaxHealth() * 0.01) + 1.5) * 1; //Health=((MaxHealth*0.01)+1.5)*Multiplier
		$player->getPveData()->setHealth($player->getPveData()->getHealth() + $gainedHealth);
	}

	public static function regenerateIntelligence(PvePlayer $player): void {
		$regenIntelligence = $player->getPveData()->getMaxMana() * 0.04;
		$player->getPveData()->setMana($player->getPveData()->getMana() + $regenIntelligence);
	}
}