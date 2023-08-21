<?php

class _2023_05_06_19_10_04_create_table_sessions
{
    public function up()
    {
        return "CREATE TABLE `sessions` (
            `id` varbinary(192) NOT NULL,
            `last_activity` int(10) NOT NULL DEFAULT '0',
            `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            PRIMARY KEY (`id`),
            KEY `last_activity` (`last_activity`)
          ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }

    public function down()
    {
        return "DROP TABLE `sessions`";
    }
}
