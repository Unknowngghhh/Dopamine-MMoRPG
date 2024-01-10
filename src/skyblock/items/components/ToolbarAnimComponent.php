<?php
declare(strict_types=1);

namespace skyblock\items\components;

use customiesdevs\customies\item\component\ItemComponent;

final class ToolbarAnimComponent implements ItemComponent {

	private bool $bool;

	public function __construct(bool $bool) {
		$this->bool = $bool;
	}

	public function getName(): string {
		return "minecraft:animates_in_toolbar";
	}

	public function getValue(): bool {
		return $this->bool;
	}

	public function isProperty(): bool {
		return true;
	}
	
}