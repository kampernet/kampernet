		$this->store->execute($this->request, $this->response);

		return new JsonResponse($this->clean($this->response));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function show($id) {

		$this->request->id = $id;
		$this->show->execute($this->request, $this->response);

		return new JsonResponse($this->clean($this->response));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function edit($id) {

		$this->request->id = $id;
		$this->show->execute($this->request, $this->response);

		return new JsonResponse($this->clean($this->response));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function update(Request $request, $id) {

		$this->request->id = $id;
