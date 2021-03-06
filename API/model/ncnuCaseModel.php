<?php
session_start();
require("../../dbconnect.php");
function addCase($data) {
    global $conn;
    $sql = "insert into ncnucase (";
    foreach ($data as $key => $value) {
        $data[$key] = mysqli_real_escape_string($conn,$value);
        if($key!="act")
            $sql .= $key .",";
    }
    $sql.="uid";
    // $sql = substr($sql,0,-1);
    $sql .=") values(";
    foreach ($data as $key => $value) {
        $data[$key] = mysqli_real_escape_string($conn,$value);
        if($key!="act")
            $sql .= '"'.$value.'"' .",";
    }
    if(!@$_SESSION["user"]){
        $result["status"]=400;
        return $result;
    }
    else
        $user = $_SESSION["user"];
    $sql.= "'$user'";
    // $sql = substr($sql,0,-1);
    $sql .=")";
    // print($sql);
    mysqli_query($conn,$sql);
    $result = mysqli_error($conn);
    if(!$result)
        $result["status"]=200;
    else if($result =="Duplicate entry '$account' for key 'PRIMARY'"){
        $result=array();
        $result["status"]=400;
    }
    else{
        $result=array();
        $result["status"]=501;
    }
    return $result;
}
function getAllCase(){
    global $conn;
    $sql = "select * from ncnucase";
    $result = mysqli_query($conn,$sql);
    $dbData = array();
    while($rs = mysqli_fetch_assoc($result)){
        // print_r($rs);
        array_push($dbData, $rs);
    }
    return $dbData;
}
function getSomeCase($data){
    // print_r($data);
    global $conn;
    $check=0;
    foreach ($data as $key => $value) {
        $data[$key] = mysqli_real_escape_string($conn,$value);
    }
    $start = $data["start"];
    $len = $data["len"];
    @$order = $data["order"];
    @$direct = $data["direct"];
    $sql = "select * from ncnucase where 1=0 ";
    @$property = explode("，", $data["property"]);
    foreach ($property as $key => $value) {
        if($value!=""){
            $sql.="or property like '%$value%' ";
        }else{
            $check++;
        }
    }
    @$type = explode("，", $data["type"]);
    foreach ($type as $key => $value) {
        if($value!=""){
            $sql.="or type like '%$value%' ";
        }else{
            $check++;
        }
    }
    @$time = explode("，", $data["time"]);
    foreach ($time as $key => $value) {
        if($value!=""){
            $sql.="or time like '%$value%' ";
        }else{
            $check++;
        }
    }
    if($check==3){
        $numsql = "select * from ncnucase";
        $sql = "select * from ncnucase order by ABS($order) $direct limit $start,$len";
    }
    else{
        $numsql = $sql;
        $sql .="order by ABS($order) $direct limit $start,$len";
    }
    // print_r("\n".$sql."\n\n");
    $query = mysqli_query($conn,$sql);
    $numquery = mysqli_query($conn,$numsql);
    $totalData = mysqli_num_rows($numquery);
    $dbData = array();
    while($result = mysqli_fetch_assoc($query)){
        array_push($dbData, $result);
    }
    @$user = $_SESSION["user"];
    $sql = "select * from likelist where uid='$user'";
    $query = mysqli_query($conn,$sql);
    $likelist=array();
    while($result = mysqli_fetch_assoc($query)){
        array_push($likelist, $result["cid"]);
    }
    $sql = "select * from connected where uid='$user'";
    $query = mysqli_query($conn,$sql);
    $connectedlist=array();
    while($result = mysqli_fetch_assoc($query)){
        array_push($connectedlist, $result["cid"]);
    }
    // $_SESSION["user"]="nancy";
    foreach ($dbData as $key => $value) {
        $dbData[$key]["like"]=0;
        foreach ($likelist as $likekey => $likevalue) {
            if($value["id"]==$likevalue){
                $dbData[$key]["like"]=1;
            }
        }
    }
    foreach ($dbData as $key => $value) {
        $dbData[$key]["connected"]=0;
        foreach ($connectedlist as $connectedkey => $connectedvalue) {
            if($value["id"]==$connectedvalue){
                $dbData[$key]["connected"]=1;
            }
        }
    }
    if(!@$_SESSION["user"]){
        foreach ($dbData as $key => $value)
        $dbData[$key]["like"]=-1;
        $dbData[$key]["connected"]=0;
    }    
    $result=array();
    $result["data"]=$dbData;
    $result["num"]=$totalData;
    // print_r($dbData);
    return $result;
}
function getLikeList($data){
    // print_r($data);
    global $conn;
    $check=0;
    foreach ($data as $key => $value) {
        $data[$key] = mysqli_real_escape_string($conn,$value);
    }
    //去喜好清單撈目前使用者喜好的清單
    // print_r($_SESSION["user"]);
    $user = $_SESSION["user"];
    @$order = $data["order"];
    @$direct = $data["direct"];
    $sql = "select * from likelist where uid = '$user'";
    $query = mysqli_query($conn,$sql);
    $likelist = array();
    while ($row = mysqli_fetch_assoc($query)) {
        array_push($likelist,$row["cid"]);
    }
    //依據喜好清單id撈工作詳細內容
    $start = $data["start"];
    $len = $data["len"];
    $sql = "select * from likelist left join ncnucase on likelist.cid = ncnucase.id where likelist.uid='$user' and (1=0 ";
    @$property = explode("，", $data["property"]);
    foreach ($property as $key => $value) {
        if($value!=""){
            $sql.="or property like '%$value%' ";
        }else{
            $check++;
        }
    }
    @$type = explode("，", $data["type"]);
    foreach ($type as $key => $value) {
        if($value!=""){
            $sql.="or type like '%$value%' ";
        }else{
            $check++;
        }
    }
    @$time = explode("，", $data["time"]);
    foreach ($time as $key => $value) {
        if($value!=""){
            $sql.="or time like '%$value%' ";
        }else{
            $check++;
        }
    }
    @$level = explode("，", $data["level"]);
    foreach ($level as $key => $value) {
        if($value!=""){
            $sql.="or level like '%$value%' ";
        }else{
            $check++;
        }
    }
    if($check==4){
        $sql = "select * from ncnucase where 1=0 ";
        foreach ($likelist as $key => $value) {
            if($value!=""){
                $sql.="or id = '$value' ";
            }else{
                $check++;
            }
        }
    }else{
        $sql .=")";
    }
    $sql .=" order by ABS($order) $direct limit $start,$len";
    $numsql = $sql; 
    $query = mysqli_query($conn,$numsql);
    $totalData = mysqli_num_rows($query);
    // print_r("\n".$sql."\n\n");
    $query = mysqli_query($conn,$sql);
    $dbData = array();
    while($result = mysqli_fetch_assoc($query)){
        array_push($dbData, $result);
    }
    @$user = $_SESSION["user"];
    $sql = "select * from likelist where uid='$user'";
    $query = mysqli_query($conn,$sql);
    $likelist=array();
    while($result = mysqli_fetch_assoc($query)){
        array_push($likelist, $result["cid"]);
    }
    // $_SESSION["user"]="nancy";
    foreach ($dbData as $key => $value) {
        $dbData[$key]["like"]=0;
        foreach ($likelist as $likekey => $likevalue) {
            if($value["id"]==$likevalue){
                $dbData[$key]["like"]=1;
            }
        }
    }
    $result=array();
    $result["data"]=$dbData;
    $result["num"]=$totalData;
    // print_r($dbData);
    return $result;
}
function getPublish($data){
    // print_r($data);
    global $conn;
    $check=0;
    foreach ($data as $key => $value) {
        $data[$key] = mysqli_real_escape_string($conn,$value);
    }
    $start = $data["start"];
    $len = $data["len"];
    $user = $_SESSION["user"];
    @$order = $data["order"];
    @$direct = $data["direct"];
    $numsql = "select * from ncnucase where uid = '$user'";
    $sql  ="select ncnucase.*,count(a.who) as num from ncnucase left join (select connected.uid as who,ncnucase.uid,connected.cid from ncnucase,connected where ncnucase.id=connected.cid) as a on ncnucase.uid = a.uid and ncnucase.id=a.cid where ncnucase.uid='$user' GROUP by ncnucase.id ";
    $sql .="order by ABS($order) $direct limit $start,$len";
    // print_r($sql);
    $query = mysqli_query($conn,$numsql);
    $totalData = mysqli_num_rows($query);
    $query = mysqli_query($conn,$sql);
    $dbData = array();
    while($result = mysqli_fetch_assoc($query)){
        array_push($dbData, $result);
    }
    $result=array();
    $result["data"]=$dbData;
    $result["num"]=$totalData;
    // print_r($dbData);
    return $result;
}                   
function caseConnect($data) {
    global $conn;
    $sql = "insert into connected (";
    foreach ($data as $key => $value) {
        $data[$key] = mysqli_real_escape_string($conn,$value);
        if($key!="act")
            $sql .= $key .",";
    }
    if($uid = @$_SESSION["user"])
        $sql .="uid";
    else{
        $result["status"]=400;
        return $result;
    }
    // $sql = substr($sql,0,-1);
    $sql .=") values(";
    foreach ($data as $key => $value) {
        $data[$key] = mysqli_real_escape_string($conn,$value);
        if($key!="act")
            $sql .= '"'.$value.'"' .",";
    }
    $cid = $data["cid"];
    $sql.="'$uid'";
    // $sql = substr($sql,0,-1);
    $sql .=")";
    mysqli_query($conn,$sql);
    $result = mysqli_error($conn);
    if(!$result){
        $result["status"]=200;
        $sql = "select * from ncnucase where id = '$cid'";
        $case = mysqli_fetch_assoc(mysqli_query($conn,$sql));
        $to =$case["email"]; //收件者
        $subject = "您在血汗暨南發的工作'".$case["title"]."'有一則新的履歷"; //信件標題
        $msg = "請至163.22.17.225使用帳號 ".$case["uid"]." 查看履歷";//信件內容
        $headers = "From: ncnujob@ncnu.edu.tw"; //寄件者
        mail("$to", "$subject", "$msg", "$headers");
    }
    else if($result =="Duplicate entry '$account' for key 'PRIMARY'"){
        $result=array();
        $result["status"]=400;
    }
    else{
        $result=array();
        $result["status"]=501;
    }
    return $result;
}
function caseUnConnect($data){
    global $conn;
    foreach ($data as $key => $value) {
        $data[$key] = mysqli_real_escape_string($conn,$value);
    }
    // print_r($data);
    $cid = $data["cid"];
    $uid = @$_SESSION["user"];
    $sql = "delete from connected where cid='$cid' and uid='$uid'";
    mysqli_query($conn,$sql);
    $result = mysqli_error($conn);
    if(!$result)
        $result["status"]=200;
    else{
        $result=array();
        $result["status"]=501;
    }
    return $result;
}
function likeCase($data) {
    global $conn;
    $sql = "insert into likelist (";
    foreach ($data as $key => $value) {
        $data[$key] = mysqli_real_escape_string($conn,$value);
        if($key!="act")
            $sql .= $key .",";
    }
    $sql .="uid";
    // $sql = substr($sql,0,-1);
    $sql .=") values(";
    foreach ($data as $key => $value) {
        $data[$key] = mysqli_real_escape_string($conn,$value);
        if($key!="act")
            $sql .= '"'.$value.'"' .",";
    }
    $account = $_SESSION["user"];
    $sql .= "'$account'";
    // $sql = substr($sql,0,-1);
    $sql .=")";
    // print($sql);
    mysqli_query($conn,$sql);
    $result = mysqli_error($conn);
    if(!$result)
        $result["status"]=200;
    else if($result =="Duplicate entry '$account' for key 'PRIMARY'"){
        $result=array();
        $result["status"]=400;
    }
    else{
        $result=array();
        $result["status"]=501;
    }
    return $result;
}
function unLikeCase($data){
    global $conn;
    foreach ($data as $key => $value) {
        $data[$key] = mysqli_real_escape_string($conn,$value);
    }
    // print_r($data);
    $cid = $data["cid"];
    $uid = @$_SESSION["user"];
    $sql = "delete from likelist where cid='$cid' and uid='$uid'";
    mysqli_query($conn,$sql);
    $result = mysqli_error($conn);
    if(!$result)
        $result["status"]=200;
    else{
        $result=array();
        $result["status"]=501;
    }
    return $result;
}
function getConnect($data){
    // print_r($data);
    global $conn;
    $check=0;
    foreach ($data as $key => $value) {
        $data[$key] = mysqli_real_escape_string($conn,$value);
    }
    //去喜好清單撈目前使用者喜好的清單
    // print_r($_SESSION["user"]);
    $user = $_SESSION["user"];
    @$order = $data["order"];
    @$direct = $data["direct"];
    $sql = "select * from connected where uid = '$user'";
    $query = mysqli_query($conn,$sql);
    $connected = array();
    while ($row = mysqli_fetch_assoc($query)) {
        array_push($connected,$row["cid"]);
    }
    //依據喜好清單id撈工作詳細內容
    $start = $data["start"];
    $len = $data["len"];
    $sql = "select * from connected left join ncnucase on connected.cid = ncnucase.id where connected.uid='$user' and(1=0 ";
    @$property = explode("，", $data["property"]);
    foreach ($property as $key => $value) {
        if($value!=""){
            $sql.="or property like '%$value%' ";
        }else{
            $check++;
        }
    }
    @$type = explode("，", $data["type"]);
    foreach ($type as $key => $value) {
        if($value!=""){
            $sql.="or type like '%$value%' ";
        }else{
            $check++;
        }
    }
    @$time = explode("，", $data["time"]);
    foreach ($time as $key => $value) {
        if($value!=""){
            $sql.="or time like '%$value%' ";
        }else{
            $check++;
        }
    }
    @$level = explode("，", $data["level"]);
    foreach ($level as $key => $value) {
        if($value!=""){
            $sql.="or level like '%$value%' ";
        }else{
            $check++;
        }
    }
    if($check==4){
        $sql = "select * from ncnucase where 1=0 ";
        foreach ($connected as $key => $value) {
            if($value!=""){
                $sql.="or id = '$value' ";
            }else{
                $check++;
            }
        }
    }else{
        $sql .=")";
    }
    $numsql = $sql; 
    $sql .="order by ABS($order) $direct limit $start,$len";
    $query = mysqli_query($conn,$numsql);
    $totalData = mysqli_num_rows($query);
    // print_r("\n".$sql."\n\n");
    $query = mysqli_query($conn,$sql);
    $dbData = array();
    while($result = mysqli_fetch_assoc($query)){
        array_push($dbData, $result);
    }
    @$user = $_SESSION["user"];
    $sql = "select * from likelist where uid='$user'";
    $query = mysqli_query($conn,$sql);
    $likelist=array();
    while($result = mysqli_fetch_assoc($query)){
        array_push($likelist, $result["cid"]);
    }
    // $_SESSION["user"]="nancy";
    foreach ($dbData as $key => $value) {
        $dbData[$key]["like"]=0;
        foreach ($likelist as $likekey => $likevalue) {
            if($value["id"]==$likevalue){
                $dbData[$key]["like"]=1;
            }
        }
    }
    $result=array();
    $result["data"]=$dbData;
    $result["num"]=$totalData;
    // print_r($dbData);
    return $result;
}
function getCaseDetail($data){
    global $conn;
    $caseID = mysqli_real_escape_string($conn,$_GET["id"]);
    $refresh = mysqli_real_escape_string($conn,$_GET["refresh"]);
    $user = @$_SESSION["user"];
    if($refresh){
        $sql = "UPDATE `ncnucase` SET `views` = views+1 WHERE `ncnucase`.`id` = '$caseID';";
        mysqli_query($conn,$sql);
    }
    $sql = "select ncnucase.*,connected.uid as employees from ncnucase left join connected on ncnucase.id = connected.cid where ncnucase.id = '$caseID'";
    $query = mysqli_query($conn,$sql);
    $result = "";
    while ($row = mysqli_fetch_assoc($query)) {
        // if($row["uid"])
        $result = $row;
        if($row["employees"]==$user)
            return $row;
    }
    $result["employees"]="";
    return $result;
}
function getResumeList($data){
    global $conn;
    $result = array();
    $caseID = mysqli_real_escape_string($conn,$_GET["id"]);
    $sql = "select * from connected left join resume on resume.uid = connected.uid where connected.cid = $caseID";
    $query = mysqli_query($conn,$sql);
    while($row = mysqli_fetch_assoc($query)){
        array_push($result, $row);
    }
    return $result;
}
// function deleteUser($alldata){
//     global $conn;
//     $sql = "delete from user where 1=0 ";
//     foreach ($alldata["id"] as $key => $value) {
//         $sql .= "or Account = '$value' ";
//     }
//     if(mysqli_query($conn,$sql)){
//         return 200;
//     }else{
//         return 501;
//     }
// }
// function DataTablegetUser($requestData) {
//         global $conn;
//         $tablename='user';
//         /***本方法有資料庫權限可使用****/
//         // $columns = array();
//         //取欄位名稱
//         // $sql = "SELECT COLUMN_NAME,ORDINAL_POSITION,DATA_TYPE,CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".$tablename."'";
//         // $query=mysqli_query($conn, $sql) or die("employee-grid-data.php: get employees");
//         // //id不排
//         // // $row = mysqli_fetch_array($query);
//         // while($row = mysqli_fetch_array($query)){
//         //     if($row[0]!="day")
//         //     $columns[] =$row[0];
//         // }
//         /******************************/
//         // print_r($columns);
//         /***無資料庫權限使用**********/
//         $columns =Array
//         ("Name","Account","Phone","Hospital");
//         /*******************************/
//         $sql = "SELECT * ";
//         $sql.=" FROM ".$tablename."";
//         $query=mysqli_query($conn, $sql) or die("employee-grid-data.php: get employees");
//         $totalData = mysqli_num_rows($query);
//         $totalFiltered = $totalData;  // when there is no search parameter then total number rows = total number filtered rows.
//         $sql = "SELECT ".$columns[0];
//         for ($i=1; $i <count($columns); $i++) { 
//             $sql.=", ".$columns[$i];
//         }
//         $sql.=" FROM ".$tablename." WHERE 1=1 ";
//         // echo $sql;
//         // $startday = $requestData['data'][1]['startday'];
//         // $endday = $requestData['data'][1]['endday'];
//         //         if(!empty($startday)&&empty($endday)){
//         //                $sql .= "and writetime >= '$startday' ";
//         //         }else if(empty($startday)&&!empty($endday)){
//         //                $sql .= "and writetime <= '$endday' ";
//         //         }else if(!empty($startday)&&!empty($endday)){
//         //                $sql .= "and writetime >= '$startday' and writetime <= '$endday' ";
//         //         }else{
//         //         }
//         //         // print_r($requestData['search']);
//         if( !empty($requestData['search']['value']) ) {   // if there is a search parameter, $requestData['search']['value'] contains search parameter
//             $sql.=" AND ( ".$columns[0]." LIKE '%".$requestData['search']['value']."%' ";
//             for ($i=1; $i <count($columns)-1; $i++) { 
//                 $sql.=" OR ".$columns[$i]." LIKE '%".$requestData['search']['value']."%' ";
//             }
//             $sql.=" OR ".$columns[$i]." LIKE '%".$requestData['search']['value']."%' )";
//             // $sql.=" OR writetime LIKE '".$requestData['search']['value']."%' ";

//             // $sql.=" OR height LIKE '".$requestData['search']['value']."%' )";
//             // $sql.=" OR weight LIKE '".$requestData['search']['value']."%' )";
//         }
//         // if(!empty($requestData['columns'][2]['search']['value']) )
//                 // $sql.=" AND ".$columns[1]." LIKE '%".$requestData['columns'][2]['search']['value']."' ";
//         // print_r($column);
//         for ($i=1; $i <count($columns)+1 ; $i++) { 
//             if( isset($requestData['columns'][$i]['search']['value']) ){   //name
//                 $sql.=" AND ".$columns[$i-1]." LIKE '%".$requestData['columns'][$i]['search']['value']."%' ";
//             }
//         }
//         // echo $sql;
//         $query=mysqli_query($conn, $sql) or die("employee-grid-data.php: get employees");
//         $totalFiltered = mysqli_num_rows($query); // when there is a search parameter then we have to modify total number filtered rows as per search result. 
//         // $sql.=" ORDER BY ". $columns[0]."   asc  LIMIT 0 ,10   ";
//         // echo $sql;
//         if($requestData['length']!=-1){
//             if($requestData['order'][0]['column']==0)
        //         $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        //     else{
        //         $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']-1]."   ".$requestData['order'][0]['dir']."  LIMIT ".$requestData['start']." ,".$requestData['length']."   ";
        //     }
        // }
        // else
        //     $sql.=" ORDER BY ". $columns[$requestData['order'][0]['column']-1]."   ".$requestData['order'][0]['dir'];
        // // echo $columns[$requestData['order'][0]['column']-1];
        // // echo $sql;
        // // print_r($requestData);
        // // echo $columns[$requestData['order'][0]['column']-1];
//         /* $requestData['order'][0]['column'] contains colmun index, $requestData['order'][0]['dir'] contains order such as asc/desc  */    
//         $query=mysqli_query($conn, $sql) or die("employee-grid-data.php: get employees");
//         mysqli_close($conn);
//         $data = array();
//         while( $row=mysqli_fetch_array($query) ) {  // preparing an array
//             $nestedData=array(); 
//             for ($i=0; $i <count($columns); $i++) { 
//                 $nestedData[] = $row[$columns[$i]];
//             }
//             $data[] = $nestedData;
//         }
//         $json_data = array(
//                     "draw"            => intval( $requestData['draw'] ),

//                     // "draw"            => intval( 2 ),   // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw. 
//                     "recordsTotal"    => intval( $totalData ),  // total number of records
//                     "recordsFiltered" => intval( $totalFiltered ), // total number of records after searching, if there is no searching then totalFiltered = totalData
//                     "data"            => $data   // total data array
//                     );
//         return $json_data;
//         // return json_encode($table, JSON_FORCE_OBJECT);
// }
// function getfrontuser(){
//         return $_SESSION["user"];
// }
// function logout(){
//         return $_SESSION["user"]="";
// }
?>
