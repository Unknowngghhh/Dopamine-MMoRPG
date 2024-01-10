<?php

declare(strict_types=1);

namespace skyblock\tasks;

use pocketmine\scheduler\Task;
use skyblock\utils\Utils;

class ImportantTask extends Task {

	private \Closure $closure;

	private string $id;

	public function __construct(\Closure $closure, string $id) {
		$this->closure = $closure;
		$this->id = $id;
	}

	public function onRun(): void {
		($this->closure)();

		unset(Utils::$importantTasks[$this->id]);

	}

}