<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>XRemind - Auth</title>
  <?php
  if(session_status() !== PHP_SESSION_ACTIVE) session_start();
  require_once __DIR__ . '/../includes/helpers.php';
  $csrf = ensure_csrf();
  // detect whether a remember token cookie is present so forms can reflect the state
  $remember_present = !empty($_COOKIE['remember_token']);
  ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="/assets/css/style.css" rel="stylesheet">
  </head>
  <body>
  <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
      <a class="navbar-brand" href="/">XRemind</a>
    </div>
  </nav>
  <main class="container py-4">
