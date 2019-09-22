<?php
namespace %namespace%\Application\Actions\Commands\%class_name%;

use %namespace%\Domain\Infrastructure\Repositories\%class_name%RepositoryInterface;
use Kampernet\Action\Command;
use Lang;

class Show extends Command {

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

		$response->data['%snake_case_class_name%'] = $this->repository->find($request->id);

		if (is_null($response->data['%snake_case_class_name%'])) {
			$response->messages['error.%snake_case_class_name%.show'] = ['message' => Lang::get('messages.%snake_case_class_name%.show.error_not_found')];
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