<?php

declare(strict_types=1);

namespace skyblock\items\components;

use customiesdevs\customies\item\component\ItemComponent;

final class ShooterComponent implements ItemComponent {
	public function __construct() {
	}

	public function getName(): string {
		return 'minecraft:shooter';
	}

	public function getValue(): array {
		return [
			'max_draw_duration' => 1,
			'launch_power_scale' => 5,
			'scale_power_by_draw_duration' => true,
			'ammunition' => [
				[
					'item' => 'minecraft:arrow',
					'use_offhand' => true,
					'search_inventory' => true,
					'use_in_creative' => true
				]
			]
		];
	}

	public function isProperty(): bool {
		return false;
	}
}
