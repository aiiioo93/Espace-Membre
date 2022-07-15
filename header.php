<!doctype html>
<html lang="fr" class="h-100">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <title><?= $title ?? '';?></title>

    <!-- Bootstrap CSS -->
  	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css">

    <style>
    	#container{
    		padding-top: 75px;
    	}
    </style>
    
  </head>
  <body class="d-flex flex-column h-100">
    <header>
  <!-- Fixed navbar -->
  <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
    <a class="navbar-brand" href="#">Espace Membre</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarCollapse">
      <ul class="navbar-nav mr-auto">
        <?php if(empty($_SESSION['user'])):?>
        <li class="nav-item active">
          <a class="nav-link" href="index.php">Inscription</a>
        </li>
        <li class="nav-item active">
          <a class="nav-link" href="login.php">Connexion</a>
        </li>
        <?php else:?>
        
        <li class="nav-item active">
          <a class="nav-link" href="dashboard.php">Mon Compte</a>
        </li>

        <li class="nav-item active">
          <a class="nav-link" href="logout.php">Déconnexion</a>
        </li>

        <?php endif;?>
      </ul>
    </div>
  </nav>
</header>

<!-- Begin page content -->
<main role="main" class="flex-shrink-0">
  <div class="container" id="container">