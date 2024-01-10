<?php

declare(strict_types=1);

namespace skyblock\items\ability;

use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\FlameParticle;
use pocketmine\world\particle\Particle;
use pocketmine\world\Position;
use skyblock\entity\PveEntity;
use skyblock\events\PlayerAttackPveEvent;
use skyblock\player\PvePlayer;
use skyblock\traits\AwaitStdTrait;
use SOFe\AwaitGenerator\Await;

class ParticleBeamAbility extends ItemAbility {
	use AwaitStdTrait;

	public function __construct(
		private Particle $particle,
		private float $baseDamage,
		private int $range,
		private Position $start,
		private ?Position $target,
		string $abilityName,
		int $manaCost,
		int $cooldown
	) {
		parent::__construct($abilityName, $manaCost, $cooldown);
	}

	protected function execute(PvePlayer $player, Item $item): bool {
		Await::f2c(function () use ($player, $item) {
			$alreadyDamaged = [];

			foreach ($this->getLine($player, 1) as $v) {
				$this->start->getWorld()->addParticle($v, $this->particle);

				$bb = new AxisAlignedBB(
					$v->x,
					$v->y,
					$v->z,
					$v->x,
					$v->y,
					$v->z
				);

				foreach (
					$this->start
						->getWorld()
						->getNearbyEntities($bb->expandedCopy(2, 2, 2))
					as $e
				) {
					if ($e instanceof PveEntity) {
						if (in_array($e->getId(), $alreadyDamaged)) {
							continue;
						}

						$ev = new PlayerAttackEntityEvent(
							$player,
							$e,
							$this->baseDamage,
							false,
							0.4
						);
						$ev->setCause($this);
						$ev->call();

						$alreadyDamaged[] = $e->getId();
					}
				}

				yield $this->getStd()->sleep(1);
			}
		});

		return true;
	}

	/**
	 * @return Vector3[]
	 */
	public function getLine(Player $player, float $addition): array {
		if ($this->target === null) {
			$direction = $player->getDirectionVector();

			$arr = [];

			for ($i = 1; $i <= $this->range; $i++) {
				$v = $this->start->addVector($direction->multiply($i));
				$arr[] = $v;
			}

			return $arr;
		}

		$direction = $this->target->subtractVector($this->start);
		$locations = [];

		for ($d = $addition; $d < $direction->length(); $d += $addition) {
			$locations[] = (clone $this->start)->addVector(
				(clone $direction)->normalize()->multiply($d)
			);
		}

		return $locations;
	}
}
