<?php
require 'db.php';
if(!empty($_SESSION['user'])){
  header('Location: dashboard.php');
}

require 'vendor/autoload.php';

$title = 'Mot de passe oublié';

if(!empty($_POST))
{
    $post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    extract($post);

    $errors = [];


    if(empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
      array_push($errors, 'Cette email est invalide.');
    }
    else{
      $req = $db->prepare('SELECT * FROM users WHERE email=:email');
      $req->bindValue(':email', $email, PDO::PARAM_STR);
      $req->execute();

      if(!$req->rowCount()){
        array_push($errors, 'Cet email ne correspond à aucun membre du site.');
      }
      else{
        $user = $req->fetch();
      }

      if(empty($errors))
      {
        $token = uniqid();

        $req = $db->prepare('INSERT INTO password_resets (email, token, created_at) VALUES (:email, :token, NOW())');
        $req->bindValue(':email', $email, PDO::PARAM_STR);
        $req->bindValue(':token', $token, PDO::PARAM_STR);
        $req->execute();

        $link = 'Bonjour, veuillez cliquer sur <a href="https://membres.test/reset.php?token='.$token.'">ce lien</a> pour réinitialiser votre mote de passe.';

        // Create the Transport
        $transport = (new Swift_SmtpTransport('smtp.mailtrap.io', 465))
          ->setUsername('fae489573327ac')
          ->setPassword('eed3d4ab64b373')
        ;

        // Create the Mailer using your created Transport
        $mailer = new Swift_Mailer($transport);

        // Create a message
        $message = (new Swift_Message('Mot de passe oublié'))
          ->setFrom(['lcorrefabien@gmail.com' => 'John Doe'])
          ->setTo([$email => $user->name])
          ->addPart($link, 'text/html');
          ;

        // Send the message
        $result = $mailer->send($message);

        if($result){
          $success = 'Un email vous a été envoyé avec des instructions.';
          unset($email);
        }
      }
    }
}

?>

<?php include('header.php');?>

    <h2><?=$title;?></h2>

    <?php include('messages.php');?>

    <form action="forgot.php" method="post">
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" placeholder="Email" value="<?= $email ?? '';?>">
      </div>
      <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
    <br>

    <p><a href="login.php">Je m'en souviens en fait.</a></p>

<?php include('footer.php');?>