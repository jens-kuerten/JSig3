<?php


namespace Lib;
class Sde {
    public static function getMass($typeName) {
        $prefix = \Db\Sql::dbPrefix();
        
        $query = 'SELECT mass from '.$prefix.'invtypes WHERE typeName = :typeName';
        $row = \Db\Sql::queryRow($query,[':typeName'=>$typeName]);
        
        if (!empty($row)) {
            return (double)$row['mass'];
        }else {
            return 0;
        }
    }
}
