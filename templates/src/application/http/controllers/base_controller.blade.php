namespace {{$namespace}}\Application\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;
use {{$namespace}}\Application\Http\ApiResponse;
use {{$namespace}}\Application\Validation\Rules\NotDeleted;
use Lang;
use Illuminate\Http\Request;

abstract class Controller extends BaseController {

    /**
     * @param array $data
     * @param array $messages
     * @param bool $success
     * @return JsonResponse
     */
    protected function apiResponse(array $data, array $messages = [], $success = true) {

        $data['status'] = ($success) ? 'success' : 'error';

        return new JsonResponse(new ApiResponse($data, $messages), ($success) ? 200 : 400);
    }

    /**
     * @return JsonResponse
     */
    protected function unauthorized() {

        return new JsonResponse(new ApiResponse([], [['type' => 'error', 'text' => Lang::get('auth.unauthorized')]]), 401);
    }

    /**
     * @param array $data
     * @param Validator $validator
     * @return JsonResponse
     */
    protected function invalid(array $data, Validator $validator) {
        return $this->apiResponse(
            $data,
            ApiResponse::messages($validator),
            false
        );
    }

    /**
     * @param Exception $exception
     * @return JsonResponse
     */
    protected function serverError(Exception $exception) {

        $msg = env('APP_DEBUG') ? $exception->getMessage() : Lang::get('messages.site.server_error');
        return new JsonResponse(new ApiResponse([], [['type' => 'error', 'text' => $msg]]), 500);
    }

    /**
     * @param Request $request
     * @param $id
     * @param $tableName
     * @return array
     */
    protected function existsNotDeletedRule(Request $request, $id, $tableName): array {

        $rules = [
            'id' => [
                'required',
                "exists:$tableName,id",
                new NotDeleted($tableName)
            ]
        ];

        $input = $request->input();
        $input['id'] = $id;
        $request->replace($input);

        return $rules;
    }
}
