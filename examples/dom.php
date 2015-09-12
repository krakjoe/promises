<?php
namespace {
	include_once(sprintf(
		"%s/../vendor/autoload.php", dirname(__FILE__)));

	use \pthreads\PromiseManager;
	use \pthreads\Promise;
	use \pthreads\Promisable;
	use \pthreads\Thenable;
	
	class SafeLogger extends Threaded {
		public function log($message, ... $args) {
			$this->synchronized(function() use($message, $args){
				printf($message, ...$args);
			});
		}
	}
	
	class DOMFetcher extends Promisable {

		public function __construct(SafeLogger $logger, $name) {
			$this->setLogger($logger);
			$this->setName($name);
		}
		
		public function onFulfill() {
			$data = file_get_contents(
				"http://php.net/{$this->name}");

			if (!$data) {
				throw new \RuntimeException(
					"failed to download documentation for {$this->name}");
			}
			
			$this->setData($data);
		}
		
		public function setLogger(SafeLogger $logger) 	{ $this->logger = $logger; }
		public function getLogger() 					{ return $this->logger; }
		
		public function setName($name) 					{ $this->name = $name; }
		public function getName() 						{ return $this->name; }
		
		public function setData($data) 					{ $this->data = $data; }
		public function getData() 						{ return $this->data; }
		
		public function setDescription($text) 			{ $this->description = trim((string) $text); }
		public function getDescription() 				{ return $this->description; }
		
		protected $logger;
		protected $name;
		protected $data;
		protected $description;
		protected $garbage;
	}
	
	trait DOMError {
		public function onError(Promisable $promised) {
			$promised->getLogger()
				->log("Oh noes: %s\n", (string) $promised->getError());
			/* allow the object to be collected */
			$promised->setGarbage();
		}
	}

	class DOMParser extends Thenable {
		use DOMError;
		
		public function onFulfilled(Promisable $promised) {
			$dom = new DOMDocument();
			if (@$dom->loadHTML($promised->getData())) {
				$xpath = new DOMXPath($dom);
				foreach ($xpath->query("//span[@class='dc-title']") as $found) {
					$promised
						->setDescription($found->nodeValue);
					break;
				}
			} else {
				throw new \RuntimeException(
					"failed to load HTML at {$promised->url}");
			}
		}
	}
	
	class DOMPrinter extends Thenable {
		use DOMError;
		
		public function onFulfilled(Promisable $promised) {
			$promised->getLogger()
				->log("%s: %s\n", 
					$promised->getName(), 
					$promised->getDescription());
			/* allow the object to be collected */
			$promised->setGarbage();
		}
	}

	/* start a pool of 8 threads to fulfill promises */
	$manager = new PromiseManager(8);
	
	/* get 100 random internal function names */
	$functions = get_defined_functions();
	foreach (array_rand($functions["internal"], 100) as $function)
		$names[] = $functions["internal"][$function];
	$functions = $names;
	
	/* create a logger for threads in the pool to share */
	$logger = new SafeLogger();
	
	/* create promises */
	foreach ($functions as $index => $function) {
		$functions[$index] = 
			new Promise($manager, new DOMFetcher($logger, $function));
		$functions[$index]
			->then(new DOMParser($functions[$index]))
			->then(new DOMPrinter($functions[$index]));
	}
	
	/* begin to collect ... */
	while ($manager->collect(function($task){
		return $task->isGarbage();
	})) continue;
	
	/* we are done */
	$manager->shutdown();
}
?>
