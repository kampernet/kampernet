<?php
namespace %namespace%\Application\Actions\%class_name%;

use %namespace%\Application\Actions\Commands\%class_name%\Update;
use %namespace%\Domain\Infrastructure\Repositories\%class_name%RepositoryInterface;
use Kampernet\Action\ActionChain;

class Update%class_name% extends ActionChain {

	public function __construct(%class_name%RepositoryInterface $repository) {

		$this->add(new Update($repository));
	}
}