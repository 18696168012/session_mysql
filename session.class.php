<?php
	/*
	*session入库
	 */
	$sdbc='';
	//打开会话
	function open_session(){
		global $sdbc;
		$sdbc=mysqli_connect('localhost','root','','session');
	}
	//关闭会话
	function close_session(){
		$sdbc=mysqli_connect('localhost','root','','session');
		//global $sdbc;
		return mysqli_close($sdbc);
	}
	//读取会话
	function read_session($sid){
		global $sdbc;
		//print_r($sdbc);
		$q=sprintf('select data from session where id=%d',mysqli_real_escape_string($sdbc,$sid));
		$r=mysqli_query($sdbc,$q);
		if( mysqli_num_rows($r) == 1){
			list($data)=mysqli_fetch_array($r,MYSQLI_NUM);
			return $data;
		}else{
			return '';
		}
	}
	//写会话
	function write_session($sid,$data){
		$sdbc=mysqli_connect('localhost','root','','session');
		/*global $sdbc;
		print_r($sdbc);*/
		$q=sprintf('replace into session(id,data) values("%s","%s")',mysqli_real_escape_string($sdbc,$sid),mysqli_real_escape_string($sdbc,$data));
		$r=mysqli_query($sdbc,$q);
		var_dump($r);
		return true;
	}
	//销毁会话
	function destroy_session($sid){
		global $sdbc;
		$q=sprintf('delete from session where id=%d',mysqli_escape_string($sdbc,$sid));
		$r=mysqli_query($sdbc,$q);
		var_dump($r);
		$_SESSION=array();
		return true;
	}
	//垃圾回收
	function clean_session($expire){
		global $sdbc;
		ECHO 1;
		$q=sprintf('delete from session where last_accessed<%d',(int)$expire);
		echo $q;
		$r=mysqli_query($sdbc,$q);
		var_dump($r); 
		return true;
	}
	//使用会话处理函数
	session_set_save_handler('open_session','close_session','read_session','write_session','destroy_session','clean_session');
	//启动会话
	session_start();