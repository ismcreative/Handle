<?php
require("database.class.php");
class SQLite extends database{
	var $server;
	var $username;
	var $password;
	var $database;
	var $queries_stats;
	var $overall_stats;
	function __construct($server="",$username="",$password="",$database){
		$this->queries_stats=array();
		$this->overall_stats=0;
        if(!($this->database = sqlite_open($database)))
		{
			echo "Failed to select database!";
		}
	}
	function isconnected(){
		if($this->database){
			return true;
		}
		return false;
	}
	function escape_string($string){
		$data=sqlite_escape_string($string);
		return $data;
	}
	function checkall($exists){
		foreach($exists as $one){
			if($one){
			}else{
				return false;
			}
		}
		return true;
	}
	
	function fetch($table,$data_array,$return="",$multi=false,$order_array=""){
        global $TABLES;
        $table = $TABLES[$table];

		$return_info="";
		if($return!=""){
			$return=explode(",",$return);
			$x=1;
			foreach($return as $item){
				if($x==1){
					$return_info=$item;
				}else{
					$return_info.=", ".$item."";
				}
				$x=2;
			}
		}else{
			$return_info="*";
		}
		if(is_array($data_array)){
			$x=1;
			foreach($data_array as $key=>$value){
				if($x==1){
					$where=$key."='".$this->escape_string($value)."'";
				}else{
					$where.=" AND ".$key."='".$this->escape_string($value)."'";
				}
				$x=2;
			}
			$where_out="WHERE ".$where;
		}
		
		$order="";
		if(is_array($order_array)){
			$order="ORDER BY ";
			foreach($order_array as $key=>$value){
				$ascdesc="ASC";
				if(strtoupper($value)=="DESC")$ascdesc="DESC";
				$order.=$key." ".$ascdesc.",";
			}
			$order=substr($order,0,-1);
		}
		
		$return=array();
		$starttime = time() + microtime();
		$sql_string="SELECT ".$return_info." FROM ".$table." ".$where_out." ".$order;
        $sql=sqlite_query($this->database,$sql_string);
		$stoptime = time() + microtime();
		$total = round($stoptime - $starttime,4);
		$this->queries_stats[]=array(
			"time"=>$total,
			"string"=>$sql_string
		);
		while($data=sqlite_fetch_array($sql, SQLITE_ASSOC)){
			$return_tmp=array($data);
			$return=array_merge($return_tmp,$return);
		}
		$this->overall_stats=$this->overall_stats+$total;
		if($multi){
			return $return;
		}else{
			return $return[0];
		}
	}
	function fetch_by($table,$data_array,$return,$multi=false,$order_array=""){
        global $TABLES;
        $table = $TABLES[$table];

		$return_info="*";
		if(is_array($data_array)){
			$x=1;
			foreach($data_array as $key=>$value){
				if($x==1){
					$where=$key."='".$this->escape_string($value)."'";
				}else{
					$where.=" AND ".$key."='".$this->escape_string($value)."'";
				}
				$x=2;
			}
			$where_out="WHERE ".$where;
		}
		$starttime = time() + microtime();
		$sql_string="SELECT ".$return_info." FROM ".$table." ".$where_out." ".$return;
		$sql=sqlite_query($this->database,$sql_string);
		$stoptime = time() + microtime();
		$total = round($stoptime - $starttime,4);
		$this->queries_stats[]=array(
			"time"=>$total,
			"string"=>$sql_string
		);
		$return=array();
		while($data=sqlite_fetch_array($sql,SQLITE_ASSOC)){
			$return_tmp=array($data);
			$return=array_merge($return_tmp,$return);
		}
		$this->overall_stats=$this->overall_stats+$total;
		if($multi){
			return $return;
		}else{
			return $return[0];
		}
	}
	function fetch_search($table,$data_array,$search,$return,$multi=false,$order_array=""){
        global $TABLES;
        $table = $TABLES[$table];

		$return_info="*";
		$x=1;
		if(is_array($data_array)){
			foreach($data_array as $key=>$value){
				if($x==1){
					$where=$key."='".$this->escape_string($value)."'";
					$x=2;
				}else{
					$where.=" AND ".$key."='".$this->escape_string($value)."'";
				}
			}
		}
		if($x==1){
			$where=$search[0]." LIKE '%".$this->escape_string($search[1])."%'";
		}else{
			$where.=" AND ".$search[0]." LIKE '%".$this->escape_string($search[1])."%'";
		}
		$where_out="WHERE ".$where;
		$sql=sqlite_query($this->database,"SELECT ".$return_info." FROM ".$table." ".$where_out." ".$return);
		$return=array();
		while($data=sqlite_fetch_array($sql,SQLITE_ASSOC)){
			$return_tmp=array($data);
			$return=array_merge($return_tmp,$return);
		}
		if($multi){
			return $return;
		}else{
			return $return[0];
		}
	}
	function insert($table,$data_array){
        global $TABLES;
        $table = $TABLES[$table];

		$x=1;
		foreach($data_array as $key=>$value){
			$value=$this->escape_string($value);
			if($x==1){
                $cols="{$key}";
                $vals="'{$value}'";
			}else{
                $cols.=",{$key}";
                $vals.=",'{$value}'";
			}
			$x=2;
		}
		
		$sql_string="INSERT INTO {$table} ({$cols}) VALUES ({$vals})";
		$data=sqlite_query($this->database,$sql_string);
		return $data;
	}
	function set($table,$data_array,$where_array){
		$x=1;
		foreach($data_array as $key=>$value){
			if($x==1){
				$set=$key."='".$this->escape_string($value)."'";
			}else{
				$set.=", ".$key."='".$this->escape_string($value)."'";
			}
			$x=2;
		}
		$x=1;
		if(is_array($where_array)){
			foreach($where_array as $key=>$value){
				if($x==1){
					$where=$key."='".$this->escape_string($value)."'";
				}else{
					$where.=" AND ".$key."='".$this->escape_string($value)."'";
				}
				$x=2;
			}
		}
		$data=sqlite_query($this->database,"UPDATE ".$table." SET ".$set." WHERE ".$where);
		return $data;
	}
	function delete($table,$where_array){
        global $TABLES;
        $table = $TABLES[$table];

		if(is_array($where_array)){
			$x=1;
			foreach($where_array as $key=>$value){
				if($x==1){
					$where=$key."='".$this->escape_string($value)."'";
				}else{
					$where.=" AND ".$key."='".$this->escape_string($value)."'";
				}
				$x=2;
			}
			$where="WHERE ".$where;
		}
		$data=sqlite_query($this->database,"DELETE FROM ".$table." ".$where);
		return $data;
	}
	function check_exists($table,$where_array,$multi=false){
        global $TABLES;
        $table = $TABLES[$table];

		$exists=array();
		$exists_single=false;
		if(is_array($where_array)){
			foreach($where_array as $key=>$value){
				$query=sqlite_query($this->database,"SELECT * FROM ".$table." WHERE ".$key."='".$this->escape_string($value)."'");
				if(sqlite_num_rows($query)==1){
					$exists[$key]=true;
					if($exists_single==false){
						$exists_single=true;
					}
				}else{
					$exists[$key]=false;
				}
			}
		}
		if($multi){
			return $exists;
		}else{
			return $exists_single;
		}
	}
	function check_exists_i($table,$where_array){
        global $TABLES;
        $table = $TABLES[$table];

		$exists=array();
		$exists_single=false;
		if(is_array($where_array)){
			$x=1;
			foreach($where_array as $key=>$value){
				if($x==1){
					$where=$key."='".$this->escape_string($value)."'";
				}else{
					$where.=" AND ".$key."='".$this->escape_string($value)."'";
				}
				$x=2;
			}
			$where="WHERE ".$where;
		}
		$query=sqlite_query($this->database,"SELECT * FROM ".$table." ".$where);
		if(sqlite_num_rows($query)==1){
			$exists=true;
		}else{
			$exists=false;
		}
		return $exists;
	}
}
?>