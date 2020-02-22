namespace {{$namespace}}\Domain\Infrastructure\Repositories;

use {{$namespace}}\Domain\Model\{{$className}};

/**
* Interface {{$className}}RepositoryInterface
* @package {{$namespace}}\Domain\Infrastructure\Repositories
*/
interface {{$className}}RepositoryInterface extends BaseRepositoryInterface {

/**
* @param {{$className}} ${{$camelCaseClassName}}
* @return {{$className}}
*/
public function save({{$className}} ${{$camelCaseClassName}}) : ?{{$className}};

}