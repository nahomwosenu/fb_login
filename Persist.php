<?php

class Persist
{
    static $ENDPOINT="http://localhost/coc";
    static $server='localhost';
    static $user='root';
    static $pass='';
    static $db='facebook';
    static function save($object,$entity){
        $array=json_encode($object);
        $array2=json_decode($array,true);

        $con=mysqli_connect(self::$server,self::$user,self::$pass,self::$db) or die(mysqli_error($con));
        $columns=implode(",", self::getColumns($array2));
        //var_dump($columns);
        $list=array();
        $dtypes=array();
        $data=self::getData($array2);
        for($i=0;$i<count(self::getColumns($array2));$i++){
            $list[$i]="'?'";
            $dtypes[$i]="s";
            $data[$i]=$con->real_escape_string($data[$i]);
        }
        $keys=implode("','",$data);
        $keys="'".$keys."'";
        $query="insert into $entity ($columns) values($keys)";
        //echo "<p>Query: $query</p>";
        $p=mysqli_prepare($con,$query) or die(mysqli_error($con));
        $p->execute();
        $id=$p->insert_id;
        return $id;
    }
    static function getColumns($object){
        $columns=array();
        $i=0;
        foreach($object as $key=>$value){
            if($key=='id')
                continue;
            $columns[$i]=$key;
            $i++;
        }
        return $columns;
    }
    static function getData($object){
        $columns=array();
        $i=0;
        foreach($object as $key=>$value){
            if($key=='id')
                continue;
            if($value==null)
                $value="null";
            $columns[$i]="$value";
            $i++;
        }
        return $columns;
    }
    static function update($object,$entity){
        $columns=array();
        $values=array();
        $i=0;
        $array=json_encode($object);
        $array2=json_decode($array,true);
        $builder="";
        $con=mysqli_connect(self::$server,self::$user,self::$pass,self::$db) or die(mysqli_error($con));
        foreach($array2 as $key=>$value){
            $columns[$i]=$key;
            $value=$con->real_escape_string($value);
            $values[$i]=$value;
            if($i==0)
                $builder=$key."='".$value."'";
            else
            $builder=$builder.','.$key."='".$value."'";
            $i++;
        }
        $query="update $entity set $builder where id='$values[0]'";
        $st=mysqli_prepare($con,$query) or die(mysqli_error($con));
        $result=$st->execute();
        return $st->affected_rows;
    }
    static function getEntity($keys,$values,$connector,$entity,$class){
        $Class="Class".$class;
        $obj=new $Class;
        $condition="";
        for($i=0;$i<count($keys);$i++){
            if($i==0)
            $condition=$keys[$i]."='".$values[$i]."'";
            else
                $condition=$condition." ".$connector." ".$keys[$i]."='".$values[$i]."'";
        }
        $con=mysqli_connect(self::$server,self::$user,self::$pass,self::$db) or die(mysqli_error($con));
        $query="select * from $entity where $condition";
        $p=mysqli_prepare($con,$query) or die(mysqli_error($con));
        $r=$p->execute();
        $result=$p->get_result();
        $row=$result->fetch_assoc();
        return $row;
    }
    static function executeQuery($query){
        $con=mysqli_connect(self::$server,self::$user,self::$pass,self::$db) or die(mysqli_error($con));
        $result=mysqli_query($con,$query) or die(mysqli_error($con));
        $list=array();
        $i=0;
        while($row=mysqli_fetch_assoc($result)){
            foreach ($row as $key=>$value){
                $list[$i][$key]=$value;
            }
            $i++;
        }
        return $list;
    }
    static function executeUpdate($query){
        $con=mysqli_connect(self::$server,self::$user,self::$pass,self::$db) or die(mysqli_error($con));
        $result=mysqli_query($con,$query) or die(mysqli_error($con));
        return mysqli_affected_rows($con);
    }
    static function  exists($key,$value,$entity){
        $query="select $key from $entity where $key='$value'";
        $con=mysqli_connect(self::$server,self::$user,self::$pass,self::$db) or die(mysqli_error($con));
        $result=mysqli_query($con,$query) or die(mysqli_error($con));
        if($row=mysqli_fetch_array($result)){
            return true;
        }
        return false;
    }
    static function checkDuplicateUsername($username){
        $bool=self::exists("username",$username,"admin");
        if($bool===false){
            $bool=self::exists("student_id",$username,"student");
            if($bool===false){
                $bool=self::exists("email",$username,"student");
                if($bool===false){
                    $bool=self::exists("email",$username,"counceler");
                    if($bool===false){
                        $bool=self::exists("email",$username,"director");
                    }
                }
            }
        }
        return $bool;
    }
    static function checkDuplicate($field,$value){
        $bool=self::exists($field,$value,"student");
        if($bool===false){
            $bool=self::exists($field,$value,"counceler");
            if($bool===false){
                $bool=self::exists($field,$value,"director");
            }
        }
        return $bool;
    }
    static function getVerificationLink($id,$hash,$entity){
        $link=self::$ENDPOINT."/verify.php?token=$hash&id=$id&type=$entity";
        return $link;
    }
    static function verifyEmail($id,$entity){
        $query="update $entity set status='verified' where id='$id'";
        $r=self::executeUpdate($query);
        return $r;
    }
    static function getResetLink($id,$hash,$entity){
        $link=self::$ENDPOINT."/reset.php?token=$hash&id=$id&type=$entity";
        return $link;
    }
    static function escape($string){
        $con=mysqli_connect(self::$server,self::$user,self::$pass,self::$db) or die(mysqli_error($con));
        return mysqli_escape_string($con,$string);
    }

}