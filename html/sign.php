<?php

// mysql 접속 호스트(인터넷에 연결 되어있는 컴퓨터) 설정
$host = "localhost";
// 사용자
$user = "서버운영종료";
// 사용자 비밀번호
$password = "서버운영종료";
// 데이터베이스 이름
$DB_name = "racing";
//mysql + php 연동 -> DB 연결
$mysqli = mysqli_connect($host, $user, $password, $DB_name);
//연결 안됬을시 로그 남기는 부분
if($mysqli == null) return mysqli_error($mysqli);



//클라이언트로 넘어온데이터 이때 넘어오는 데이터는 중복확인이냐? / 가입완료냐? 를 판단하는
$what = $_POST['what'];



//이메일 중복확인을 눌럿냐?
if(strcmp($what,'confirm_email') == 0)
{
    $email = $_POST['email'];

    $select_query ="SELECT * FROM member WHERE m_email ='$email'";
    $result = mysqli_query($mysqli,$select_query);
    $row = mysqli_fetch_assoc($result);

    //중복 된 이메일이없다면 회원가입 가능한 이메일이다.
    if($result->num_rows == 0)
    {
        echo "false";
    }
    //중복 되어있다면 이미 사용하고 있는 이메일이기때문에 다른 이메일 써야한다.
    else
    {
        echo "true";
    }
}
//닉네임 중복확인을 눌럿냐?
else if(strcmp($what,'confirm_nickname') == 0)
{
    $nickname = $_POST['nickname'];

    $select_query ="SELECT * FROM member WHERE m_nickname = '$nickname'";
    $result = mysqli_query($mysqli,$select_query);
    $row = mysqli_fetch_assoc($result);

    //중복 된 닉네임이없다면 false 반환.
    if($result->num_rows == 0)
    {
        echo "false";
    }
    //중복 되어있다면 이미 사용하고 있는 닉네임이기때문에 true 반환.
    else
    {
        echo "true";
    }
}
//회원가입을 눌럿냐?
else if(strcmp($what,'sign') == 0)
{
    $email = $_POST['email'];
    $pw = $_POST['pw'];
    $nickname = $_POST['nickname'];

    //회원 테이블에 유저 정보를 추가한다.
    $insert_memberquery = "INSERT INTO member(m_email,m_pw,m_nickname,m_money) VALUES('$email','$pw','$nickname', 0 )";
    $result_memberquery = mysqli_query($mysqli,$insert_memberquery);

    //회원가입을 하면 기본 자동차를 준다. 회원인벤토리에 기본차 하나를 추가한다.
    $insert_membercarquery = "INSERT INTO memberinventory(m_email,m_item,m_equip) VALUES('$email',1, 1)";
    $result_membercarquery = mysqli_query($mysqli,$insert_membercarquery);

    //회원가입을 하면 기본 바퀴를 준다. 회원인벤토리에 바퀴 하나를 추가한다.
    $insert_memberwheelquery = "INSERT INTO memberinventory(m_email,m_item,m_equip) VALUES('$email',3, 1)";
    $result_memberwheelquery = mysqli_query($mysqli,$insert_memberwheelquery);

    //회원가입을 하면 기본 바퀴를 준다. 회원인벤토리에 바퀴 하나를 추가한다.
    $insert_memberwingquery = "INSERT INTO memberinventory(m_email,m_item,m_equip) VALUES('$email',5, 1)";
    $result_memberwingquery = mysqli_query($mysqli,$insert_memberwingquery);


    //DB에 질의 날리기 그리고 질의 실패했는지 안했는지 검사
    if($result_memberquery == null || $result_membercarquery == null || $result_memberquery == false || $result_membercarquery == false)
    {
        //실패했다면 어떤 에러인지 출력
        echo mysqli_error($mysqli);
    }
    else
    {
        //성공했다면 true 반환
        echo "true";
    }
}


//mysql 연결 종료
mysqli_close($mysqli);

?>

