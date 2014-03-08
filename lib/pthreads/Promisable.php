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
	abstract class Promisable extends \Stackable implements IPromisable {
		const PENDING = 0;
		const FULFILLED = 1;
		const FAILED = 2;
		
		public function onFulfill() {}
		
		public function run() {
			try {
				$this
					->onFulfill();
				$this
					->setState(PROMISABLE::FULFILLED);
			} catch (\Exception $ex) {
				$this
					->setState(PROMISABLE::FAILED, $ex);
			}
		}
		
		final protected function getError() {
			return $this->ex;
		}
		
		final protected function getState() {
			if ($this->isTerminated()) {
				return 
					PROMISABLE::FAILED;
			}

			switch ($this->state) {
				case PROMISABLE::PENDING:
				case null:
					return PROMISABLE::PENDING;
					
				default:
					return $this->state;
			}
		}
		
		final protected function setState($state, $error = null) {
			switch ($this->getState()) {
				case PROMISABLE::FULFILLED:
					throw new \RuntimeException(
						"illegal attempt to set the state of fulfilled Promiable");
				return;
				
				default:
					$this->state = $state;
					$this->error = $error;
			}
		}
		
		protected $state;
		protected $error;
	}
}