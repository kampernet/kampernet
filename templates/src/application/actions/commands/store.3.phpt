
		$validation = Validator::make(json_decode(json_encode($request), true), %class_name%Validation::$store);

		if ($validation->passes()) {
			$response->data['%snake_case_class_name%'] = $this->repository->save($%camel_case_class_name%);
			$response->messages['success.%snake_case_class_name%.store'] = ['message' => Lang::get('messages.%snake_case_class_name%.store.success')];
		} else {
			$response->data['%snake_case_class_name%'] = $%camel_case_class_name%;
			$errors = $validation->errors()->toArray();
			$response->messages['error.%snake_case_class_name%.store'] = $errors;
		}

		return true;
	}

	/**
	 * The command undo method
	 */
	public function undo() {

		return $this->previous;
	}
}