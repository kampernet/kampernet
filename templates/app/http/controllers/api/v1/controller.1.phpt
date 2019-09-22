<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use %namespace%\Application\Actions\%class_name%\Create%class_name%;
use %namespace%\Application\Actions\%class_name%\Delete%class_name%;
use %namespace%\Application\Actions\%class_name%\Get%class_name%;
use %namespace%\Application\Actions\%class_name%\Get%class_name_plural%;
use %namespace%\Application\Actions\%class_name%\Insert%class_name%;
use %namespace%\Application\Actions\%class_name%\Update%class_name%;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use %namespace%\Application\Request as ApplicationRequest;
use %namespace%\Application\Response as ApplicationResponse;

/**
 * Class %class_name%Controller
 * @package App\Http\Controllers\Api\V1
 */
class %class_name%Controller extends Controller {

	/**
	 * @var ApplicationRequest
	 */
	private $request;

	/**
	 * @var ApplicationResponse
	 */
	private $response;

	/**
	 * @var Get%class_name_plural%
	 */
	private $index;

	/**
	 * @var Get%class_name%
	 */
	private $show;

	/**
	 * @var Create%class_name%
	 */
	private $create;

	/**
	 * @var Delete%class_name%
	 */
	private $delete;

	/**
	 * @var Insert%class_name%
	 */
	private $store;

	/**
	 * @var Update%class_name%
	 */
	private $update;

	/**
	 * %class_name%Controller constructor.
	 *
	 * @param ApplicationRequest $request
	 * @param ApplicationResponse $response
	 * @param Get%class_name_plural% $index
	 * @param Get%class_name% $show
	 * @param Create%class_name% $create
	 * @param Delete%class_name% $delete
	 * @param Insert%class_name% $store
	 * @param Update%class_name% $update
	 */
	public function __construct(ApplicationRequest $request, ApplicationResponse $response,
	                            Get%class_name_plural% $index, Get%class_name% $show, Create%class_name% $create,
	                            Delete%class_name% $delete, Insert%class_name% $store, Update%class_name% $update) {

		$this->request = $request;
		$this->response = $response;

		$this->index = $index;
		$this->show = $show;
		$this->create = $create;
		$this->delete = $delete;
		$this->store = $store;
		$this->update = $update;
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function index() {

		$this->index->execute($this->request, $this->response);

		return new JsonResponse($this->clean($this->response));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function create() {

		$this->create->execute($this->request, $this->response);

		return new JsonResponse($this->clean($this->response));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function store(Request $request) {

