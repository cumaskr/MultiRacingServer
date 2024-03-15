# MultiRacing_Server
>Youtube URL : https://www.youtube.com/watch?v=VpqVrVNnLuA

안녕하세요.  
[Unity] 포트폴리오 용으로 제작한 멀티레이싱 게임 입니다.  
해당 프로젝트는 서버 파트입니다.

## 주요 스크립트 경로
>TCP 서버 - ./MultiRacingServer.java  
>HTTP 웹 서버의 비즈니스로직 - ./html/php 파일들
## 특이사항
취업 전, 웹페이지 만들어보고 난 후 응용하여   
어떻게하면 서버에서 데이터를 먼저보낼수있는지  
조사하며 만들어본 JAVA(TCP 서버)/ Apache(Http 웹서버) 프로젝트입니다.  
<hr/>
프로토콜 설계  

1)클라이언트 채팅방 생성/조인 시, 연결 설정된 TCP소켓 파일생성  
2)유저 당 1개의 쓰레드 생성 후, 유저의 소켓 관리 및 클라>서버 요청 처리  
3)방 단위로 유저 관리