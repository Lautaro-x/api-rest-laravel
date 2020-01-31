<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SqlController extends Controller {

    private $defBDDname = 'api_rest_laravel';

    private function CrearBDDQuery($BDDname = NULL) {
        $BDDname = ($BDDname) ? $BDDname : $this->defBDDname;
        return 'CREATE DATABASE IF NOT EXISTS ' . $BDDname . ';';
    }

    private function CrearUsersTableQuery($BDDname = NULL) {
        $BDDname = ($BDDname) ? $BDDname : $this->defBDDname;
        return "
            CREATE TABLE IF NOT EXISTS `$BDDname`.`users` (
                `id`            int(255)        NOT NULL AUTO_INCREMENT,
                `name`          varchar(50)     NOT NULL,
                `surname`       varchar(100)    NOT NULL,
                `email`         varchar(255)    DEFAULT NULL,
                `password`      varchar(255)    NOT NULL,
                `role`          varchar(20)     DEFAULT NULL,
                `description`   text,
                `image`         varchar(255)    NOT NULL,
                `created_at`    datetime        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`    datetime        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `remember_token` varchar(255)   NOT NULL,
            PRIMARY KEY (`id`)
            )
            ENGINE=InnoDB;";
    }

    private function CrearCategoriesTableQuery($BDDname = NULL) {
        $BDDname = ($BDDname) ? $BDDname : $this->defBDDname;
        return "
            CREATE TABLE IF NOT EXISTS `$BDDname`.`categories` (
                `id`            int(255)        NOT NULL AUTO_INCREMENT,
                `name`          varchar(100)    DEFAULT NULL,
                `created_at`    datetime        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at`    datetime        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`)
            ) 
            ENGINE=InnoDB;";
    }

    private function CrearPostsTableQuery($BDDname = NULL) {

        $BDDname = ($BDDname) ? $BDDname : $this->defBDDname;
        return "        
            CREATE TABLE IF NOT EXISTS `$BDDname`.`posts` ( 
                `id`            INT(255)        NOT NULL AUTO_INCREMENT , 
                `user_id`       INT(255)        NOT NULL , 
                `category_id`   INT(255)        NOT NULL , 
                `title`         VARCHAR(255)    DEFAULT NULL , 
                `content`       TEXT            DEFAULT NULL , 
                `image`         VARCHAR(255)    NOT NULL , 
                `created_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP , 
                `updated_at`    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP , 
                PRIMARY KEY (`id`),
                CONSTRAINT `fk_posts_users` FOREIGN KEY(`user_id`) REFERENCES `users`(`id`),
                CONSTRAINT `fk_posts_categories` FOREIGN KEY(`category_id`) REFERENCES `categories`(`id`)
            ) ENGINE = InnoDB;";
    }

}
