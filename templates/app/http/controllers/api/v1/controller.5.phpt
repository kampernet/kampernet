
		$this->update->execute($this->request, $this->response);

		return new JsonResponse($this->clean($this->response));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function destroy($id) {

		$this->request->id = $id;
		$this->delete->execute($this->request, $this->response);

		return new JsonResponse($this->clean($this->response));
	}
}
