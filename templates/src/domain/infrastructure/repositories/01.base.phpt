<?php
namespace %namespace%\Domain\Infrastructure\Repositories;

/**
 * Interface BaseRepositoryInterface
 * @package %namespace%\Domain\Repositories
 */
interface BaseRepositoryInterface {

	/**
	 * @param int $id
	 * @return mixed
	 */
	public function find($id);

	/**
	 * @param array $params
	 * @return mixed
	 */
	public function match(array $params = []);

	/**
	 * @param mixed $object
	 * @return mixed
	 */
	public function save($object);

	/**
	 * @param int $id
	 * @return mixed
	 */
	public function delete($id);

}