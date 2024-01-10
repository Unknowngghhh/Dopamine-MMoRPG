<?php

declare(strict_types=1);

namespace muqsit\random;

use Generator;
use pocketmine\utils\Binary; // https://github.com/pmmp/BinaryUtils
use pocketmine\utils\Random; // https://github.com/pmmp/PocketMine-MP/blob/60938c8c9df4bf21d096f142c6579dfad92eb8c2/src/utils/Random.php

/**
 * @phpstan-template T https://gist.github.com/Muqsit/5042779c0e87fd55e55560f83e24af69
 */
final class WeightedRandom{

	private Random $random;

	/** @var float[] */
	private array $probabilities = [];

	/** @var int[] */
	private array $aliases;

	/**
	 * @var T[]
	 *
	 * @phpstan-var T[]
	 */
	private array $indexes = [];

	public function __construct(){
	}

	/**
	 * @param T $value
	 * @param float $weight
	 *
	 * @phpstan-param T $value
	 */
	public function add(mixed $value, float $weight) : void{
		$this->probabilities[] = $weight;
		$this->indexes[] = $value;
	}

	public function count() : int{
		return count($this->probabilities);
	}

	private function normalize() : void{
		$sum = array_sum($this->probabilities);
		foreach($this->probabilities as &$weight){
			$weight /= $sum;
		}
	}

	public function setup() : void{
		$probabilities_c = $this->count();
		if($probabilities_c === 0){
			return;
		}

		// Store the underlying generator.
		$this->random = new Random(Binary::readLong(Binary::writeInt(mt_rand()) . Binary::writeInt(mt_rand())));
		$this->aliases = [];

		$this->normalize();

		// Compute the average probability and cache it for later use.
		$average = 1.0 / $probabilities_c;

		$probabilities = $this->probabilities;

		// Create two stacks to act as worklists as we populate the tables.
		$small = [];
		$large = [];

		// Populate the stacks with the input probabilities.
		for($i = 0; $i < $probabilities_c; ++$i){
			/**
			 * If the probability is below the average probability, then we add
			 * it to the small list; otherwise we add it to the large list.
			 */
			if($probabilities[$i] >= $average){
				$large[] = $i;
			}else{
				$small[] = $i;
			}
		}

		/**
		 * As a note: in the mathematical specification of the algorithm, we
		 * will always exhaust the small list before the big list.  However,
		 * due to floating point inaccuracies, this is not necessarily true.
		 * Consequently, this inner loop (which tries to pair small and large
		 * elements) will have to check that both lists aren't empty.
		 */
		while(count($small) > 0 && count($large) > 0){
			/* Get the index of the small and the large probabilities. */
			$less = array_pop($small);
			$more = array_pop($large);

			/**
			 * These probabilities have not yet been scaled up to be such that
			 * 1/n is given weight 1.0.  We do this here instead.
			 */
			$this->probabilities[$less] = $probabilities[$less] * $probabilities_c;
			$this->aliases[$less] = $more;

			/**
			 * Decrease the probability of the larger one by the appropriate
			 * amount.
			 */
			$probabilities[$more] = ($probabilities[$more] + $probabilities[$less]) - $average;

			/**
			 * If the new probability is less than the average, add it into the
			 * small list; otherwise add it to the large list.
			 */
			if($probabilities[$more] >= 1.0 / $probabilities_c){
				$large[] = $more;
			}else{
				$small[] = $more;
			}
		}

		/**
		 * At this point, everything is in one list, which means that the
		 * remaining probabilities should all be 1/n.  Based on this, set them
		 * appropriately.  Due to numerical issues, we can't be sure which
		 * stack will hold the entries, so we empty both.
		 */
		while(count($small) > 0){
			$this->probabilities[array_pop($small)] = 1.0;
		}
		while(count($large) > 0){
			$this->probabilities[array_pop($large)] = 1.0;
		}
	}

	/**
	 * @param int $count
	 * @return Generator<int>
	 */
	private function generateIndexes(int $count) : Generator{
		$probabilities_c = count($this->probabilities);
		if($probabilities_c > 0){
			while(--$count >= 0){
				$index = $this->random->nextBoundedInt($probabilities_c);
				yield $this->random->nextFloat() <= $this->probabilities[$index] ? $index : $this->aliases[$index];
			}
		}
	}

	/**
	 * @param int $count
	 * @return Generator<T>
	 *
	 * @phpstan-return Generator<T>
	 */
	public function generate(int $count) : Generator{
		foreach($this->generateIndexes($count) as $index){
			yield clone $this->indexes[$index];
		}
	}
}