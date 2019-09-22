<?php
namespace %namespace%\Infrastructure\Repositories\Doctrine;

use %namespace%\Domain\Models\%class_name%;
use %namespace%\Domain\Infrastructure\Repositories\%class_name%RepositoryInterface;

/**
 * Class %class_name%Repository
 * @package %namespace%\Infrastructure\Repositories\Doctrine
 */
class %class_name%Repository implements %class_name%RepositoryInterface {

	use BaseRepository;

	public function __construct() {

		$this->class = %class_name%::class;
	}
}