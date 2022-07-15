<?php

require('db.php');

if(empty($_SESSION['user'])){
  header('Location: login.php');
}

$user = $_SESSION['user'];

$title = 'Changer mon mot de passe';

if(!empty($_POST))
{
	$post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

  	extract($post);

  	$errors = [];

  	if(!password_verify($actual, $user->password)){
  		array_push($errors, 'Le mot de passe actuel n\'est pas le bon.');
  	}

  	if(empty($password) || strlen($password) < 6){
	    array_push($errors, 'Le mot de passe est requis et doit contenir au moins 6 caractères.');
	  }

	if($password !== $password_confirmation){
		array_push($errors, 'Les mots de passe ne correspondent pas.');
	}

	if(empty($errors))
	{
		$req = $db->prepare('UPDATE users SET password=:password WHERE id=:id');
		$req->bindValue(':password', password_hash($password, PASSWORD_ARGON2ID), PDO::PARAM_STR);
		$req->bindValue(':id', $user->id, PDO::PARAM_INT);
		$req->execute();

		$success = 'Mot de passe mis à jour.';
	}
}

?>

<?php include('header.php');?>

    <h2><?=$title;?></h2>

    <?php include('messages.php');?>

    <form action="password.php" method="post">
      <div class="form-group">
        <label for="actual">Mot de passe actuel</label>
        <input type="password" name="actual" class="form-control" placeholder="Mot de passe actuel">
      </div>
      <div class="form-group">
        <label for="password">Nouveau mot de passe</label>
        <input type="password" name="password" class="form-control" placeholder="Nouveau mot de passe">
      </div>
      <div class="form-group">
        <label for="password_confirmation">Confirmez le mot de passe</label>
        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirmez le mot de passe">
      </div>
      <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
    <br>

    <p><a href="dashboard.php">Revenir à mon compte</a></p>

<?php include('footer.php');?>