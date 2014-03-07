<?php


namespace {	
	include_once(sprintf(
		"%s/lib/bootstrap.php", dirname(__FILE__)));

	use \pthreads\PromiseManager;
	use \pthreads\Promise;
	use \pthreads\Thenable;
	
	class CalculateTheMeaningOfLife extends Stackable {
		public function run() {
			$this->meaning = 42;
		}
	}
	
	class ProcessError extends Thenable {
		
		public function onError(Stackable $promised) {
			printf("Errors !!\n");
		}
	}

	class AddTwo extends Thenable {

		public function onComplete(Stackable $promised) {
			$promised->meaning += 2;
		}
	}

	class PrintMeaning extends Thenable {

		public function onComplete(Stackable $promised) {
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
			new ProcessError($promise))
		->then(
			new AddTwo($promise))
		->then(
			new PrintMeaning($promise));

	$manager->shutdown();	
}
?>
