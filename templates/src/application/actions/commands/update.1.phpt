<?php
namespace %namespace%\Application\Actions\Commands\%class_name%;

use %namespace%\Application\Validation\%class_name%Validation;
use %namespace%\Domain\Infrastructure\Repositories\%class_name%RepositoryInterface;
use %namespace%\Domain\Models\%class_name%;
use Lang;
use Validator;
use Kampernet\Action\Command;

class Update extends Command {

	/**
	 * @var %class_name%RepositoryInterface
	 */
	private $repository;

	/**
	 * @param %class_name%RepositoryInterface $repository
	 */
	public function __construct(%class_name%RepositoryInterface $repository) {

		$this->repository = $repository;
	}

	/**
	 * The command execute method
	 *
	 * @param \%namespace%\Application\Request $request
	 * @param \%namespace%\Application\Response $response
	 * @return boolean
	 */
	public function execute($request, $response = null) {

		/**
		 * @var %class_name% $%camel_case_class_name%
		 */
		$%camel_case_class_name% = $this->repository->find($request->id);
