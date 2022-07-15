<?php 
require('db.php');

if(!empty($_SESSION['user'])){
  header('Location: dashboard.php');
}

$title = 'Inscription';

if(!empty($_POST))
{
  $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

  extract($post);

  $errors = [];

  if(empty($name) || strlen($name) < 3){
    array_push($errors, 'Le nom est require et doit contenir au moins 3 caractères.');
  }

  if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
    array_push($errors, 'L\'email n\'est pas une adresse email valide.');
  }

  if(empty($password) || strlen($password) < 6){
    array_push($errors, 'Le mot de passe est requis et doit contenir au moins 6 caractères.');
  }

  if(empty($errors))
  {
    $req = $db->prepare('SELECT * FROM users WHERE name = :name');
    $req->bindValue(':name', $name, PDO::PARAM_STR);
    $req->execute();

    if($req->rowCount() > 0){
      array_push($errors, 'Un utilisateur est déjà enregistré avec ce nom.');
    }

    $req = $db->prepare('SELECT * FROM users WHERE email = :email');
    $req->bindValue(':email', $email, PDO::PARAM_STR);
    $req->execute();

    if($req->rowCount() > 0){
      array_push($errors, 'Un utilisateur est déjà enregistré avec cet email.');
    }

    if(empty($errors))
    {
      $req = $db->prepare('INSERT INTO users (name, email, password, create_at) VALUES (:name, :email, :password, NOW()) ');
      $req->bindValue(':name', $name, PDO::PARAM_STR);
      $req->bindValue(':email', $email, PDO::PARAM_STR);
      $req->bindValue(':password', password_hash($password, PASSWORD_ARGON2ID), PDO::PARAM_STR);
      $req->execute();

      unset($name, $email, $password);
      $success = 'Votre inscription est terminée, vous pouvez <a href="login.php">vous connecter</a>.';
    }

  }

}

?>


<?php include('header.php');?>

    <h2><?=$title;?></h2>

    <?php include('messages.php');?>

    <form action="index.php" method="post">
      <div class="form-group">
        <label for="name">Nom d'utilisateur</label>
        <input type="text" name="name" class="form-control" placeholder="Nom d'utilisateur" value="<?= $name ?? '';?>">
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" placeholder="Email" value="<?= $email ?? '';?>">
      </div>
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input type="password" name="password" class="form-control" placeholder="Mot de passe">
      </div>
      <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>

<?php include('footer.php');?>