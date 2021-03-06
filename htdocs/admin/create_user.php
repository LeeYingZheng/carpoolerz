<?php
	$dbconn = pg_connect("host=localhost port=5432 dbname=carpoolerz user=postgres password=postgres")
	or die('Could not connect: ' . pg_last_error());
	
	// Define variables and initialize with empty values
	$username = $name = $password = $licensenum = $is_admin = "";
	$username_err= $name_err = $password_err= $licensenum_err = $is_admin_err = "";
	
	// Processing form data when form is submitted
	if($_SERVER["REQUEST_METHOD"] == "POST"){
		// Validate username
		$input_username = trim($_POST["username"]);
		$username_check = "SELECT * FROM systemuser WHERE username = '$input_username';";
		$result = pg_query($dbconn,$username_check);
		if (!$result) {
			echo pg_last_error($dbconn);
			exit;
		}
		if (pg_num_rows($result) != 0) {
			$username_err = "That username is taken";
		}else{
			$username = $input_username;
		}
		
		// Validate full name
		$input_name = trim($_POST["name"]);
		if(empty($input_name)){
			$name_err = "Please enter a name.";
			} elseif(!filter_var(trim($_POST["name"]), FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Z'-.\s ]+$/")))){
			$name_err = 'Please enter a valid name.';
			} else{
			$name = $input_name;
		}
		
		// Validate password
		$input_pw = trim($_POST["password"]);
		if(empty($input_pw)){
			$password_err = 'Please enter a password.';     
			} else{
			$password = $input_pw;
		}
		
		// Validate is_admin
		$input_is_admin = trim($_POST["is_admin"]);
		if(empty($input_is_admin)){
			$is_admin_err = "Please specify admin priviliges (Y/N)";     
			} elseif($input_is_admin !== "Y" AND $input_is_admin !== "N" AND $input_is_admin !== "t" AND $input_is_admin !== "f" AND $input_is_admin !== "TRUE" AND $input_is_admin !== "FALSE"){
			$is_admin_err = 'Please enter Y or N';
			} else{
			if($input_is_admin === "Y" OR $input_is_admin === "t" OR $input_is_admin === "TRUE"){
				$is_admin = "TRUE";
				}else if($input_is_admin === "N" OR $input_is_admin === "f" OR $input_is_admin === "FALSE"){
				$is_admin = "FALSE";
			}
		}
		
		// Check input errors before inserting in database
		if(empty($username_err) && empty($name_err) && empty($password_err) && empty($is_admin_err)){
			$input_ln = trim($_POST["licensenum"]);
			if(empty($input_ln)){
				$sql = "INSERT into systemuser VALUES('$username', '$name', '$password', DEFAULT, '$is_admin')";
			} else{
				//store the input into the page variable, so that it can be shown again on success page
				$licensenum=$input_ln;
				$sql = "INSERT into systemuser VALUES('$username', '$name', '$password', '$licensenum', '$is_admin')";
			}
			$result = pg_query($dbconn, $sql);
			
			if(!$result){
				echo pg_last_error($dbconn);
				} else {
				echo "<h3 class='text-center'>User Created successfully</h3>"."<br>";
				echo "<h4 class='text-center'>Redirecting you back to View Users page</h4>";
				header("refresh:3;url=admin-users.php");
			} 
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Create User</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
		<style type="text/css">
			.wrapper{
            width: 500px;
            margin: 0 auto;
			}
		</style>
	</head>
	<body>
		<div class="wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-md-12">
						<div class="page-header">
							<h2>Create User</h2>
						</div>
						<p>Please fill this form and submit to add user to the database.</p>
						<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
							<div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
								<label>Full Name</label>
								<input type="text" name="name" class="form-control" value="<?php echo $name; ?>" required>
								<span class="help-block"><?php echo $name_err;?></span>
							</div>
							<div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
								<label>Username</label>
								<input type="text" name="username" class="form-control" value="<?php echo $username; ?>" required>
								<span class="help-block"><?php echo $username_err;?></span>
							</div>
							<div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
								<label>Password</label>
								<input type="password" name="password" class="form-control" value="<?php echo $password; ?>" required>
								<span class="help-block"><?php echo $password_err;?></span>
							</div>
							<div class="form-group">
								<label>License Number</label>
								<input type="number" name="licensenum" placeholder="Default: You have no license number" class="form-control" value="<?php echo $licensenum; ?>">
								<span class="help-block"><?php echo $licensenum_err;?></span>
							</div>
							<div class="form-group <?php echo (!empty($is_admin_err)) ? 'has-error' : ''; ?>">
								<label>Admin Priviliges (Y/N)</label>
								<input type="text" name="is_admin" class="form-control" value="<?php echo $is_admin; ?>" required>
								<span class="help-block"><?php echo $is_admin_err;?></span>
							</div>
							<input type="submit" class="btn btn-primary" value="Submit">
							<input type="reset" class="btn btn-warning" value="Reset">
							<a href="admin-users.php" class="btn btn-default">Go Back To User Page</a>
						</form>
					</div>
				</div>        
			</div>
		</div>
	</body>
</html>