<?php
namespace %namespace%\Application\Actions\%class_name%;

use %namespace%\Application\Actions\Commands\%class_name%\Store;
use %namespace%\Domain\Infrastructure\Repositories\%class_name%RepositoryInterface;
use Kampernet\Action\ActionChain;

class Insert%class_name% extends ActionChain {

	public function __construct(%class_name%RepositoryInterface $repository) {

		$this->add(new Store($repository));
	}
}