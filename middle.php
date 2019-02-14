<?php
/*
Nikhil Patel
CS-490: Middle-end
Project: Final
*/

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}


function grade($username, $password, $testID, $user, $QID, $answers, $student_answer, $points, $testcases, $numOfTestCases, $keywords, $user_test, $testcaseIDs, $insertPointstestcase){
    // run student code
    $student_answer = str_replace('~', '+', $student_answer);
    
    // CHECK STUDENTS CODE
    //=====================================================================
    //$keywords = "doubleit, return";
    $keywords = str_replace(' ', '', $keywords);
    $keyword = explode(",", $keywords);
    $comment = " ";

    for($x=0;$x<sizeof($keyword);$x++)
    {
        $val = strpos($student_answer, $keyword[$x]);
        if($val !== false)
        {
            $points = $points - 0;
        }
        else
        {
            $points = $points - 3;
            //echo "missing:\t" . $keyword[$x] . "<br>";
            if($keyword[$x] == "return")
            {
                if (strpos($student_answer, 'print') !== false) {
                    $student_answer = str_replace("print", $keyword[$x], $student_answer);
                }
                $comment .= "Missing return, -3 points\n";
            }
            else if($keyword[$x] == "for" || $keyword[$x] == "while" )
            {
                $comment .= "Missing for/while loop, -3 points\n"; 
            }
            else if($keyword[$x] == "if" )
            {
                $comment .= "Missing if loop, -3 points\n"; 
            }
            else
            {
                $parsed = get_string_between($student_answer, 'def ', '(');// trim is useless
                $student_answer = str_replace($parsed, $keyword[$x], $student_answer);
                $comment .= "Incorrect Function Name, -3 points\n";
                
            }
        }   
    }
    // colon check
    foreach(preg_split("/((\r?\n)|(\r\n?))/", $student_answer) as $line){
        $temp1 = strpos($line, "def");
        $temp2 = strpos($line, " if");
        $temp3 = strpos($line, "else");
        $temp4 = strpos($line, "elif");
        $temp5 = strpos($line, "for");
        $temp6 = strpos($line, "while");

        if($temp1 !== false){
            $temp = strpos($line, "def");
            $line = trim($line);
            if($line[strlen($line)-1] != ":")
            {
                $comment .= "Colon is Missing after def, WARNING\n";// $comment .=
                $t = strpos($student_answer, "def");
                $line = trim($line);
                $index = ($t) + strlen($line);
                $student_answer = substr_replace($student_answer, ":", $index, 0);
            }
        }
        else if($temp2 !== false) {
            $temp = strpos($line, "if");
            if($line[strlen($line)-1] != ":")
            {
                $comment .= "Colon is Missing after if, WARNING\n";
                $t = strpos($student_answer, "if");
                $line = trim($line);
                $index = ($t) + strlen($line);
                $student_answer = substr_replace($student_answer, ":", $index, 0);

            }
        }
        else if($temp4 !== false) {
            $temp = strpos($line, "elif");
            if($line[strlen($line)-1] != ":")
            {
                $comment .= "Colon is Missing after elif, WARNING\n";
                $t = strpos($student_answer, "elif");
                $line = trim($line);
                $index = ($t) + strlen($line);
                $student_answer = substr_replace($student_answer, ":", $index, 0);

            }
        }
        else if($temp3 !== false) {
            $temp = strpos($line, "else");
            if($line[strlen($line)-1] != ":")
            {
                $t = strpos($student_answer, "else");
                $comment .= "Colon is Missing after else, WARNING\n";
                $line = trim($line);
                $index = ($t) + strlen($line);
                $student_answer = substr_replace($student_answer, ":", $index, 0);

            }
        }
        else if($temp5 !== false) {
            $temp = strpos($line, "for");
            if($line[strlen($line)-1] != ":")
            {
                $t = strpos($student_answer, "for");
                $comment .= "Colon is Missing after for, WARNING\n";
                $line = trim($line);
                $index = ($t) + strlen($line);
                $student_answer = substr_replace($student_answer, ":", $index, 0);

            }
        }
        else if($temp6 !== false) {
            $temp = strpos($line, "while");
            if($line[strlen($line)-1] != ":")
            {
                $t = strpos($student_answer, "while");
                $comment .= "Colon is Missing after while, WARNING\n";
                $line = trim($line);
                $index = ($t) + strlen($line);
                $student_answer = substr_replace($student_answer, ":", $index, 0);
            }
        }

    }
    //=====================================================================
    
    //=========================================================================
    $grade = 0;
   
    for($x=0;$x<$numOfTestCases;$x++){ // rethink this it does not work **********************
        $str = "";
        if(in_array("print", $keyword)){
            $str = ($str . $testcases[$x] . "\n");
        }
        else{ 
            $str = ($str . "print(" . $testcases[$x] . ")" . "\n");
        }
        
        $file = 'testfile.txt';
        $myfile = fopen($file, "w") or die ("Unable to open file!");
        $write = ($student_answer . "\n" . $str);
        fwrite($myfile, $write);
        fclose($myfile);
        $run= shell_exec('python ' . $file);
        
        $val = strpos($run, ($answers[$x]."\n"));
        
        if($val !== false){
            $grade = $grade + round(($points/$numOfTestCases), 2);
            $info = array('username' => $username, 'password' => $password, 'user_test_question' => $insertPointstestcase, 'test_case'=> $testcaseIDs[$x], 'points'=> round(($points/$numOfTestCases), 2), 'output' => $run);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/update_user_test_question_test_case');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $send_back = curl_exec($ch);
            curl_close($ch);
//            echo "correct";
//            echo "<br>";
        }
        else{
            // zero points
            $info = array('username' => $username, 'password' => $password, 'user_test_question' => $insertPointstestcase, 'test_case'=> $testcaseIDs[$x], 'points'=> 0, 'output' => $run);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/update_user_test_question_test_case');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $send_back = curl_exec($ch);
            curl_close($ch);
            $comment .= "Incorrect Test Case: " . round($testcases[$x], 2) . ", -". strval($points/$numOfTestCases) . "\n";
        }
    }
    //=========================================================================
    $info = array('username' => $username, 'password' => $password, 'user_test' => $user_test, 'question'=> $QID, 'points'=>$grade);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/update_user_test_question');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $send_back = curl_exec($ch);
    curl_close($ch);
    
    if($comment == " "){
        $comment = "Good Job :)";
    }
    
     $info = array('username' => $username, 'password' => $password, 'user_test' => $user_test, 'question'=> $QID, 'comment'=>$comment);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/update_user_test_question');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $send_back = curl_exec($ch);
    curl_close($ch);
//    echo $student_answer;
//    echo "\n";
//    echo $points; //  
    echo "\n";
    echo $comment; // save to update_user_test_question
//    echo "\n";
//    echo "user_test: " . $user_test;
//    echo "\n";
//    echo "insert points id: " . $insertPointstestcase;
//    echo "\n";
//    echo "total: " . $grade; // return this value
//    echo "\n";
    if($points < $grade)
    {
        return $points;
    }
    else{
        return $grade;
    }
    
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/get_user"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['ID'])){
            $ID = $_POST['ID'];
        }
        if(isset($_POST['user'])){
            $user = $_POST['user'];
        }
        if(isset($_POST['permission'])){
            $permission = $_POST['permission'];
        }
        $info = array('username' => $username, 'password' => $password, 'ID'=> $ID, 'user'=>$user, 'permission'=> $permission);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/get_user');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/create_test"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['name'])){
            $name = $_POST['name'];
        }
        $info = array('username' => $username, 'password' => $password, 'name'=> $name);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/create_test');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/get_test"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['ID'])){
            $ID = $_POST['ID'];
        }
        if(isset($_POST['name'])){
            $name = $_POST['name'];
        }
        $info = array('username' => $username, 'password' => $password, 'ID' => $ID, 'name' => $name);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/get_test');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/add_test_to_user"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['test'])){
            $test = $_POST['test'];
        }
        if(isset($_POST['user'])){
            $user = $_POST['user'];
        }
        $info = array('username' => $username, 'password' => $password, 'test' => $test, 'user' => $user);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/add_test_to_user');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/update_user_test"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['test'])){
            $test = $_POST['test'];
        }
        if(isset($_POST['user'])){
            $user = $_POST['user'];
        }
        if(isset($_POST['taken'])){
            $taken = $_POST['taken'];
        }
        
        $info = array('username' => $username, 'password' => $password, 'test' => $test, 'user' => $user, 'taken' => $taken);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/update_user_test');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/get_user_test"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['test'])){
            $test = $_POST['test'];
        }
        if(isset($_POST['user'])){
            $user = $_POST['user'];
        }
        if(isset($_POST['grade'])){
            $grade = $_POST['grade'];
        }
        if(isset($_POST['publish'])){
            $publish = $_POST['publish'];
        }
        if(isset($_POST['comment'])){
            $comment = $_POST['comment'];
        }
        if(isset($_POST['taken'])){
            $taken = $_POST['taken'];
        }
        $info = array('username' => $username, 'password' => $password, 'test' => $test, 'user' => $user, 'grade'=> $grade, 'publish'=> $publish, 'comment'=> $comment, 'taken'=> $taken);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/get_user_test');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
    }
}



if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/create_question"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['question'])){
            $question = $_POST['question'];
        }
        if(isset($_POST['difficulty'])){
            $difficulty = $_POST['difficulty'];
        }
        if(isset($_POST['testcases'])){
            $testcases = $_POST['testcases'];
        }
        if(isset($_POST['topic'])){
            $topic = $_POST['topic'];
        }
        if(isset($_POST['answers'])){
            $answers = $_POST['answers'];
        }
        if(isset($_POST['keywords'])){
            $keywords = $_POST['keywords'];
        }
        // add in keywords
       
        // create question
        $info = array('username' => $username, 'password' => $password, 'question' => $question, 'difficulty' => $difficulty, 'topic'=>$topic, 'keywords'=> $keywords);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/create_question');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
        $returned= json_decode($send_back, true);
        
        $temp = $returned['response']['questions'];
        foreach ($temp as $key => $value){
            $QID = $key;
        }
        
        // remove spaces
        $testcases = str_replace(' ', '', $testcases);
        $answers = str_replace(' ', '', $answers);
        $testcases = str_replace('\n', '', $testcases);
        $answers = str_replace('\n', '', $answers);
        // parse testcase and answer
        $testcase = explode("|", $testcases);
        $answer = explode(",", $answers);
        
        $testCaseIDS = array(); // holds test case ids the we just inserted
        // get question ID\
        print_r($testcase);
        for($x=0; $x<sizeof($testcase);$x++){
            // insert test case
            $info = array('username' => $username, 'password' => $password, 'test_case'=> $testcase[$x], 'answer'=> $answer[$x]);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/create_test_case');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $send_back = curl_exec($ch);
            curl_close($ch);
            echo $send_back;
            $re= json_decode($send_back, true);

            $tem = $re['response']['test_cases'];
            foreach ($tem as $key => $value){
                array_push($testCaseIDS, $key);
                
            }
        }
        //link test_case to question using 
        
        for($x=0; $x<sizeof($testCaseIDS);$x++){
            // insert test case
            $info = array('username' => $username, 'password' => $password, 'question'=>$QID, 'test_case'=> $testCaseIDS[$x]);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/add_test_case_to_question');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $send_back = curl_exec($ch);
            curl_close($ch);
            echo $send_back;  
        }
        
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/get_question"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['ID'])){
            $ID = $_POST['ID'];
        }
        
        $info = array('username' => $username, 'password' => $password,'ID'=>$ID);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/get_question');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
        
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/add_question_to_test"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['test'])){
            $test = $_POST['test'];
        }
        if(isset($_POST['question'])){
            $question = $_POST['question'];
        }
        if(isset($_POST['points'])){
            $points = $_POST['points'];
        }
        $info = array('username' => $username, 'password' => $password, 'test' => $test, 'question'=> $question, 'points' => $points);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/add_question_to_test');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
        
        
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/add_user_test_question"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['user_test'])){
            $user_test = $_POST['user_test'];
        }
        if(isset($_POST['question'])){
            $question = $_POST['question'];
        }
        if(isset($_POST['code'])){
            $code = $_POST['code'];
        }
        if(isset($_POST['comment'])){
            $comment = $_POST['comment'];
        }
        
        
        $info = array('username' => $username, 'password' => $password, 'user_test' => $user_test, 'question'=> $question, 'code'=> $code, 'comment'=> $comment);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/add_user_test_question');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/update_user_test_question"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['user_test'])){
            $user_test = $_POST['user_test'];
        }
        if(isset($_POST['question'])){
            $question = $_POST['question'];
        }
        if(isset($_POST['code'])){
            $code = $_POST['code'];
        }
        if(isset($_POST['comment'])){
            $comment = $_POST['comment'];
        }
        
        
        $info = array('username' => $username, 'password' => $password, 'user_test' => $user_test, 'question'=> $question, 'code'=> $code, 'comment'=> $comment);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/update_user_test_question');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/get_user_test_question"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['user_test'])){
            $user_test = $_POST['user_test'];
        }
        if(isset($_POST['question'])){
            $question = $_POST['question'];
        }
        if(isset($_POST['code'])){
            $code = $_POST['code'];
        }
        if(isset($_POST['comment'])){
            $comment = $_POST['comment'];
        }
        
        
        $info = array('username' => $username, 'password' => $password, 'user_test' => $user_test, 'question'=> $question, 'code'=> $code, 'comment'=> $comment);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/get_user_test_question');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
    }
}


if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/create_test_case"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['test_case'])){
            $test_case = $_POST['test_case'];
        }
        if(isset($_POST['answer'])){
            $answer = $_POST['answer'];
        }
        if(isset($_POST['points'])){
            $points = $_POST['points'];
        }
        
        $info = array('username' => $username, 'password' => $password, 'test_case' => $test_case, 'answer'=> $answer, 'points'=> $points);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/create_test_case');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back; 
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/get_test_case"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['user_test_question'])){
            $user_test_question = $_POST['user_test_question'];
        }
        if(isset($_POST['test_grade'])){
            $test_grade = $_POST['test_grade'];
        }
        if(isset($_POST['ID'])){
            $ID = $_POST['ID'];
        }
        
        $info = array('username' => $username, 'password' => $password, 'user_test_question' => $user_test_question, 'test_grade'=> $test_grade, 'ID'=> $ID);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/get_test_case');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back; 
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/add_test_case_to_question"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['user_test_question'])){
            $user_test_question = $_POST['user_test_question'];
        }
        if(isset($_POST['test_grade'])){
            $test_grade = $_POST['test_grade'];
        }
        
        $info = array('username' => $username, 'password' => $password, 'user_test_question' => $user_test_question, 'test_grade'=> $test_grade);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/add_test_case_to_question');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back; 
    }
}


if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/get_grades"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['test'])){
            $test = $_POST['test'];
        }
        if(isset($_POST['user'])){
            $user = $_POST['user'];
        }
        if(isset($_POST['grade'])){
            $grade = $_POST['grade'];
        }
        
        $info = array('username' => $username, 'password' => $password, 'test' => $test, 'user'=> $user);// , 'grade'=>$grade
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/get_grades');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back; // this data type is string
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/add_comment"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['user_test'])){
            $user_test = $_POST['user_test'];
        }
        if(isset($_POST['QID'])){
            $QID = $_POST['QID'];
        }
        if(isset($_POST['comment'])){
            $comment = $_POST['comment'];
        }
        
        $info = array('username' => $username, 'password' => $password, 'user_test' => $user_test, 'question'=> $QID, 'comment'=>$comment);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/update_user_test_question');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        echo $send_back;
    
        
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/get_user_test_question_test_case"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['user_test'])){
            $test = $_POST['user_test'];
        }
        if(isset($_POST['question'])){
            $QID = $_POST['question'];
        }

        $info = array('username' => $username, 'password' => $password, 'user_test' => $user_test, 'question'=>$QID);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/get_user_test_question');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
        
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/grade"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['student_responses'])){
            $student_responses = $_POST['student_responses'];
        }
        
        
        //$student_responses = rawurldecode($student_responses);
        $student_responses = json_decode($student_responses, true);
        //print_r($student_responses);
        //$student_responses = stripslashes($student_responses);
        //echo "info recived";
        //echo gettype($student_responses);
        //print_r($student_responses);
        //echo sizeof($student_responses);
        foreach($student_responses as &$value)//for($i=0; $i<sizeof($student_responses); $i++)
        {
            
            $testID = $student_responses[$value]['test'];
            $user = $student_responses[$value]['user'];
            $QID = $student_responses[$value]['QID'];
            $student_answer = $student_responses[$value]['studentanswer'];
            $testID = $value['test'];
            $user = $value['user'];
            $QID = $value['QID'];
            $student_answer = $value['studentanswer'];
           
            $student_answer = str_replace('~', '+', $student_answer);
           
            // get questions of exam from backend
            $info = array('username' => $username, 'password' => $password, 'ID'=> $testID);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/get_test');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $test_query = curl_exec($ch);
            curl_close($ch);
            //echo $test_query;
            $returned= json_decode($test_query, true);

            //get answers of questions from backend
            $answers = array();
            $points = $returned['tests'][$testID]['questions'][$QID]['points'];
            $keywords = $returned['tests'][$testID]['questions'][$QID]['keywords'];
            $testcaseArray = $returned['tests'][$testID]['questions'][$QID]['test_cases'];// testcases array

            $testcases = array();
            $testcaseIDs = array();
            $numOfTestCases = 0;
            foreach ($testcaseArray as $key => $value){
                array_push($testcaseIDs, $key);
                $testcase = str_replace("\n", "", $value['test_case']);
                array_push($testcases, $testcase);
                $numOfTestCases = $numOfTestCases+1;
                $answer = str_replace("\n", "", $value['answer']);
                array_push($answers, $answer);
            } 
            //  SEND BACKEND THE STUDENT ANSWER
            $info = array('username' => $username, 'password' => $password, 'ID' => $user );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/get_user');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $send_back = curl_exec($ch);
            curl_close($ch);
            $info= json_decode($send_back, true);
            foreach ($info as $key => $value){
                $user_name = $key;
            }
            $user_test = $info[$user_name]['test'][$testID]['ID'];
            $insertPointstestcase = $info[$user_name]['test'][$testID]['questions'][$QID]['ID'];

            $info = array('username' => $username, 'password' => $password, 'user_test'=> $user_test, 'question'=> $QID, 'code' => $student_answer);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/update_user_test_question');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $send_back = curl_exec($ch);

            $student_points = grade($username, $password, $testID, $user, $QID, $answers, $student_answer, $points, $testcases, $numOfTestCases, $keywords, $user_test, $testcaseIDs, $insertPointstestcase);

            // store returned value from grade function
            // get user_test to get grade
            $info = array('username' => $username, 'password' => $password, 'ID'=> $user_test);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/get_user_test');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $send_back = curl_exec($ch);
            $info_returned = json_decode($send_back, true);
            $grade = $info_returned[$user_test]['grade'];


            // += user grade with points returned from the grade function
            $grade = $grade + $student_points;

            $info = array('username' => $username, 'password' => $password, 'ID'=> $user_test, 'grade' => $grade);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/update_user_test');
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $send_back = curl_exec($ch);
            //echo "Grade updated";
        
        }// end of big for loop
        
    
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/update_grade"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['user_test'])){
            $user_test = $_POST['user_test'];
        }
        if(isset($_POST['grade'])){
            $grade = $_POST['grade'];
        }
        
        $info = array('username' => $username, 'password' => $password, 'ID'=> $user_test, 'grade' => $grade);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/update_user_test');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        echo "Grade updated ";
        echo $send_back;
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/update_question_grade"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['user_test'])){
            $user_test = $_POST['ID'];
        }
        if(isset($_POST['question'])){
            $QID = $_POST['question'];
        }
        if(isset($_POST['points'])){
            $grade = $_POST['points'];
        }
        
   $info = array('username' => $username, 'password' => $password, 'user_test' => $user_test, 'question'=> $QID, 'points'=>$grade);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/update_user_test_question');
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $send_back = curl_exec($ch);
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/publish_grade"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['test'])){
            $test = $_POST['test'];
        }
        if(isset($_POST['user'])){
            $user = $_POST['user'];
        }
        if(isset($_POST['publish'])){
            $publish = $_POST['publish'];
        }
        
        $info = array('username' => $username, 'password' => $password, 'test' => $test, 'user'=> $user, 'publish' => $publish);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/update_user_test');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        echo $send_back;
    }
}

if($_SERVER['REQUEST_URI'] == "/~np363/cs490/middle.php/reCalcGrade"){
    if($_SERVER['REQUEST_METHOD'] == "POST"){
        if(isset($_POST['username'])){
            $username = $_POST['username'];
        }
        if(isset($_POST['password'])){
            $password = $_POST['password'];
        }
        if(isset($_POST['user_test'])){
            $user_test = $_POST['user_test'];
        }
        if(isset($_POST['test'])){
            $test = $_POST['test'];
        }
        if(isset($_POST['user'])){
            $user = $_POST['user'];
        }
        $info = array('username' => $username, 'password' => $password, 'user_test' => $user_test);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/get_user_test_question');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);
        //echo $send_back;
        $returned= json_decode($send_back, true);

        // add all points then store in update_user_test
        $newExamGrade = 0; 

        foreach ($returned as $key => $value){
            $newExamGrade = $newExamGrade + intval($value['points']);
        } 

        $info = array('username' => $username, 'password' => $password, 'test'=> $test, 'user'=> $user, 'grade'=> $newExamGrade);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://web.njit.edu/~mhj5/API.php/update_user_test');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $info);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $send_back = curl_exec($ch);
        curl_close($ch);

    }
}
?>
