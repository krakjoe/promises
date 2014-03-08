<?php
namespace {
	include_once(sprintf(
		"%s/lib/bootstrap.php", dirname(__FILE__)));

	use \pthreads\PromiseManager;
	use \pthreads\Promise;
	use \pthreads\Promisable;
	use \pthreads\Thenable;
	
	class CalculateTheMeaningOfLife extends Promisable {
		public function onFulfill() {
			$this->meaning = 42;
		}
		
		public $meaning;
	}
	
	/* this is optional */
	trait ErrorDetector {
		public function onError(Promisable $promised) {
			printf(
				"Errors !!\n");
			
			var_dump($promised->getError());
		}
	}

	class AddTwo extends Thenable {
		use ErrorDetector;
		
		public function onFulfilled(Promisable $promised) {
			$promised->meaning += 2;
		}
	}

	class PrintMeaning extends Thenable {
		use ErrorDetector;
		
		public function onFulfilled(Promisable $promised) {
			printf(
				"The meaning of life + 2: %d\n", 
				$promised->meaning);
		}
	}

	$manager = new PromiseManager();
	$promise = 
		new Promise($manager, new CalculateTheMeaningOfLife());
	$promise
		->then(
			new AddTwo($promise))
		->then(
			new PrintMeaning($promise));

	$manager->shutdown();	
}
?>
