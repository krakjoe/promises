promises
========

Promises in PHP using **pthreads v1.0.0+**

Example Code
============

Please see example.php

```php
$manager = new PromiseManager();
$promise = 
	new Promise($manager, new CalculateTheMeaningOfLife());
$promise
	->then(
		new AddTwo($promise))
	->then(
		new PrintMeaning($promise));

$manager->shutdown();
```
