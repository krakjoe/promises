<?php
/*
  +----------------------------------------------------------------------+
  | pthreads                                                             |
  +----------------------------------------------------------------------+
  | Copyright (c) Joe Watkins 2012 - 2014                                |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author: Joe Watkins <joe.watkins@live.co.uk>                         |
  +----------------------------------------------------------------------+
 */
namespace pthreads {
	abstract class Promisable extends \Collectable implements IPromisable {
		const PENDING = 0;
		const FULFILLED = 1;
		const ERROR = 2;

		public function run() {
			$this->setState(PROMISABLE::PENDING);
			try {
				$this->onFulfill();
			} catch (\Exception $ex) {
				$this->setState(PROMISABLE::ERROR, $ex);
			} finally {
				switch ($this->state) {
					case PROMISABLE::PENDING:
					case null:
						$this->setState(PROMISABLE::FULFILLED);
					break;
				}
			}
		}

		public function getState() {
			return $this->synchronized(function(){
				if ($this->isTerminated())
					return PROMISABLE::ERROR;

				switch ($this->state) {
					case PROMISABLE::PENDING:
					case null:
						return PROMISABLE::PENDING;
					
					default:
						return $this->state;
				}
			});
		}
		
		public function setState($state, $error = null) {
			$this->synchronized(function() use($state, $error) {
				switch ($state) {
					case PROMISABLE::FULFILLED:
					case PROMISABLE::PENDING:
					case PROMISABLE::ERROR:
						$this->state = $state;
						$this->error = $error;
					return;
				
					default:
						throw new \RuntimeException(
							"attempt to set unrecognized state ({$state})");
				}
			});
		}
		
		public function getError() { return $this->error; }
		public function onFulfill() { }
		
		protected $state;
		protected $error;
	}
}
