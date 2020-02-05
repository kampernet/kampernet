<?php
namespace %namespace%\Domain\Infrastructure\Repositories;

/**
 * Interface BaseRepositoryInterface
 * @package %namespace%\Domain\Repositories
 */
interface BaseRepositoryInterface {

    /**
     * @param $id
     * @param bool $eager
     * @param bool $includeDeleted
     * @return mixed
     */
    public function find($id, $eager = false, $includeDeleted = false);

    /**
     * @param array $params
     * @param bool $first
     * @param int $page
     * @param int $numPerPage
     * @param bool $orderBy
     * @param bool $deleted
     * @return mixed
     */
    public function filter(array $params = [], $first = false, $page = 1, $numPerPage = 100, $orderBy = false, $deleted = false);

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * @param $id
     * @return mixed
     */
    public function restore($id);

}