<?php
namespace %namespace%\Application\Actions\%class_name%;

use %namespace%\Application\Actions\Commands\%class_name%\Index;
use %namespace%\Domain\Infrastructure\Repositories\%class_name%RepositoryInterface;
use Kampernet\Action\ActionChain;

class Get%class_name_plural% extends ActionChain {

	public function __construct(%class_name%RepositoryInterface $repository) {

		$this->add(new Index($repository));
	}
}