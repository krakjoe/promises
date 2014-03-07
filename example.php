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

	class AddTwo extends Thenable {
		public function onComplete(Stackable $promised) {
			$promised->meaning += 2;
		}
	
		public function onError(Stackable $promised) {
			printf("Something went wong !\n");
		}
	
		public function onProgress(Stackable $promised) {}
	}

	class PrintMeaning extends Thenable {
	
		public function onComplete(Stackable $promised) {
			printf(
				"The meaning of life + 2: %d\n", 
				$promised->meaning);
		}
	
		public function onError(Stackable $promised) {
			echo "I failed !!\n";
		}
	
		public function onProgress(Stackable $promised) {}
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
