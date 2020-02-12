        ]);

        return $%camel_case_class_name%;
    }

    /**
     * @param %class_name% $%camel_case_class_name%
     * @return %class_name%
     */
    private function insert(%class_name% $%camel_case_class_name%) {

        $%camel_case_class_name%->id = DB::table($this->tableName)->insertGetId([
