namespace {{$namespace}}\Application\Http;

use Auth;
use Illuminate\Contracts\Validation\Validator;

class ApiResponse {

    /**
     * @var array
     */
    public $data;

    /**
     * @var array
     */
    public $messages;

    /**
     * @var \stdClass
     */
    public $user;

    /**
     * ApiResponse constructor.
     *
     * @param array $data
     * @param array $messages
     */
    public function __construct($data, $messages = []) {

        $this->data = $data;
        $this->messages = $messages;
        $this->user = new \stdClass();
        $this->user->id = Auth::user()->id;
        $this->user->name = Auth::user()->name;
    }

    /**
     * Takes a validator and returns the messages in the message bag as an array
     * the ApiMessageResponse class would accept for it's messages property
     *
     * @param Validator $validator
     * @return array
     */
    public static function messages(Validator $validator) {

        $validationErrors = [];
        $fields = $validator->getMessageBag()->toArray();
        foreach ($fields as $field => $messages) {
            foreach ($messages as $message) {
                $validationErrors [$field] = [
                    'type' => 'error',
                    'text' => $message];
            }
        }

        return $validationErrors;
    }
}
