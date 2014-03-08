promises
========

Promises in PHP using **pthreads v1.0.0+**

Promise Manager
===============

The Promise Manager represents a pool of threads used to fulfill promises asynchronously.

The only public API for the Promise Manager is the constructor:

	public function __construct($size = 4, $worker = \Worker::class, $ctor = [])

See Pool::__construct in the PHP manual; it is inherited from Pool.

Promise
=======

A Promise represents a promise to execute the public interface of a Promisable at some time in the future:

	public Promise Promise::__construct(PromiseManager $manager, Promisable $promisable)

A Promise provides the ability to schedule the subsequent execution of the public interface of a Thenable.

	public Promise Promise::then(Thenable $then)

Interfaces
==========

IPromisable

	public void IPromisable::onFulfill();

IThenable
	
	public void IThenable::onFulfilled(Promisable $promised);
	public void IThenable::onError(Promisable $promised);

Notes
=====

Exceptions thrown by IPromisable objects will be caught and bubble up to invoke IThenable::onError

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

This is a work in progress
--------------------------

*Note that, this is a work in progress, that does not aim for maximum compatibility with any established standard but rather
an easy to use implementation, that is compatible with and takes full advantage of pthreads.*
