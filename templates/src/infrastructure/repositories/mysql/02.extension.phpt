<?php
namespace %namespace%\Infrastructure\Repositories\MySql;

use DB;
use %namespace%\Domain\Infrastructure\Repositories\%class_name%RepositoryInterface;
use %namespace%\Domain\Model\%class_name%;

class %class_name%Repository extends BaseRepository implements %class_name%RepositoryInterface {

    /**
     * @var string
     */
    public $tableName = "%table_name%";

    /**
     * @var string
     */
    public $className = %class_name%::class;

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
            $%camel_case_class_name%->log = new DailyActivityLog();
            $%camel_case_class_name%->log->id = $rec->daily_activity_log_id;
            $%camel_case_class_name%->bikeNumber = $rec->bike_number;
            $%camel_case_class_name%->paymentMethod = $rec->payment_method;
            $%camel_case_class_name%->amount = $rec->amount;
        }

        return $%camel_case_class_name%;
    }

    /**
     * @param %class_name% $%camel_case_class_name%
     * @return %class_name%
     */
    private function update(%class_name% $%camel_case_class_name%) {

        DB::table($this->tableName)->where('id', '=', $%camel_case_class_name%->id)->update([
            'daily_activity_log_id' => $%camel_case_class_name%->log->id,
            'amount' => $%camel_case_class_name%->amount,
            'payment_method' => $%camel_case_class_name%->paymentMethod,
            'bike_number' => $%camel_case_class_name%->bikeNumber
        ]);

        return $%camel_case_class_name%;
    }

    /**
     * @param %class_name% $%camel_case_class_name%
     * @return %class_name%
     */
    private function insert(%class_name% $%camel_case_class_name%) {

        $%camel_case_class_name%->id = DB::table($this->tableName)->insertGetId([
            'daily_activity_log_id' => $%camel_case_class_name%->log->id,
            'amount' => $%camel_case_class_name%->amount,
            'payment_method' => $%camel_case_class_name%->paymentMethod,
            'bike_number' => $%camel_case_class_name%->bikeNumber
        ]);

        return $%camel_case_class_name%;
    }

}
