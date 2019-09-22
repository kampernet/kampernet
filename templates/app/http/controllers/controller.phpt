<?php

namespace App\Http\Controllers;

use %namespace%\Application\Response as ApplicationResponse;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesResources;

class Controller extends BaseController {

	use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

	private $blacklist = [
		'__initializer__',
		'__cloner__',
		'__isInitialized__',
		'password',
	];

	/**
	 * @param ApplicationResponse $response
	 * @return ApplicationResponse
	 */
	public function clean(ApplicationResponse $response) {

		/*
		 * convert the data to an array, if there's objects in it.
		 */
		$response->data = json_decode(json_encode($response->data), true);

		foreach($this->blacklist as $key) {
			$this->cleanRecursively($response->data, $key);
		}

		return $response;
	}

	private function cleanRecursively(&$data, $key) {

		unset($data[$key]);
		foreach ($data as &$value) {
			if (is_array($value)) {
				$this->cleanRecursively($value, $key);
			}
		}
	}
}
