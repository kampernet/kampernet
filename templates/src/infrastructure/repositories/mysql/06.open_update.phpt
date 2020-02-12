        }

        return $%camel_case_class_name%;
    }

    /**
     * @param %class_name% $%camel_case_class_name%
     * @return %class_name%
     */
    private function update(%class_name% $%camel_case_class_name%) {

        DB::table($this->tableName)->where('id', '=', $%camel_case_class_name%->id)->update([
