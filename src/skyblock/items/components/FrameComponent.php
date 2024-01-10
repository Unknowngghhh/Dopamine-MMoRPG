<?php
declare(strict_types=1);

namespace skyblock\items\components;

use customiesdevs\customies\item\component\ItemComponent;

final class FrameComponent implements ItemComponent {

	private int $count;

	public function __construct(int $count) {
		$this->count = $count;
	}

	public function getName(): string {
		return "minecraft:frame_count";
	}

	public function getValue(): int {
		return $this->count;
	}

	public function isProperty(): bool {
		return true;
	}
}