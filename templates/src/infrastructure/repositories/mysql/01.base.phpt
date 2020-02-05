<?php
namespace %namespace%\Infrastructure\Repositories\MySql;

use DB;
use DateTime;
use InvalidArgumentException;
use %namespace%\Domain\Infrastructure\Repositories\BaseRepositoryInterface;
use stdClass;

abstract class BaseRepository implements BaseRepositoryInterface {

    /**
     * @var string
     */
    public $tableName;

    /**
     * @var string
     */
    public $className;

    /**
     * @var bool
     */
    public $softDelete = false;

    /**
     * @param int $id
     * @param bool $eager
     * @param bool $includeDeleted
     * @return mixed
     */
    public function find($id, $eager = false, $includeDeleted = false) {

        $this->sanity();
        if ($this->softDelete && !$includeDeleted) {
            $rec = DB::table($this->tableName)
                ->whereNull('deleted_at')
                ->where('id', '=', $id)
                ->first();
        } else {
            $rec = DB::table($this->tableName)->find($id);
        }

        return $this->hydrate($rec, $eager);
    }

    /**
     * @param array $params
     * @param bool $first
     * @param int $page
     * @param int $numPerPage
     * @param bool $orderBy
     * @param bool $deleted
     * @return stdClass[]|stdClass
     */
    public function filter(array $params = [], $first = false, $page = 1, $numPerPage = 100, $orderBy = false, $deleted = false) {

        /**
         * @var stdClass[] $matches
         */
        $this->sanity();
        $direction = $orderBy['direction'] ?? 'asc';

        $matches = DB::table($this->tableName)->where($params);
        if ($this->softDelete) {
            if (!$deleted) {
                $matches = $matches->whereNull('deleted_at');
            } else {
                $matches = $matches->whereNotNull('deleted_at');
            }
        }
        if ($orderBy) {
            $matches = $matches->orderBy($orderBy['column'], $direction);
        }

        if ($first) {
            $matches = $matches->first();
            $results = $this->hydrate($matches);
        } else {
            $results = [];
            $matches = $matches->take($numPerPage)->offset(($page - 1) * $numPerPage)->get();
            foreach ($matches as $rec) {
                $results[] = $this->hydrate($rec);
            }
        }

        return $results;
    }

    /**
     * @param $id
     */
    public function delete($id) {

        $this->sanity();
        $deletedAt = $updatedAt = new DateTime();

        if ($this->softDelete) {
            DB::table($this->tableName)->where('id', '=', $id)->update([
                'deleted_at' => $deletedAt
            ]);
        } else {
            DB::table($this->tableName)->where('id', '=', $id)->delete();
        }
    }

    /**
     * @param $id
     */
    public function restore($id) {

        $this->sanity();
        $updatedAt = new DateTime;

        if ($this->softDelete) {
            DB::table($this->tableName)
                ->where('id', '=', $id)
                ->update([
                    'deleted_at' => null,
                    'updated_at' => $updatedAt,
                ]);
        }
    }

    /**
     * a sanity check for a better error message
     */
    protected function sanity() {

        if (!$this->tableName) {
            throw new InvalidArgumentException("Table name not set");
        }

        if (!$this->className) {
            throw new InvalidArgumentException("Class name not set");
        }
    }

    /**
     * @param $rec
     * @param bool $eager
     * @return mixed
     */
    abstract protected function hydrate($rec, $eager = false);

}
