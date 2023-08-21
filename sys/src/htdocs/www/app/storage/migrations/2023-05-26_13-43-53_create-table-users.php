<?php

use App\Model\ModelTables;

class _2023_05_26_13_43_53_create_table_users
{
    public function up()
    {
        return ModelTables::USERS;
    }

    public function down()
    {
        return "DROP TABLE `users`";
    }
}
