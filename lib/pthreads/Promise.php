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
	
	final class Promise extends Promisable {

		public function __construct($manager, Promisable $promised) {
			if (!($manager instanceof PromiseManager) &&
				(!is_array($manager) || !($manager[0] instanceof PromiseManager))) {
				throw new InvalidArgumentException(
					"The manager passed to constructor was invalid,".
					" expected PromiseManager or [PromiseManager, int]");
			}
			
			if (is_array($manager)) {
				$this->manager = $manager[0];
				$this->worker  = $manager[0]
					->submitTo($manager[1], $promised);
			} else {
				$this->worker  = $manager
					->submit($promised);
				$this->manager = $manager;
			}
			
			$this->promised = $promised;
		}

		public function then(Thenable $thenable) {
			return $this->manager
				->manage($this, $thenable);
		}

		public function getWorker() 				{ return $this->worker;	}
		public function getManager()				{ return $this->manager; }
		public function getPromised($key = null)	{
			if ($key != null)
				return $this->promised[$key];
			return $this->promised;
		}
		
		protected $manager;
		protected $worker;
		protected $promised;
	}
}
