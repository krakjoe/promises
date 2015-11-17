<?php
namespace {
	include_once("vendor/autoload.php");

	use \pthreads\PromiseManager;
	use \pthreads\Promise;
	use \pthreads\Promisable;
	use \pthreads\Thenable;

	class CalculateTheMeaningOfLife extends Promisable {
		public function onFulfill() {
			$this->meaning = 42;
			
			/* Throwing an exception here will cause invocation of 
				AddTwo::onError */
		}
		
		public $meaning;
	}
	
	/* this is optional */
	trait ErrorDetector {
		public function onError(Promisable $promised) {
			printf(
				"Oh noes: %s\n", (string) $promised->getError());
		}
	}

	class AddTwo extends Thenable {
		use ErrorDetector;
		
		public function onFulfilled(Promisable $promised) {
			$promised->meaning += 2;

			/* throwing an exception here will cause invocation of 
				PrintMeaning::onError */
		}
	}

	class PrintMeaning extends Thenable {
		use ErrorDetector;
		
		public function onFulfilled(Promisable $promised) {
			printf(
				"The meaning of life + 2: %d\n", 
				$promised->meaning);
			
			/* You can access exceptions thrown here using $promised->getError */
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
