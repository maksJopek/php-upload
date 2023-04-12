<?php
session_start();

function random_str(
	int $length = 64,
	string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
): string
{
	if ( $length < 1 )
		throw new RangeException("Length must be a positive integer");

	$pieces = [];
	$max = mb_strlen($keyspace, '8bit') - 1;
	for ($i = 0; $i < $length; ++$i)
		$pieces[] = $keyspace[random_int(0, $max)];

	return implode('', $pieces);
}

if ( !$_SESSION["logged"] )
{
	header("Location: index.php");
	exit();
}

if ( isset($_FILES['files']['name'][0]) )
{
	if ( isset($_FILES['files']['name'][0]) && ($_FILES['files']['error'][0] == UPLOAD_ERR_OK) )
	{
		$uploaddir = '../private/upload/';
		$uploaddir = realpath(getcwd() . DIRECTORY_SEPARATOR . $uploaddir) . DIRECTORY_SEPARATOR;
		$success = [];
		$error = array();
		$i = 0;
		foreach ($_FILES['files']['error'] as $k => $v)
		{
			if ( $v == 0 )
			{
				$id = random_str();
				$new_name = $uploaddir . $id;
				var_dump($new_name);
				$temp_name = $_FILES['files']['tmp_name'][$k];
				$file_name = $_FILES['files']['name'][$k];

				if ( file_exists($new_name) ) {
					$_SESSION["communicates"]['fileError'] = "Plik z podaną nazwą $new_name już istnieje na serwerze";
				}
				else {
					if ( move_uploaded_file($temp_name, $new_name) ) {
						$success[] = $_FILES['files']['name'][$k];
						$fd = fopen($uploaddir . "../downloads.txt", 'a');
						$description = $_POST["text$i"];// != "" ? $_POST["text$i"] : "brak";
						fwrite($fd, "$id;$file_name;$uploaddir;$description\n");
						fclose($fd);
					} else {
						$error[] = $_FILES['files']['name'][$k];
					}
				}
			}
			$i++;
		}
		if ( count($error) )
			$_SESSION["communicates"]['uploadError'] = "Nie udało się załadować następujących plików: " . implode(", ", $error);
		else if ( count($success) )
			$_SESSION["communicates"]['uploadSuccess'] = "Udało się załadować następujące pliki: " . implode(", ", $success);
	} else
	{
		switch ($_FILES['fileName']['error'])
		{
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				$_SESSION["communicates"]["uploadFileError"] = "Przekroczony maksymalny rozmiar pliku!";
				break;
			case UPLOAD_ERR_PARTIAL:
				$_SESSION["communicates"]["uploadFileError"] = "Odebrano tylko część pliku!";
				break;
			case UPLOAD_ERR_NO_FILE:
				$_SESSION["communicates"]["uploadFileError"] = "Plik nie został pobrany!";
				break;
			case UPLOAD_ERR_NO_TMP_DIR:
				$_SESSION["communicates"]["uploadFileError"] = "Brak dostępu do katalogu tymczasowego!";
				break;
			case UPLOAD_ERR_CANT_WRITE:
				$_SESSION["communicates"]["uploadFileError"] = "Nie udało się zapisać pliku na dysku serwera!";
				break;
			case UPLOAD_ERR_EXTENSION:
				$_SESSION["communicates"]["uploadFileError"] = "Ładowanie pliku przerwane przez rozszerzenie PHP!";
				break;
			default:
				$_SESSION["communicates"]["uploadFileError"] = "Nieznany typ błędu!";
		}
	}
	header("Location: fileManager.php");
	exit();
}
?>
<!DOCTYPE html>
<html>

<head>
	<meta charset="UTF-8">
	<meta name="viewport"
		content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
		integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

	<style>
		body {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			background-color: black;
			color: white;
			overflow: hidden;
		}

		#form {
			display: grid;
			grid-template-rows: repeat(2, 1fr);
		}
		#form div {
			display: flex;
			flex-direction: column;
			justify-content: center;
			align-items: center;
		}

		input[type="file"] {
			margin-top: 2rem;
			background-color: rgba(0, 0, 0, 0);
			border: 3px solid white;
			border-radius: 1rem;
			padding: 1rem;
		}
		::file-selector-button {
			background-color: #0000;
			border: 3px solid #0056b3;
			color: #0056b3;
			font-size: 1rem;
			padding: .5rem;
			border-radius: .75rem;
			margin-right: 1.5rem;
		}
		label {
			margin-top: 0.75rem;
		}

		button {
			background-color: #0000;
			border: 3px solid #0056b3;
			color: #0056b3;
			font-size: 1.5rem;
			padding: .5rem;
			border-radius: .75rem;
		}

		.list-group {
			display: flex;
			flex-direction: column;
			gap: 1rem;
			font-size: 1.5rem;
			max-height: 90vh;
			overflow-y: scroll;
			margin-top: 2rem;
			text-align: center;
			align-items: center;
		}

		.list-group div {
			border: 3px solid white;
			border-radius: 2rem;
			padding: 1rem;
			width: 80%;
		}

		.list-group  span {
			font-size: 1.4rem;
		}

		#checkIfNotEmpty {
			position: absolute;
			font-size: 2rem;
			color: #E00;
			left: 10rem;
			bottom: 10rem;
		}
	</style>

	<title>fileManager</title>
</head>

<body>
	<form id="form" enctype="multipart/form-data" action="fileManager.php" method="POST">
	<div>
		<input type="file" name="files[]" id="file0" onchange="addInput(this)">
		<!--<input type="file" name="files[]" id="file1" onchange="addInput(this)">-->
		<!--<input type="file" name="files[]" id="file2" onchange="addInput(this)">-->
		<br><br>
		<button type="button" onclick="checkIfNotEmpty(event)" id="button">Send files</button>
	</div>
		<p id="checkIfNotEmpty">
			<?php
				foreach ($_SESSION["communicates"] as $key => $value)
					echo "$value";
			?>
		</p>
	</form>
	<div class="list-group">
		<h1>Uploaded files</h1>
		<?php
			//$path = "/var/www/private/fileManagerFiles/downloads.txt";
			$path = "../private/downloads.txt";
			$fd = fopen($path, 'r');
			if ( $fd )
			{
				while (!feof($fd))
				{
					$line = trim(fgets($fd));
					$arr = explode(";", $line);
					if ( count($arr) == 4 )
					{
						$where = "fileManagerFiles/download.php?fileid=";
						echo "<div><a href='$where{$arr[0]}'>{$arr[1]}</a>";
						if($arr[3] !== "")
							echo "<br><span>{$arr[3]}</span></div>";
						else
							echo "</div>";
					}
				}
				fclose($fd);
			} else
				echo '<a href="#">Something went wrong</a>';
		?>
	</div>
	<script>
		function checkIfNotEmpty(e) {
			e.preventDefault();
			let form = document.getElementById("form");

			for (let input of form.querySelectorAll('input[type="file"]')) {
				if (input.value) {
					form.submit();
					return
				}
			}

			document.getElementById("checkIfNotEmpty").innerHTML = "No files given!";
		}

		let number = 1;

		let div = document.getElementById("form").children[0];
		let br = document.getElementById("br");
		function addInput(self) {
			let newInput = document.createElement("input");
			//let txtInput = document.createElement("input");
			let label = document.createElement("label");

			newInput.type = "file";
			newInput.name = "files[]";
			newInput.id = "file" + number.toString();
			newInput.onchange = () => addInput(newInput);
			//txtInput.type = "text";
			//txtInput.name = "text" + self.id[4];
			//txtInput.id = txtInput.name;
			number++;
			label.innerHTML = `Opis: <input type="text" name="${"text" + self.id[4]}" id="${"text" + self.id[4]}">`;
			console.log(self.id, self.id[4])

			self.after(label, newInput);
			//let t = self.nextSibling.nextSibling;
			////form.insertBefore(txtInput, t.nodeName === "INPUT" ? t : newInput);
			//div.insertBefore(label, newInput);
		}
	</script>
</body>

</html>
<?php
session_unset();
$_SESSION["logged"] = true;
?>
