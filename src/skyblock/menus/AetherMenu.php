<?php

declare(strict_types=1);

namespace skyblock\menus;

use Closure;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\command\defaults\StopCommand;
use pocketmine\inventory\Inventory;
use pocketmine\network\mcpe\protocol\ClientCacheMissResponsePacket;
use pocketmine\player\Player;
use skyblock\caches\data\MenuCache;
use skyblock\Main;
use skyblock\tasks\TickableMenuTask;

abstract class AetherMenu {

	const READONLY = 0;
	const NORMAL = 1;

	/** @var InvMenu */
	protected $menu;

	protected $type = self::READONLY;

	protected bool $dupeCheck = true;

	/** @var TickableMenuTask[] */
	protected array $tasks = [];

	protected bool $closed = false;

	public function __construct() {
		$this->menu = $this->constructMenu();
		if($this->type === self::NORMAL) {
			$this->getMenu()->setListener(\Closure::fromCallable([$this, "onNormalTransaction"]));
		} else {
			$this->getMenu()->setListener(InvMenu::readonly(\Closure::fromCallable([$this, "onReadonlyTransaction"])));
		}

		$this->getMenu()->setInventoryCloseListener(\Closure::fromCallable([$this, "onInternalClose"]));
	}


	protected function startTicking(int $slot, int $interval, int $tickAmount = null, int $delay = 0): void {
		$task = new TickableMenuTask($this, $slot, $tickAmount);
		Main::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($task, $delay, $interval);

		$this->tasks[] = $task;
	}

	public function onReadonlyTransaction(InvMenuTransaction $transaction): void {}

	public function onNormalTransaction(InvMenuTransaction $transaction): InvMenuTransactionResult {
		return $transaction->continue();
	}

	public function onInternalClose(Player $player, Inventory $inventory): void {
		$this->onClose($player, $inventory);

		MenuCache::getInstance()->set($player->getName(), 0);
		$this->closed = true;
	}

	public function onClose(Player $player, Inventory $inventory): void {
		foreach($this->tasks as $task){
			if($task->getTickAmount() !== null && $task->getHandler() !== null && $task->getHandler()->isCancelled() === false){
				$task->getHandler()->cancel();
			}
		}
	}

	public function send(Player $player): void {
		if($player->isOnline()){
			if($this->dupeCheck){
				foreach($this->getMenu()->getInventory()->getContents() as $i => $content){
					$content->getNamedTag()->setString("menuItem", "ye");
					$this->getMenu()->getInventory()->setItem($i, $content);
				}
			}

			if(MenuCache::getInstance()->get($player->getName()) === 1){
				//$player->sendMessage(Main::PREFIX . "You already are viewing an invmenu");
				return;
			}

			$this->menu->send($player, null, function(bool $success) use($player): void {
				if($success){
					MenuCache::getInstance()->set($player->getName(), 1);
				}
			});
		}
	}

	public function getMenu(): InvMenu {
		return $this->menu;
	}

	/**
	 * @param int $slot
	 * @param bool $lastTick it's true if it's the last tick of the slot if not then it's false
	 * @return bool if returned false then cancel the ticking task
	 */
	public function onTick(int $slot, bool $lastTick): bool {
		return false;
	}

	/**
	 * @return InvMenu
	 * this functions constructs a new menu
	 */
	abstract public function constructMenu(): InvMenu;
}