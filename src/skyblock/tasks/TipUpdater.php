<?php

declare(strict_types=1);

namespace skyblock\tasks;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use skyblock\player\PvePlayer;
use skyblock\utils\PveUtils;

class TipUpdater extends Task{
	public function onRun() : void{
		/** @var PvePlayer $player */
		foreach(Server::getInstance()->getOnlinePlayers() as $player){
			self::updateTip($player);
		}
	}

	public static function updateTip(PvePlayer $player, string $extra = ""): void {
		$data = $player->getPveData();
		$maxHealth = number_format($data->getMaxHealth());
		$health = number_format($data->getHealth());
		$maxMana = number_format($data->getMaxMana());
		$mana = number_format($data->getMana());
		$defense = number_format($data->getDefense());
		$tab = "	";

		$tip = '';
		$tip .= "§c{$health}/{$maxHealth}" . PveUtils::getHealthSymbol();
		if ($defense > 0) {
			$tip .= $tab . "§a{$defense}" . PveUtils::getDefenseSymbol();
		}
		$tip .= $tab . "§b{$mana}/{$maxMana}" . PveUtils::getIntelligenceSymbol();

		if($extra !== ""){
			$tip .= "\n" . $extra;
		}

		$player->sendTip($tip);
	}
}