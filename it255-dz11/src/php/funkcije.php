<?php 
	include("konekcija.php");
	
	function checkIfLoggedIn(){
		global $con;
		if(isset($_SERVER['HTTP_TOKEN'])){
			$token = $_SERVER['HTTP_TOKEN'];
			$result = $con->prepare("SELECT * FROM korisnici WHERE token=:token");
			$result->bindParam(":token",$token);
			$result->execute();
			if($data = $result->fetch() > 0)
			{
				return true;
			}
			else{   
				return false;
			}
		}
		else{
			return false;
		}
	}
	
	function login($username, $password){
		global $con;
		$rarray = array();
		if(checkLogin($username, $password)){
			$id = sha1(uniqid());
			$result2 = $con->prepare("UPDATE korisnici SET token=:token WHERE username=:username");
			$result2->bindParam(":token",$id);
			$result2->bindParam(":username",$username);
			$result2->execute();
			$rarray['token'] = $id;
		} else{
			header('HTTP/1.1 401 Unauthorized');
			$rarray['error'] = "Invalid username/password";
		}
		return json_encode($rarray);
	}
	function checkLogin($username, $password){
		global $con;
		$password = md5($password);
		$result = $con->prepare("SELECT * FROM korisnici WHERE username=:username AND password=:password");
		$result->bindParam(":username", $username);
		$result->bindParam(":password", $password);
		$result->execute();
		if($data = $result->fetch() > 0){
			return true;
		}
		else{   
			return false;
		}
	}
	function register($username, $password, $ime, $prezime, $email){
		global $con;
		$rarray = array();
		$errors = "";
		if(checkIfUserExists($username)){
			$errors .= "Username already exists\r\n";
		}
		if(strlen($username) < 5 || $username == ""){
			$errors .= "Username must have at least 5 characters and cannot be empty\r\n";
		}
		if(strlen($password) < 5 || $password == ""){
			$errors .= "Password must have at least 5 characters and cannot be empty\r\n";
		}
		if(strlen($ime) < 3 || $ime == ""){
			$errors .= "Name must have at least 3 characters and cannot be empty\r\n";
		}
		if(strlen($prezime) < 3 || $prezime == ""){
			$errors .= "Last name must have at least 3 characters and cannot be empty\r\n";
		}
		if(strlen($email) < 3 || $email == ""){
			$errors .= "E-mail must have at least 3 characters and cannot be empty\r\n";
		}
		
		if($errors == ""){
			$stmt = $con->prepare("INSERT INTO korisnici (username, password, ime, prezime, email) VALUES (:username, :password, :ime, :prezime, :email)");
			$password =md5($password);
			$stmt->bindParam(":username", $username);
			$stmt->bindParam(":password", $password);
			$stmt->bindParam(":ime", $ime);
			$stmt->bindParam(":prezime", $prezime);
			$stmt->bindParam(":email", $email);
			
			if($stmt->execute()){
				$id = sha1(uniqid());
				$result2 = $con->prepare("UPDATE korisnici SET token=:token WHERE username=:username");
				$result2->bindParam(":token",$id);
				$result2->bindParam(":username",$username);
				$result2->execute();
				$rarray['token'] = $id;
			}else{
				header('HTTP/1.1 400 Bad request');
				$rarray['error'] = "Database connection error";
			}
		} else{
			header('HTTP/1.1 400 Bad request');
			$rarray['error'] = json_encode($errors);
		}
		
		return json_encode($rarray);
	}
 
	function checkIfUserExists($username){
		global $con;
		$result = $con->prepare("SELECT * FROM korisnici WHERE username=:username");
		$result->bindParam(":username",$username);
		$result->execute();
		if($data = $result->fetch() > 0)
		{
			return true;
		}
		else{   
			return false;
		}
	}
?>