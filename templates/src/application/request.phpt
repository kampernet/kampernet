<?php
namespace %namespace%\Application;

class Request {

	/**
	 * @param $name
	 * @return null
	 */
	public function __get($name) {

		return (isset($this->$name)) ? $this->$name : null;
	}

	/**
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value) {

		$this->$name = $value;
	}
}
