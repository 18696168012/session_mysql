<?php
	class Session{
		private static $handler='';
		private static $client_ip='';
		private static $lifetime='';
		private static $time='';
		private static function init($handler){
			self::$handler=$handler;
			self::$client_ip=!empty($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'unknown';
			self::$lifetime=ini_get('session.gc_maxlifetime');
			self::$time=time();
		}
		static function start(PDO $pdo){
			self::init($pdo);
			session_set_save_handler(
				array(__CLASS__,'open'), 
				array(__CLASS__,'close'), 
				array(__CLASS__,'read'),
				array(__CLASS__,'write'),
				array(__CLASS__,'destroy'),
				array(__CLASS__,'gc')
				);
			session_start();
		}
		public static function open($path,$name){
			return true;
		}
		public static function close(){
			return true;
		}
		public static function read($PHPSESSID){
			//echo 111;
			$sql="select * from session where PHPSESSID= ?";
			$stmt=self::$handler->prepare($sql);
			$stmt->execute(array($PHPSESSID));
			if(!$result=$stmt->fetch(PDO::FETCH_ASSOC)){
				echo 'aa';
				return '';
			}
			if(self::$client_ip != $result['client_ip']){
				echo 'bb';
				self::destroy($PHPSESSID);
				return '';
			}
			if(($result['update_time'] + self::$lifetime) <time()){
				echo 'cc';
				self::destroy($PHPSESSID);
				return '';
			}
			//var_dump($result);
			echo 'ee';
			return $result['data'];
		}
		public static function write($PHPSESSID,$data){
			$sql="select * from session where PHPSESSID= ?";
			$stmt=self::$handler->prepare($sql);
			$stmt->execute(array($PHPSESSID));
			if($result=$stmt->fetch(PDO::FETCH_ASSOC)){
				if($result['data'] != $data || self::$time>($result['update_time']+30)){
					echo 22;
					$sql="update session set update_time=?,data=? where PHPSESSID=?";
					$stm=self::$handler->prepare($sql);
					//print_r($stm);
					$stm->execute(array(self::$time,$data,$PHPSESSID));
				}
			}else{
				if(!empty($data)){
					echo '33';
					//开始插入
					$sql="insert into session(PHPSESSID,update_time,client_ip,data) values(?,?,?,?)";
					$sth=self::$handler->prepare($sql);
					$sth->execute(array($PHPSESSID,self::$time,self::$client_ip,$data));
				}
			}
			return true;
		}
		public static function destroy($id){
			echo 44;
			$sql='delete from session where PHPSESSID=?';
			$stmt=self::$handler->prepare($sql);
			$stmt->execute(array($id));
			return true;
		}
		public static function gc($lifetime){
			echo 55;
			$sql='delete from session where update_time < ?';
			$stmt=self::$handler->prepare($sql);
			$stmt->execute(array(self::$time-$lifetime));
			return true;
		}
	}
	try{
		$pdo=new pdo('mysql:host=localhost;dbname=test','root','');
	}catch(PODException $e){
		echo $e->getMessage();
	}
	Session::start($pdo);