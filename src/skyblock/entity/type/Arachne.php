<?php

namespace skyblock\entity\type;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\entity\EntitySizeInfo;
use skyblock\entity\PveEntity;

class Arachne extends PveEntity {
	protected function getInitialSizeInfo(): EntitySizeInfo {
		return new EntitySizeInfo(
			2,
			2
		);
	}

	public static function getNetworkTypeId(): string {
		return EntityIds::SPIDER;
	}
}