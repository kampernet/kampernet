namespace {{$namespace}}\Infrastructure\Repositories\MySql;

use DB;
use {{$namespace}}\Domain\Infrastructure\Repositories\{{$className}}RepositoryInterface;
use {{$namespace}}\Domain\Model\{{$className}};
@foreach($properties as $property)
    @if($property['isObject'])
        use {{$namespace}}\Domain\Infrastructure\Repositories\{{$property['propertyClassname']}}RepositoryInterface;
        use {{$namespace}}\Domain\Model\{{$property['propertyClassname']}};
    @endif
@endforeach

class {{$className}}Repository extends BaseRepository implements {{$className}}RepositoryInterface {

    /**
     * @var string
     */
    public $tableName = "{{$tableName}}";

    /**
     * @var string
     */
    public $className = {{$className}}::class;

    @foreach($properties as $property)
        @if($property['isObject'])
            /**
             * @var {{$property['propertyClassname']}}RepositoryInterface
             */
            private ${{$property['objectPropertyCamelcase']}}Repository;

        @endif
    @endforeach
    /**
     * {{$className}}Repository constructor
     *
    @php
    $injects = [];
    @endphp
    @foreach($properties as $property)
        @if($property['isObject'])
            @php
                $injects []= $property['propertyClassname'] . "RepositoryInterface $".$property['objectPropertyCamelcase']."Repository";
            @endphp
     * @param {{$property['propertyClassname']}}RepositoryInterface ${{$property['objectPropertyCamelcase']}}Repository
     @endif
     @endforeach
     */
    public function __construct({{implode(", ", $injects)}}) {
    @foreach($properties as $property)
        @if($property['isObject'])
        $this->{{$property['objectPropertyCamelcase']}}Repository = ${{$property['objectPropertyCamelcase']}}Repository;
        @endif
    @endforeach
    }

    /**
     * @param {{$className}} ${{$camelCaseClassName}}
     * @return {{$className}}
     */
    public function save({{$className}} ${{$camelCaseClassName}}): ?{{$className}} {

        if (${{$camelCaseClassName}}->id) {
            return $this->update(${{$camelCaseClassName}});
        } else {
            return $this->insert(${{$camelCaseClassName}});
        }
    }

    /**
     * @param $rec
     * @param bool $eager
     * @return mixed
     */
    protected function hydrate($rec, $eager = false) {

        ${{$camelCaseClassName}} = new {{$className}}();
        if ($rec) {
            ${{$camelCaseClassName}}->id = $rec->id;
            @foreach($properties as $property)
                @if($property['isObject'])
                    if ($eager) {
                        ${{$camelCaseClassName}}->{{$property['propertyName']}} = $this->{{$property['objectPropertyCamelcase']}}Repository->find($rec->{{$property['columnName']}});
                    } else {
                        ${{$camelCaseClassName}}->{{$property['propertyName']}} = new {{$property['propertyClassname']}}();
                        ${{$camelCaseClassName}}->{{$property['propertyName']}}->id = $rec->{{$property['columnName']}};
                    }
                @else
                    ${{$camelCaseClassName}}->{{$property['propertyName']}} = $rec->{{$property['columnName']}};
                @endif
            @endforeach
        }

        return ${{$camelCaseClassName}};
    }

    /**
     * @param {{$className}} ${{$camelCaseClassName}}
     * @return {{$className}}
     */
    private function update({{$className}} ${{$camelCaseClassName}}) {

        DB::table($this->tableName)->where('id', '=', ${{$camelCaseClassName}}->id)->update([
            @foreach($properties as $property)
                @if($property['isObject'])
                    '{{$property['columnName']}}' => ${{$camelCaseClassName}}->{{$property['propertyName']}}->id,
                @else
                    '{{$property['columnName']}}' => ${{$camelCaseClassName}}->{{$property['propertyName']}},
                @endif
            @endforeach
        ]);

        return ${{$camelCaseClassName}};
    }

    /**
     * @param {{$className}} ${{$camelCaseClassName}}
     * @return {{$className}}
     */
    private function insert({{$className}} ${{$camelCaseClassName}}) {

        ${{$camelCaseClassName}}->id = DB::table($this->tableName)->insertGetId([
            @foreach($properties as $property)
                @if($property['isObject'])
                    '{{$property['columnName']}}' => ${{$camelCaseClassName}}->{{$property['propertyName']}}->id,
                @else
                    '{{$property['columnName']}}' => ${{$camelCaseClassName}}->{{$property['propertyName']}},
                @endif
            @endforeach
        ]);

        return ${{$camelCaseClassName}};
    }

}
