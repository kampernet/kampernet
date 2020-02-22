namespace {{$namespace}}\Domain\Model;

/**
* Class {{$className}}
*
* @package {{$namespace}}\Domain\Model
*/
class {{$className}} {

    /**
    * @var int
    */
    public $id;

    @foreach($properties as $property)
        @if($property['isObject'])
            @if($property['isCollection'])
/**
    * @var \{{$namespace}}\Domain\Model\{{$property['propertyClassname']}}[]
    */
    public ${{$property['pluralPropertyName']}};

            @else
/**
    * @var \{{$namespace}}\Domain\Model\{{$property['propertyClassname']}}
    */
    public ${{$property['objectPropertyCamelcase']}};

            @endif
        @else
/**
    * @var {{$property['phpType']}}
    */
    public ${{$property['propertyName']}};

        @endif
    @endforeach

}