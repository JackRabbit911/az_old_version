<?php

class _2023_05_06_19_09_49_create_table_migrations
{
    public function up()
    {
        return "CREATE TABLE `migrations` (
            `name` varchar(128) COLLATE latin1_bin NOT NULL,
            `path` varchar(64) COLLATE latin1_bin NOT NULL,
            PRIMARY KEY (`name`)
          ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin";
    }

    public function down()
    {
        return "DROP TABLE `migrations`";
    }
}
