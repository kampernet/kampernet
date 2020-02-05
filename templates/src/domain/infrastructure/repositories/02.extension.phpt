<?php
namespace %namespace%\Domain\Infrastructure\Repositories;

use %namespace%\Domain\Model\%class_name%;

/**
 * Interface %class_name%RepositoryInterface
 * @package %namespace%\Domain\Infrastructure\Repositories
 */
interface %class_name%RepositoryInterface extends BaseRepositoryInterface {

    /**
     * @param %class_name% $%camel_case_class_name%
     * @return %class_name%
     */
    public function save(%class_name% $%camel_case_class_name%) : ?%class_name%;

}