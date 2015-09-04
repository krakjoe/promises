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

	class Pool {
		public function __construct($size = 1, $class = \Worker::class, $ctor = []) {
			$this->size = $size;
			$this->class = $class;
			$this->ctor = $ctor;
			$this->last = 0;
			$this->workers = [];
		}

		public function submit(\Collectable $work) {
			if ($this->last > $this->size)
				$this->last = 0;

			if (!isset($this->workers[$this->last])) {
				$this->workers[$this->last] = 
					new $this->class(...$this->ctor);
				$this->workers[$this->last]->start();
			}

			if ($this->workers[$this->last]->stack($work)) {
				return $this->last++;
			}
		}

		public function submitTo(int $worker, \Collectable $work) {
			if (isset($this->workers[$worker])) {
				if ($this->workers[$worker]->stack($work)) {
					return $worker;
				}
			}
		}

		public function collect(Closure $collector) {
			$total = 0;
			foreach ($this->workers as $worker)
				$total += $worker->collect($collector);
			return $total;
		}

		public function shutdown() {
			foreach ($this->workers as $worker) {
				$worker->shutdown();
			}
			unset($this->workers);
		}
		
		private $workers;
		private $size;
		private $class;
		private $ctor;
		private $last;
	}

	class PromiseManager extends Pool {
	    
		public function manage(Promise $promise, Thenable $thenable) {
			return new Promise(
				[$this, $promise->getWorker()], $thenable);
		}
		
		public function hasWork() { return count($this->work); }
	}
}
