<?php

require 'db.php';
if(!empty($_SESSION['user'])){
  header('Location: dashboard.php');
}

if(empty($_GET['token'])){
  header('Location: index.php');
}

$token = $_GET['token'];

$req = $db->prepare('SELECT * FROM password_resets WHERE token=:token');
$req->bindValue(':token', $token, PDO::PARAM_STR);
$req->execute();

if(!$req->rowCount()){
  header('Location: index.php');
}
else{
  $password_reset = $req->fetch();
}

if(!empty($_POST))
{
    $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

    extract($post);

    $errors = [];

    if($password_reset->email !== $email){
      array_push($errors, 'Cette adresse email est invalide.');
    }

    if(empty($password) || strlen($password) < 6){
      array_push($errors, 'Le mot de passe est requis et doit contenir au moins 6 caractères.');
    }

    if(!empty($password) && $password != $password_confirmation){
      array_push($errors, 'Les mots de passe ne correspondent pas.');
    }

    //optionnelement vérifier si l'email existe toujours dans la table  users

    if(empty($errors)){
      $req = $db->prepare('UPDATE users SET password=:password WHERE email=:email');
      $req->bindValue(':password', password_hash($password, PASSWORD_ARGON2ID), PDO::PARAM_STR);
      $req->bindValue(':email', $email, PDO::PARAM_STR);
      $req->execute();

      $success = 'Mot de passe mise à jour. <a href="login.php">Me connecter</a>';

      $req = $db->prepare('DELETE FROM password_resets WHERE email=:email');
      $req->bindValue(':email', $email, PDO::PARAM_STR);
      $req->execute();

      unset($email, $password);
    }
}

$title = 'Réinitialiser mon mot de passe';

?>

<?php include('header.php');?>

    <h2><?=$title;?></h2>

    <?php include('messages.php');?>

    <form action="reset.php?token=<?=$token?>" method="post">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" placeholder="Email" value="<?= $email ?? '';?>">
      </div>
      <div class="form-group">
        <label for="password">Nouveau mot de passe</label>
        <input type="password" name="password" class="form-control" placeholder="Mot de passe">
      </div>
      <div class="form-group">
        <label for="password_confirmation">Confirmez le mot de passe</label>
        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirmez le mot de passe">
      </div>
      <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>

<?php include('footer.php');?>