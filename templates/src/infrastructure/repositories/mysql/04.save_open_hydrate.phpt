
    /**
     * @param %class_name% $%camel_case_class_name%
     * @return %class_name%
     */
    public function save(%class_name% $%camel_case_class_name%): ?%class_name% {

        if ($%camel_case_class_name%->id) {
            return $this->update($%camel_case_class_name%);
        } else {
            return $this->insert($%camel_case_class_name%);
        }
    }

    /**
     * @param $rec
     * @param bool $eager
     * @return mixed
     */
    protected function hydrate($rec, $eager = false) {

        $%camel_case_class_name% = new %class_name%();
        if ($rec) {
            $%camel_case_class_name%->id = $rec->id;
