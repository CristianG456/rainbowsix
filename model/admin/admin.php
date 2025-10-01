<?php
require_once("../../database/db.php");
$db = new database;
$con = $db->conectar();
session_start();
?>

<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rainbow Six Siege</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet">
    <link rel="stylesheet" href="../../controller/css/style1index.css">
    <link rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  </head>
  <body>
    <header>
      <nav
        class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm custom-navbar">
        <div class="container">
          <a class="navbar-brand d-flex align-items-center" href="">
            <img src="../../controller/img/logo4.jpg" alt="Logo" class="logo-navbar">
            <span class="fw-bold">Rainbow Six Siege</span>
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav"
            aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
        </div>

         <div class="d-flex">
              <a href="iniciosesion.php"
                class="btn btn-danger px-4 fw-bold juega-btn"> Cerrar Sesión </a>
            </div>
      </nav>
    </header>