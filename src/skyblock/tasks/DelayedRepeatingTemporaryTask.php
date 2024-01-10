<?php

declare(strict_types=1);

namespace skyblock\tasks;

use Closure;
use pocketmine\scheduler\Task;

class DelayedRepeatingTemporaryTask extends Task {

	private int $repeatFor;
	private Closure $closure;

	public function __construct(int $repeatFor, Closure $closure) {
		$this->repeatFor = $repeatFor;
		$this->closure = $closure;
	}

	public function onRun(): void {
		$this->repeatFor--;

		$bool = ($this->closure)();

		if($this->repeatFor < 1 || $bool === true) {
			$this->getHandler()->cancel();
		}
	}
}