<?php
	require_once "getdb.php";
	$button = 'Добавить';
	if ( isset($_SESSION['user'])) {
		$user_id = $_SESSION['user']['user_id'];
		if (!empty($_POST['sort_by'])) {
			$sort = $_POST['sort_by'];
			$sql = "SELECT task.id, task.description, task.date_added, task.is_done, task.assigned_user_id, user.login FROM task INNER JOIN user ON  user.id=task.user_id WHERE user_id=$user_id ORDER BY $sort ASC";
			$sql_two = "SELECT task.id, task.description, task.date_added, task.is_done, task.assigned_user_id, user.login FROM task INNER JOIN user ON  user.id=task.user_id WHERE user_id=$user_id ORDER BY $sort ASC";
		} else {
			$sql = "SELECT task.id, task.description, task.date_added, task.is_done, task.user_id, task.assigned_user_id, user.login FROM task INNER JOIN user ON  user.id=task.user_id WHERE user_id=$user_id";
			$sql_two = "SELECT task.id, task.description, task.date_added, task.is_done, task.user_id, task.assigned_user_id, user.login FROM task INNER JOIN user ON  user.id=task.assigned_user_id WHERE (assigned_user_id=$user_id) AND (user_id!=$user_id)";
			// foreach ($dbh->query($sql_two) as $row) {
			// 	echo "<pre>";print_r($row); echo "</pre>";
			// }
		}
	}

	if (!empty($_GET['id']) && !empty($_GET['action'])) {
		$id= strip_tags($_GET['id']);
		if ($_GET['action'] == "delete") {
			$query = "DELETE FROM task WHERE id = ?";
			$del = $dbh->prepare($query);
			$del->bindValue(1, $id, PDO::PARAM_INT);
			$del->execute();
			header("Location: index.php");
		}

		if ($_GET['action'] == "done") {
			$query = "UPDATE task SET is_done = 1 WHERE id = ?";
			$done = $dbh->prepare($query);
			$done->bindValue(1, $id, PDO::PARAM_INT);
			$done->execute();
			header("Location: index.php");
		}

		if ($_GET['action'] == "edit") {
			$button = 'Обновить';
			$select = "SELECT description FROM task WHERE id = ?";
			$edit = $dbh->prepare($select);
			$edit->bindValue(1, $id, PDO::PARAM_INT);
			$edit->execute();
			foreach ($edit->FetchAll(PDO::FETCH_ASSOC) as $rows) {$str = $rows['description'];}
		}
	}

	if (!empty($_POST['save']) and $_POST['save'] == "Добавить") {
		$description = strip_tags($_POST['description']);
		$insert = "INSERT INTO task (description, date_added, user_id, assigned_user_id) VALUES (?, now(), ?, ?)";
		$add = $dbh->prepare($insert);
		$add->bindValue(1, $description, PDO::PARAM_STR);
		$add->bindValue(2, $_SESSION['user']['user_id'], PDO::PARAM_INT);
		$add->bindValue(3, $_SESSION['user']['user_id'], PDO::PARAM_INT);
		$add->execute();
	} 
	elseif (!empty($_POST['id']) and !empty($_POST['action']) && $_POST['action'] == 'edit') {
		$button = 'Обновить';
		$id = strip_tags($_POST['id']);
		$description = strip_tags($_POST['description']);
		$insert = "UPDATE task SET description = ? WHERE id = ?";
		$update = $dbh->prepare($insert);
		$update->bindValue(1, $description, PDO::PARAM_STR);
		$update->bindValue(2, $id, PDO::PARAM_INT);
		$update->execute();
		header("Location: index.php");
	}

	if(!empty($_POST['assigned_user_id'])) {
		$assign_id = strip_tags($_POST['assigned_user_id']);
		$row_id = strip_tags($_POST['row_id']);
		$query="UPDATE task SET assigned_user_id = ? WHERE id = ?";
		$setid = $dbh->prepare($query);
		$setid->bindValue(1, $assign_id, PDO::PARAM_INT);
		$setid->bindValue(2, $row_id, PDO::PARAM_INT);
		$setid->execute();
	}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css" integrity="sha384-Zug+QiDoJOrZ5t4lssLdxGhVrurbmBWopoEl+M6BdEfwnCJZtKxi1KgxUyJq13dy" crossorigin="anonymous">
</head>
<body style="background-color: #9ECCB3">
	<div class="container" style="margin-top: 10vh;">
		<div class="row">
			<?php if ( isset($_SESSION['user'])): ?>
			<div class="col">
				<form action="index.php" method="POST">
					<div class="input-group">
						<input type="hidden" name="id" value="<?= strip_tags($_GET['id']); ?>">
						<input type="hidden" name="action" value="<?= strip_tags($_GET['action']); ?>">
						<input type="text" name="description" placeholder="описание задачи" value="<?= $_GET ? $str : " " ?>" class="form-control">
						<div class="input-group-append">
							<input type="submit" name="save" value="<?= $button; ?>" class="btn btn-info">
						</div>
					</div>
				</form>				
			</div>
			<div class="col">
				<form action="index.php" method="POST">
					<div class="input-group">
						<select name="sort_by" class="custom-select">
							<option>Сортировать по:</option>
							<option value="date_added">Дате добавления</option>
							<option value="is_done">Статусу</option>
							<option value="description">Описанию</option>
						</select>
						<div class="input-group-append">
							<input type="submit" name="sort" value="Отсортировать" class="btn btn-info">
						</div>
					</div>
				</form>	
			</div>
		</div>
		<div class="row">
			<h3 style="color: #fffff1; margin: 30px 0 20px 0;">Здравствуйте, <?= $_SESSION['user']['user_name'] ?>! Вот ваш список дел:</h3>
			<table class="table table-bordered table-sm" style="margin-top: 10px; background-color: #fff;">
				<thead class="thead-dark">
					<tr>
						<th scope="col">Описание задачи</th>
						<th scope="col">Дата добавления</th>
						<th scope="col">Статус</th>
						<th scope="col">Редактирование</th>
						<th scope="col">Исполнитель</th>
            			<th scope="col">Автор</th>
           				<th scope="col">Передать задачу другому</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($dbh->query($sql) as $row): ?>
					<tr>
						<td><?= $row['description'] ?></td>
						<td><?= $row['date_added'] ?></td>
						<td class="font-weight-bold"><?php echo $result = $row['is_done'] == 0 ? "В процессе" : "Выполнено"; ?></td>
						<td>
							<a href="?id=<?= $row['id'] ?>&action=edit" class="badge badge-primary">Изменить</a>
							<?php if ($row['assigned_user_id'] == $row['user_id']) : ?>
							<a href="?id=<?= $row['id'] ?>&action=done" class="badge badge-success">Выполнить</a>
							<?php endif; ?>
							<a href="?id=<?= $row['id'] ?>&action=delete" class="badge badge-danger">Удалить</a>
						</td>
						<td>
							<?php 
								foreach ($dbh->query('SELECT * FROM user') as $user) {
									if (($user['id'] == $row['assigned_user_id']) && ($row['assigned_user_id'] != $user_id)) {
										echo $user['login'];
									} 
									} 
								if ($row['assigned_user_id'] == $user_id) {
									echo 'Вы';
								}
							?>
						</td>
						<td><?= $row['login'] ?></td>
						<td>
							<form method="POST">
								<div class="input-group">
									<input type="hidden" name="row_id" value="<?= $row['id'] ?>">
									<select name='assigned_user_id' class="custom-select custom-select-sm">
										<?php foreach ($dbh->query('SELECT * FROM user') as $user) {echo '<option value="' . $user['id'] . '">' . $user['login'] . '</option>';} ?>
									</select>
									<div class="input-group-append ">
										<input type='submit' name='assign' class="btn btn-info btn-sm" value='Сменить исполнителя' />
									</div>
								</div>
							</form>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<h3 style="color: #fffff1; margin: 30px 0 20px 0;">Также, посмотрите, что от Вас требуют другие люди:</h3>
			<table class="table table-bordered table-sm" style="margin: 10px 0 40px 0; background-color: #fff;">
        		<thead class="thead-dark">
        			<tr>
	            		<th scope="col">Описание задачи</th>
	            		<th scope="col">Дата добавления</th>
	            		<th scope="col">Статус</th>
			            <th scope="col">Редактирование</th>
			            <th scope="col">Исполнитель</th>
			            <th scope="col">Автор</th>
		            </tr>
		        </thead>
				<tbody>
					<?php foreach ($dbh->query($sql_two) as $row): ?>
					<tr>
						<td><?= $row['description'] ?></td>
						<td><?= $row['date_added'] ?></td>
						<td class="font-weight-bold"><?php echo $result = $row['is_done'] == 0 ? "В процессе" : "Выполнено"; ?></td>
						<td>
							<a href="?id=<?= $row['id'] ?>&action=edit" class="badge badge-primary">Изменить</a>
							<a href="?id=<?= $row['id'] ?>&action=done" class="badge badge-success">Выполнить</a>
							<a href="?id=<?= $row['id'] ?>&action=delete" class="badge badge-danger">Удалить</a>
						</td>
						<td><?= $row['login'] ?></td>
						<td>
							<?php 
								foreach ($dbh->query('SELECT * FROM user') as $user) {
									if (($user['id'] == $row['user_id'])) {
										echo $user['login'];
									}
								} 
							?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<a href="logout.php" class="btn btn-secondary btn-lg">выйти</a>
		<?php else: ?>
			<h1 class="display-1" style="margin: 20px auto;">WELCOME TO TODO</h1>
			<a href="register.php" class="btn btn-primary btn-lg" style="margin: 20px auto;">Войти или зарегистрирвоаться</a>
		</div>
	</div>
</body>
</html>
<?php endif; ?>