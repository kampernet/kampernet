<?php
namespace %namespace%\Infrastructure\Repositories\Doctrine;

use EntityManager;
use %namespace%\Domain\Models\Status;

trait BaseRepository {

	private $class;

	/**
	 * @param int $id
	 * @return mixed
	 */
	public function find($id) {

		$object = EntityManager::getRepository($this->class)->find($id);
		if ($object && property_exists($object, 'status')) {
			if ($object->status == Status::DELETED) {
				$object = null;
			}
		}

		return $object;
	}

	/**
	 * @param array $params
	 * @return array
	 */
	public function match(array $params = []) {

		if (property_exists($this->class, 'status')) {
			$params['status'] = Status::ACTIVE;
		}

		return EntityManager::getRepository($this->class)->findBy($params);
	}

	/**
	 * @param mixed $object
	 * @return mixed
	 */
	public function save($object) {

		EntityManager::persist($object);
		EntityManager::flush();

		return $object;
	}

	/**
	 * @param int $id
	 * @return void
	 */
	public function delete($id) {

		$object = $this->find($id);
		if (property_exists($object, 'status')) {
			$object->status = Status::DELETED;
			EntityManager::persist($object);
		} else {
			EntityManager::remove($object);
		}

		EntityManager::flush();
	}
}