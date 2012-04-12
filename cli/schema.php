<?php
class_exists('UFile');
DBSQLite::$DEFAULT_FLAGS=SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE;
//include CORE.'db/DBSchema.php';
//DBSchema::processAll(new Folder(APP.DS.'models'),true);
$schemaProcessing=new DBSchemaProcessing(new Folder(APP.'models'),new Folder(APP.'triggers'),true);
echo "Schema processed";
