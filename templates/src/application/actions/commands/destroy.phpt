<?php
namespace %namespace%\Application\Actions\Commands\%class_name%;

use %namespace%\Application\Validation\%class_name%Validation;
use %namespace%\Domain\Infrastructure\Repositories\%class_name%RepositoryInterface;
use Lang;
use Kampernet\Action\Command;
use Validator;

class Destroy extends Command {

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

		$validation = Validator::make(json_decode(json_encode($request), true), %class_name%Validation::$destroy);

		if ($validation->passes()) {
			$this->repository->delete($request->id);
			$response->messages['success.%snake_case_class_name%.destroy'] = ['message' => Lang::get('messages.%snake_case_class_name%.destroy.success')];
		} else {
			$errors = $validation->errors()->toArray();
			$response->messages['error.%snake_case_class_name%.destroy'] = $errors;
		}

		return true;
	}

	/**
	 * The command undo method
	 */
	public function undo() {

		return $this->previous;
	}
}