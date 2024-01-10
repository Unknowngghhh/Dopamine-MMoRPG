<?php

declare(strict_types=1);

namespace skyblock\traits;

use skyblock\Main;
use SOFe\AwaitStd\AwaitStd;

trait AwaitStdTrait {

	public function getStd(): AwaitStd {
		return Main::getInstance()->getStd();
	}
}