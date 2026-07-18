<?php

use PGDatabase\Postgres;

if (!Postgres::isConnected()) {
    error_log("Error de conexión a la BD: " . Postgres::getError());
    die("Error de conexión a la base de datos.");
}
