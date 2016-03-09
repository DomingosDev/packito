<?php

	class UUIDWriterMySQL extends RedBeanPHP\QueryWriter\MySQL{
		protected $defaultValue = '@uuid';
        const C_DATATYPE_SPECIAL_UUID  = 97;

        public function __construct( RedBeanPHP\Adapter $adapter ) {
            parent::__construct( $adapter );
            $this->addDataType(
                self::C_DATATYPE_SPECIAL_UUID, 'char(32)'  );
        }

        public function createTable( $table ) {
            $table = $this->esc( $table );
            $sql   = "
            CREATE TABLE {$table} (
            id char(32) NOT NULL)
            ENGINE = InnoDB DEFAULT
            CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci ";
            $this->adapter->exec( $sql );
        }

        public function updateRecord( $table, $updateValues, $id = NULL ) {
            $uid = UUID::create();

            $flagNeedsReturnID = (!$id);
            if ($flagNeedsReturnID) R::exec("SET @uuid = '$uid'");
            $id = parent::updateRecord( $table, $updateValues, $id );
            if($flagNeedsReturnID) $id = $uid;
            return $id;
        }

        public function getTypeForID(){
            return self::C_DATATYPE_SPECIAL_UUID;
        }
    }
?>