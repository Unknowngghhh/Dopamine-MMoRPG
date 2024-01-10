<?php

declare(strict_types=1);

namespace skyblock\traits;

use skyblock\Main;

trait HandlerTrait {

	protected Main $plugin;

	final protected function __construct() {
		if (self::$instance !== null) {
			return;
		}

		self::setInstance($this);
		$this->plugin = Main::getInstance();
		$this->onEnable($this->plugin);
	}


	public function onEnable() : void {}

	public function onDisable() : void {}

	/** @var self|null */
	private static $instance = null;

	private static function make() : static {
		return new self;
	}

	public static function initialise() : void {
		if (self::$instance === null) {
			self::setInstance(self::make());
		}
	}

	public static function isInitialized(): bool {
		return self::$instance !== null;
	}

	public static function getInstance() : static {
		if (self::$instance === null) {
			self::setInstance(self::make());
		}
		return self::$instance;
	}

	public static function setInstance(self $instance) : void {
		self::$instance = $instance;
	}

	public static function reset() : void {
		self::$instance = null;
	}
}