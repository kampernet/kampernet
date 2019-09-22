
		$validation = Validator::make(json_decode(json_encode($request), true), %class_name%Validation::$update);
		if ($validation->passes()) {
			$response->data['%snake_case_class_name%'] = $this->repository->save($%camel_case_class_name%);
			$response->messages['success.%snake_case_class_name%.update'] = ['message' => Lang::get('messages.%snake_case_class_name%.update.success')];
		} else {
			$response->data['%snake_case_class_name%'] = $%camel_case_class_name%;
			$errors = $validation->errors()->toArray();
			$response->messages['error.%snake_case_class_name%.update'] = $errors;
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