<?php
declare(strict_types=1);

namespace skyblock\items\components;

use customiesdevs\customies\item\component\ItemComponent;

final class UseAnimationComponent implements ItemComponent {

	private string $animation;

	public function __construct(string $animation) {
		$this->animation = $animation;
	}

	public function getName(): string {
		return "minecraft:use_animation";
	}

	public function getValue(): string {
		return $this->animation;
	}

	public function isProperty(): bool {
		return true;
	}
}