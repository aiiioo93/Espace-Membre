<?php

require('db.php');

if(empty($_SESSION['user'])){
  header('Location: login.php');
}

require 'vendor/autoload.php';

use Intervention\Image\ImageManagerStatic as Image;

Image::configure(array('driver' => 'imagick'));

$user = $_SESSION['user'];

$title = 'Bonjour '.$user->name;

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

	if(empty($errors))
	{
		$req = $db->prepare('SELECT * FROM users WHERE name=:name AND id != :id');
		$req->bindValue(':name', $name, PDO::PARAM_STR);
		$req->bindValue(':id', $user->id, PDO::PARAM_INT);
		$req->execute();

		if($req->rowCount() > 0){
			array_push($errors, 'Un autre utilisateur a déjà ce nom.');
		}

		$req = $db->prepare('SELECT * FROM users WHERE email=:email AND id != :id');
		$req->bindValue(':email', $email, PDO::PARAM_STR);
		$req->bindValue(':id', $user->id, PDO::PARAM_INT);
		$req->execute();

		if($req->rowCount() > 0){
			array_push($errors, 'Un autre utilisateur a déjà cet email.');
		}

		if(!empty($_FILES['photo']['name']))
		{
			$photo = $_FILES['photo'];

			$filePath = 'photos/'.$user->id;
			$thumbPath = $filePath.'/thumbnail';

			@mkdir($filePath, 0777, true);

			@mkdir($filePath.'/thumbnail', 0777, true);

			$allowedExt = ['jpeg', 'jpg', 'png'];

			$ext = strtolower(pathinfo($photo['name'], PATHINFO_EXTENSION));

			if(!in_array($ext, $allowedExt)){
				array_push($errors, 'Le fichier n\'est pas autorisé.');
			}
			else{
				$infos = getimagesize($photo['tmp_name']);

				$width = $infos[0];

				$height = $infos[1];

				if($width < 200 || $height < 200){
					array_push($errors, 'L\'image doit faire au moins 200px de large et 200px de hauteur.');
				}
				else{
					$filename = uniqid($user->id, true).'.'.$ext;
					move_uploaded_file($photo['tmp_name'], $filePath.'/'.$filename);

					$thumbFilePath = $filePath.'/thumbnail/'.$filename;

					Image::make($filePath.'/'.$filename)->fit(200)->save($thumbFilePath);
				}
			}
		}

		if(empty($errors))
		{
			$req = $db->prepare('SELECT * FROM users WHERE id=:id');
			$req->bindValue(':id', $user->id, PDO::PARAM_INT);
			$req->execute();

			$user = $req->fetch();

			if($user->photo){
				$oldFilePath = $filePath.'/'.$user->photo;
				$oldThumbFilePath = $thumbPath.'/'.$user->photo;
			}

			$req = $db->prepare('UPDATE users SET name=:name, email=:email, photo=:photo WHERE id=:id');
			$req->bindValue(':name', $name, PDO::PARAM_STR);
			$req->bindValue(':email', $email, PDO::PARAM_STR);
			$req->bindValue(':photo', $filename ?? $user->photo, PDO::PARAM_STR);
			$req->bindValue(':id', $user->id, PDO::PARAM_INT);
			$req->execute();

			$req = $db->prepare('SELECT * FROM users WHERE id=:id');
			$req->bindValue(':id', $user->id, PDO::PARAM_INT);
			$req->execute();

			$user = $req->fetch();

			unset($_SESSION['user']);
			$_SESSION['user'] = $user;

			if(!empty($oldFilePath) && !empty($filename)){
				@unlink($oldFilePath);
				@unlink($oldThumbFilePath);
			}

			$success = 'Informations mises à jour.';
		}
	}
}

?>


<?php include('header.php');?>

    <h2><?=$title;?></h2>

    <?php include('messages.php');?>

    <form action="dashboard.php" method="post" enctype="multipart/form-data">
      <div class="form-group">
        <label for="name">Nom d'utilisateur</label>
        <input type="text" name="name" class="form-control" placeholder="Nom d'utilisateur" value="<?= $name ?? $user->name;?>">
      </div>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" name="email" class="form-control" placeholder="Email" value="<?= $email ?? $user->email;?>">
      </div>
      <div class="form-group">
        <label for="photo">Photo au format jpeg, jpg ou png d'au moins 200x200px</label>
        <input type="file" name="photo" class="form-control">
      </div>
      <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>

    <br>

    <a style="float: right;" onclick="return confirm('Confirmez la suppresion de votre comtpe ?');" href="delete.php" class="btn btn-danger delete">Supprimer mon compte</a>

    <p><a href="password.php">Modifier mon mot de passe.</a></p>


	<?php if(!empty($user->photo)):?>
		<a href="photos/<?=$user->id.'/'.$user->photo;?>">
			<img src="photos/<?=$user->id.'/'.$user->photo;?>" alt="" width="200" height="200">
		</a>
	<?php endif;?>


<?php include('footer.php');?>