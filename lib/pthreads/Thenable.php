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
	
	abstract class Thenable extends Promisable implements IThenable {

		public function __construct(Promise $promise) {
			$this->promise = $promise;
		}

		public function onFulfilled	(Promisable $promised) {}
		public function onError		(Promisable $promised) {}

		public function getPromise() : Promise { return $this->promise; }

		public function run() {
			$promised = $this
				->getPromise()
				->getPromised();

			$promised->synchronized(function() use($promised) {
				try {
					switch ($promised->getState()) {
						case PROMISABLE::ERROR:
							$this->onError($promised);
						break;

						default: 
							$this->onFulfilled($promised);
					}
				} catch (\Exception $ex) {
					$promised->setState(PROMISABLE::ERROR, $ex);
				}
			});
		}

		protected $promise;
	}
}
