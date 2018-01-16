<?php
require_once "getdb.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$errors = array();
	if (isset($_POST['register'])) {
		if (!empty($_POST['login']) && !empty($_POST['password'])) {
			$login = strip_tags(trim($_POST['login']));
			$password = md5(strip_tags(trim($_POST['password'])));
			$data = $dbh->query('SELECT login FROM user');
			foreach ($data as $logins) {
				$login_name = $logins['login'];
			}
			if ($login_name == $login) {
				$errors[] = 'Такой пользователь уже существует в базе данных.';
			}
			else {
			$query = "INSERT INTO user (login, password) VALUES (?, ?)";
			$reg = $dbh->prepare($query);
			$reg->bindValue(1, $login, PDO::PARAM_STR);
			$reg->bindValue(2, $password, PDO::PARAM_STR);
			$reg->execute();
			$errors[] = 'Теперь вы можете войти под своим логином и паролем';
			}
		}
		else {
		$errors[] = 'Ошибка регистрации. Введите все необхдоимые данные.';
		}
	}

	if (isset($_POST['sign_in'])) {
		if (!empty($_POST['login']) && !empty($_POST['password'])) {
			$login = strip_tags(trim($_POST['login']));
			$password = md5(strip_tags(trim($_POST['password'])));
			$data = $dbh->query('SELECT * FROM user');
			foreach ($data as $logins) {
				$login_name = $logins['login'];
				$login_pass = $logins['password'];
				$login_id = $logins['id'];
			
			if (($login_name == $login) && ($login_pass == $password)) {
				$_SESSION['user'] = array('user_name'=>$login_name, 'user_id'=>$login_id);
				header("location: index.php");
			}
			else {
				$errors[] = 'Такой пользователь не существует, либо неверный пароль.';
			}}
		}
		else {
		$errors[] = 'Ошибка входа. Введите все необхдоимые данные.';
		}
	}
}
else {
	$errors[] = 'Введите данные для регистрации или войдите, если уже регистрировались:';
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
</head>
<body style="background-color: #9ECCB3">
	<div class="container" style="margin-top: 10vh;">
		<h6 class="alert alert-info" style="max-width: 450px; margin: 0 auto;"><?php echo array_shift($errors); ?></h6>
		<form method="POST" style="max-width: 400px; margin: 20px auto;">
			<div class="form-group">
		    	<input class="form-control" type="text" name="login" placeholder="Логин" />
		    </div>
		    <div class="form-group">
		    	<input class="form-control" type="password" name="password" placeholder="Пароль" />
		    </div>

		    <input type="submit" class="btn btn-primary" name="sign_in" value="Вход" />
		    <input type="submit" class="btn btn-primary" name="register" value="Регистрация" />
		</form>
</div>
</body>
</html>