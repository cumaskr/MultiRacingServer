import java.io.BufferedReader;
import java.io.BufferedWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.OutputStream;
import java.io.OutputStreamWriter;
import java.net.ServerSocket;
import java.net.Socket;
import java.util.Collections;
import java.util.HashMap;
import java.util.LinkedHashMap;
import java.util.concurrent.atomic.AtomicInteger;

//============================메세지 종류============================
//NICKNAME 
//클라 = NICKNAME:유저닉네임/차AssetName/바퀴AssetName/날개AssetName     ☞ 멀티플레이를 누를때 메세지 [전송]
//서버 =                                                               ☞ 유저 생성
//------------------------------------------------------------------
//ROOMLISTSHOW(방목록 갱신) 
//클라 = ROOMLISTSHOW:                                                 ☞ 해당 화면으로 넘어가거나, 새로고침을 누르면 메세지 [전송]
//서버 = ROOMLISTSHOW:방ID/맵이름/방제목/현재인원/총인원 메세지[전송]     ☞ 방이 없을경우 ROOMLISTSHOW:[전송]
//------------------------------------------------------------------
//ROOMMAKE
//클라 = ROOMMAKE:맵이름/방제목/총인원                                   ☞ 방만들기를 누를때 메세지[전송]
//서버 = ROOMREFRESH:생략 / ROOMRJOIN:생략  /MSG:생략                    ☞ 방을 생성하고 메세지[전송]   
//------------------------------------------------------------------
//ROOMJOIN
//클라 = ROOMJOIN:방ID                                                  ☞ 방 리스트에서 클릭후 들어갈때 메세지[전송]
//서버 = ROOMREFRESH:생략 / ROOMRJOIN:생략  /MSG:생략                    ☞ 유저 방에 넣고 메세지[전송]
//------------------------------------------------------------------
//ROOMOUT
//클라 = ROOMOUT:                                                       ☞방에서 정상적으로 종료 했을때 메세지[전송]
//서버 = ROOMREFRESH:생략 / MSG:생략                                     ☞정상/비정상 종료 했을때 유저 방에서 빼고 메세지[전송]
//------------------------------------------------------------------
//ROOMREFRESH(채팅방 갱신)
//클라 = ROOMREFRESH:맵이름(방장전용)                                      ☞ 방장이 맵을 바꿀때만 메세지[전송]
//서버 = ROOMREFRESH:맵이름/방제목/현재인원/총인원                          ☞ 방생성하거나 방조인할때 서버에서 클라로 메세지[전송]
//------------------------------------------------------------------
//MSG                                                   
//클라 = MSG:채팅창에 친 글자                                               ☞ 채팅창에서 친 메세지[전송]
//서버 = MSG:닉네임 + 메세지                                                ☞ 모든 유저한테 메세지[전송]
//------------------------------------------------------------------
//UNITYJOIN
//클라 = 
//서버 = UNITYJOIN:방안의 유저들(닉네임,차,날개,바퀴)/유저인덱스/방장인덱스/총인원  ☞ 방안에 유저를 넣을때 클라로 메세지[전송]
//------------------------------------------------------------------                                                      
//UNITYOUT
//클라 = 
//서버 = UNITYOUT:유저닉네임/방장닉네임                                          ☞ 방안에서 유저를 뺄때 서버->클라로 메세지[전송]
//=================================================================
//ROOMKICK
//클라 = ROOMKICK:유저닉네임                                                    ☞ 방장이 유저강퇴 할때 클라->서버로 메세지[전송]
//서버 = ROOMKICK:유저닉네임                                                    ☞ 전체 클라들한테 그대로 메세지[전송]
//1.X자 버튼에 붙어있는 SC_ChattingRoom_Kick에서 클라 -> 서버 ROOMKICK:유저이름 메세지[전송]
//2. 서버에서 ROOMKICK을 전체 방 인원에 전송
//3. 클라에서 ROOMKICK메세지를 받고 나 자신라면 내가 나를 내보낸다.                        
//=================================================================                        
//UNITYSTATE
//클라 = UNITYSTATE:유저닉네임/READY
//서버 = UNITYSTATE:유저닉네임/READY
//UNITYSTATE
//클라 = UNITYSTATE:유저닉네임/START
//서버 = UNITYSTATE:유저닉네임/START                                          ☞ 방안에서 유저를 뺄때 서버->클라로 메세지[전송]
//=================================================================     
//GAMESTATE
//클라 = GAMESTATE:유저닉네임/START                                           ☞ 시네마틱 카메라 보고난후 클라->서버로 메세지[전송]
//서버 = GAMESTATE:START                                                     ☞ 클라에서 메세지가 왔을때 방안의 유저 START 체크를 하고 모두 START면 방안의 전체한테 전송                           
//=================================================================     
//GAMESYNCHRONIZE
//클라 = GAMESYNCHRONIZE:유저닉네임/X/Y/Z/X/Y/Z                               ☞ 클라에서 자신의 차의 움직임을 서버로 메세지[전송]
//서버 = GAMESYNCHRONIZE:유저닉네임/X/Y/Z/X/Y/Z                               ☞ 서버에서 해당 유저 닉네임을 제외한 유저한테 서버->클라 메세지[전송]
//=================================================================     
//GAMEFALL
//클라 = GAMEFALL:유저닉네임                                                  ☞ 클라에서 바다로 떨어진 자신의 닉네임을 서버로 메세지[전송]
//서버 = GAMEFALL:유저닉네임                                                  ☞ 서버에서 이 유저를 제외한 유저한테 메세지[전송]
//☞ 해당 메세지를 받은 클라는 그 유저의 BoxCollider를 잠시 꺼준다. 
//결론 : 바다 떨어졌을때 내 꺼만 BoxCollider끈다고 되는게아니다. 상대방한테 보여지는 나에게는 BoxCollider가 유지되어있다.

//=================================================================     
//GAMERANK
//클라 = GAMERANK:유저닉네임/m_spawnIndex                                     ☞ 클라에서 유저가 다음 가야할 인덱스 위치를 전송
//서버 = GAMEFALL:유저닉네임/m_spawnIndex                                     ☞ 서버에서 다른 모든 유저한테 해당 유저의 인덱스 위치 전송

//GAMEOVER
//클라 = GAMEOVER:유저닉네임/GAMEOVER                                           ☞ 결승점에 도착한후 클라->서버로 메세지[전송]
//서버 = GAMEOVER:유저닉네임/GAMEOVER                                           ☞ 결승점 도착한 유저를 제외한 유저에게 메세지 [전송]
//서버 = GAMEOVER:GAMEOVER                                                 ☞ 모든 유저가 도착했다(결승점도착 or 5초카운트다운)면 GAVEOVER을 전송후 게임을 종료 한다.

//채팅 및 게임 데이터 동기화 해주는 서버
public class MultiRacingServer {

    int m_count = 0;

    //방 번호(생성될때 마다 증가한다. 동기화 문제로 여러쓰레드에서 접근했을때 순차적으로 오르는것을 보장)
    AtomicInteger m_atomicInteger;

    //Key : 방ID, Value : 방
    HashMap<Integer, Room> m_roomList;

    //Key : 유저이름, Value : 유저 -> 서버에 접속해 있는 전체 유저(전체 대기방)
    HashMap<String, User> m_userAllList;

    public static void main(String args[]) {
        //채팅서버 시작
        new MultiRacingServer().start();
    }

    //방
    class Room
    {
        //방 ID
        public Integer m_id;
        //맵 번호
        public String m_roomMap;   
        //방 제목
        public String m_roomName;       
        //전체 인원
        public String m_roomAllCount;       
        //방장
        public User m_master;        
        //Key : 닉네임 Value : 유저
        LinkedHashMap<String, User> m_userList;    

        //방ID, 맵이름, 방제목, 현재인원, 총인원, 방장
        public Room(Integer _roomID ,String _roomMap ,String _roomName, String _roomAllCount , User _master)
        {   
            m_id = _roomID;
            m_roomMap = _roomMap;     
            m_roomName = _roomName;        
            m_roomAllCount = _roomAllCount;        
            m_master = _master;
            m_userList = new LinkedHashMap<String, User>();
            Collections.synchronizedMap(m_userList);                   
        }

        public void JoinToRoom(String _nickname,User _user) throws IOException
        {
            //유저정보 초기화 m_isReady는 서버에서 관리하는 변수이다.
            _user.m_isReady = false;

            m_userList.put(_nickname,_user);     
        
            //여기서 UNITYJOIN
            int _index = 0;
            int _joinUserIndex = -1;
            int _masterIndex = -1;
            String _userInfo = "";
            
            for(String key : m_userList.keySet())
            {   
                User _tmpUser = m_userList.get(key);                 
                
                //방에 들어가려는 유저의 인덱스
                if(_tmpUser.m_nickName == _nickname)
                {
                    _joinUserIndex = _index;
                }
                //방장 인덱스
                if(_tmpUser.m_nickName == m_master.m_nickName)
                {
                    _masterIndex = _index;
                }
                _userInfo += _tmpUser.m_nickName + "," + _tmpUser.m_CarAssetName  + "," + _tmpUser.m_WheelAssetName + "," + _tmpUser.m_WingAssetName + "/";

                _index++;
            }
            
            String _unityJoin = _userInfo + _joinUserIndex + "/" + _masterIndex + "/" + m_roomAllCount;

            SendToAll("UNITYJOIN:"+_unityJoin);        
        }

        public void OutToRoom(String _nickname) throws IOException
        {                
            //방안에 유저를 제거한다.
            m_userList.remove(_nickname);     

            //여기서 UNITYOUT
            String _unityout = _nickname + "/" + m_master.m_nickName;

            SendToAll("UNITYOUT:"+_unityout);        
        }
    
        public Integer GetRoomUserCount()
        {
            return m_userList.size();
        }

        public void SendToAll(String _msg) throws IOException
        {        
            for(String key : this.m_userList.keySet())
            {                
                User _user = this.m_userList.get(key);        
                _user.bw.write(_msg+"\n");
                _user.bw.flush();                     
            }
        }

        //_exceptUserNickName 유저 외에 유저한테 메세지 전송
        public void SendToAnother(String _msg,String _exceptUserNickName) throws IOException
        {        
            for(String key : this.m_userList.keySet())
            {
                if(key.equals(_exceptUserNickName))
                {

                }
                else
                {
                    User _user = this.m_userList.get(key);        
                    _user.bw.write(_msg+"\n");
                    _user.bw.flush();                     
                }                        
            }
        }

        public void ChangeMaster() throws IOException
        {
            for(String _userNickname : this.m_userList.keySet())
            {  
                //돌다가 현재 방장이 아니라면 위임한다.
                if(_userNickname != m_master.m_nickName)
                {                
                    User _user = this.m_userList.get(_userNickname); 
                    m_master = _user;                
                
                    //테스트 코드
                    // _user.bw.write("MSG:< 방장이 되셨습니다. >"+"\n");
                    // _user.bw.flush();
                    break;                     
                }                               
            }
        }
    }

    //유저
    class User
    {
        //유저 이름
        String m_nickName;
        //유저한테 보내기만 하면된다.
        BufferedWriter bw;        
        //유저 차량 AssetName
        String m_CarAssetName;
        //유저 바퀴 AssetName
        String m_WheelAssetName;
        //유저 날개 AssetName
        String m_WingAssetName;
        //게임방준비
        boolean m_isReady;
        //게임준비
        boolean m_gameStart;
        //게임종료
        boolean m_isOver;

        public User(String _nickName,BufferedWriter _bufferedWriter,String _CarAssetName,String _WheelAssetName,String _WingAssetName)
        {
            m_nickName = _nickName;
            bw = _bufferedWriter;
            m_CarAssetName = _CarAssetName;
            m_WheelAssetName = _WheelAssetName;
            m_WingAssetName = _WingAssetName;
            m_isReady = false;
            m_gameStart = false;
            m_isOver = false;
        }
    }

    // 생성자
    Server_Racing() {    
        m_roomList = new  HashMap<Integer, Room>();
        m_userAllList = new HashMap<String, User>();

        Collections.synchronizedMap(m_roomList);
        Collections.synchronizedMap(m_userAllList);

        m_atomicInteger = new AtomicInteger();

        //여기는 1초마다 데이터가 얼마나 들어오는지 확인하기 위한 코드
        // TimerTask task = new TimerTask(){
        //     @Override
        //     public void run() {
        //         m_count=0;
        //         System.out.println("==================================================================");
                
        //     }
        // };
        //new Timer().schedule(task, 0l, 1000l);
    }

    public void start() {
        // 서버소켓 선언
        ServerSocket serverSocket = null;
        // 서버 소켓 선언
        Socket socket = null;
        try {
            // 서버소켓 생성 - 8888번포트 사용
            serverSocket = new ServerSocket(8888);
            System.out.println("서버가 생성되었습니다.");
            while (true) {
                // 클라이언트를 받는 서버 소캣 생성
                socket = serverSocket.accept();
                System.out.println("[" + socket.getInetAddress() + ":" + socket.getPort() + "]" + "에서 접속하였습니다.");
                // 서버에서 메세지를 받는 쓰레드 생성(클라이언트 소켓 넘겨준다)
                // 클라이언트당 ServerReceiver 쓰레드가 1개씩 생성된다.
                ServerReceiver thread = new ServerReceiver(socket);
                // 쓰레드시작
                thread.start();                              
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
    
    class ServerReceiver extends Thread {                
    	//서버 <-> 클라이언트 연결 소켓
        Socket m_socket;
        //유저 닉네임
        String _nickname = "";
        //유저(해당 클라이언트 유저)
        User m_user;        
        //유저가 들어가있는 방ID
        Integer m_roomID;
        //데이터 스트림
        InputStream in;                        
        OutputStream out;                
        BufferedReader br;
        BufferedWriter bw;
        
        //생성자
        ServerReceiver(Socket _socket) {        	
            this.m_socket = _socket;            
            this.m_user = null;
            this.m_roomID = null;
            try {
            	//클라이언트 -> 서버 통로
                in = _socket.getInputStream();
                //서버 -> 클라이언트 통로
                out = _socket.getOutputStream();                              
                br = new BufferedReader(new InputStreamReader(in));
                bw = new BufferedWriter(new OutputStreamWriter(out));
            } catch (IOException e) {
            }
        }

        //ServerReceiver쓰레드의 메인기능은 클라이언트에서 오는 메세지를 수신하고 그 메세지 종류에 따라 일처리 진행
        public void run() {
            try {
                String _msg = "";
                while ((_msg = br.readLine()) != null) {
                    //_arrMSG[0] 메세지 타입  _arrMSG[1] 데이터
                    String[] _arrMSG = _msg.split(":");
                    switch (_arrMSG[0]) {

                        case "NICKNAME":

                            //NICKNAME:유저닉네임/차AssetName/바퀴AssetName/날개AssetName
                            String[] _userInfo  = _arrMSG[1].split("/");

                                //닉네임 변수에 클라이언트에서 받아온 닉네임을 넣어준다.
                                _nickname = _userInfo[0];
                                //유저객체를 생성한다. 유저(닉네임,출력스트림)
                                m_user = new User(_nickname,bw,_userInfo[1],_userInfo[2],_userInfo[3]);
                                //총 유저 목록에 넣어준다.
                                m_userAllList.put(_nickname, m_user);                                        
                                //============================서버 로그============================
                                System.out.println("현재 서버접속자 수는 " + m_userAllList.size() + "입니다.");   
                                //=================================================================
                        break;
                        
                        case "GAMESYNCHRONIZE":

                            //GAMESYNCHRONIZE:유저닉네임/Position/Rotation
                            String[] _gameSynchroUserInfo  = _arrMSG[1].split("/");                                
                            
                            //채팅방 가져온다.
                            Room _gameSynchroRoom =  m_roomList.get(m_roomID);

                            //나 자신 빼고 전송
                            _gameSynchroRoom.SendToAnother("GAMESYNCHRONIZE:"+_arrMSG[1], _gameSynchroUserInfo[0]);
                        break;
                        
                        case "GAMEFALL":

                            // GAMEFALL:유저닉네임                                                                                                                               
                            //채팅방 가져온다.
                            Room _gameFallRoom =  m_roomList.get(m_roomID);

                            //나 자신 빼고 전송
                            _gameFallRoom.SendToAnother("GAMEFALL:"+_arrMSG[1], m_user.m_nickName);
                            
                        break;

                        case "GAMEOVER":

                            //GAMEOVER:유저닉네임/OVER
                            String[] _gameOver  = _arrMSG[1].split("/");                                
                            
                            //채팅방 가져온다.
                            Room _gameOverRoom =  m_roomList.get(m_roomID);

                            //해당 유저는 서버에서 판별하는 종료를 설정한다.
                            m_user.m_isOver = true;

                            //모든 유저가 종료인지 판별하는 알고리즘
                            boolean _isGameOver = true;

                            for(String _userNickname : _gameOverRoom.m_userList.keySet())
                            {  
                                User _user = _gameOverRoom.m_userList.get(_userNickname); 
                                
                                if(_user.m_isOver == false)
                                {
                                    _isGameOver = false;
                                    break;
                                }                                                    
                            }

                            //모든 유저가 게임을 종료했다면(완주했거단 5초 카운트가 지난후 강제종료)
                            if(_isGameOver)
                            {
                                //종료 메세지를 보낸다.
                                _gameOverRoom.SendToAll("GAMEOVER:GAMEOVER");
                            }
                            //모든 유저가 종료가아니라면(아직 5초 카운트 다운이 안끝난 상황)
                            else
                            {
                                //나 자신 빼고 전송
                                _gameOverRoom.SendToAnother("GAMEOVER:"+_arrMSG[1], _gameOver[0]);
                            }
                            
                            break;


                            case "GAMERANK":

                            // GAMERANK:유저닉네임/m_spawnIndex                                                                                                                               
                            //채팅방 가져온다.
                            Room _gameRankRoom =  m_roomList.get(m_roomID);

                            //나 자신 빼고 전송
                            _gameRankRoom.SendToAnother("GAMERANK:"+_arrMSG[1], m_user.m_nickName);
                                                                
                            break;

                            case "GAMESTATE":

                            //GAMESTATE:유저닉네임/START
                            String[] _gameUserInfo  = _arrMSG[1].split("/");                                
                            //시작을 유저가 보냈다면
                            if(_gameUserInfo[1].equals("START"))
                            {
                                //채팅방 가져온다.
                                Room _gameRoom =  m_roomList.get(m_roomID); 
                                                                        
                                _gameRoom.m_userList.get(_gameUserInfo[0]).m_gameStart = true;

                                boolean _isGameStart = true;
                                for(String _userNickname : _gameRoom.m_userList.keySet())
                                {  
                                    User _user = _gameRoom.m_userList.get(_userNickname); 
                                    //방장을 제외한 모든 유저 래디 체크
                                    if(_user.m_gameStart == false)
                                    {
                                        _isGameStart = false;
                                        break;
                                    }                                                    
                                }

                                //모든 유저가 시네마틱장면이 끝났다면
                                if(_isGameStart)
                                {
                                    //게임 시작 하는 메세지를 전송한다.
                                    _gameRoom.SendToAll("GAMESTATE:START");
                                }
                                //모든 유저가 래디를 하지 않았다면
                                else
                                {
                                    //아직 시네마틱 카메라가 끝나지 않은 유저가 있다.
                                    //아무것도 하지않는다.
                                }
                        }                                                                    
                        break;

                        case "UNITYSTATE":

                            //UNITYSTATE:유저닉네임/READY, UNITYSTATE:유저닉네임/START
                            String[] _stateInfo  = _arrMSG[1].split("/");
                            
                            if(_stateInfo[1].equals("READY"))
                            {                                                                                   
                                //채팅방 가져온다.
                                Room _stateRoom =  m_roomList.get(m_roomID); 
                                                                    
                                //준비를 한 유저의 래디값을 바꾸어준다.
                                //만약 준비중이였다가 준비를 또 누르면 취소한다.
                                if(_stateRoom.m_userList.get(_stateInfo[0]).m_isReady == true)
                                {
                                    _stateRoom.m_userList.get(_stateInfo[0]).m_isReady = false;
                                }
                                //만약 준비중이 아니였다가 준비를 또 누르면 준비를 한다.
                                else
                                {
                                    _stateRoom.m_userList.get(_stateInfo[0]).m_isReady = true;                        
                                }
                                                                        
                                //유니티 표시를 위해 전체 메세지를 날린다.
                                _stateRoom.SendToAll("UNITYSTATE:"+_stateInfo[0] + "/" + _stateInfo[1]);
                            }
                            else if(_stateInfo[1].equals("START"))
                            {                                                                                   
                                //채팅방 가져온다.
                                Room _stateRoom =  m_roomList.get(m_roomID); 
                                
                                //방장 혼자 있다면
                                if(_stateRoom.m_userList.size() == 1)
                                {
                                    //시작 할수없다는 안내메세지를 날린다.
                                    _stateRoom.SendToAll("UNITYSTATE:인원이 부족합니다.");
                                }
                                //2명이상이라면
                                else
                                {
                                    boolean _isStart = true;
                                    for(String _userNickname : _stateRoom.m_userList.keySet())
                                    {  
                                        User _user = _stateRoom.m_userList.get(_userNickname); 
                                        //방장을 제외한 모든 유저 래디 체크
                                        if(_user.m_nickName.equals(_stateRoom.m_master.m_nickName) == false  && _user.m_isReady == false)
                                        {
                                            _isStart = false;
                                            break;
                                        }                                                    
                                    }
                                    //모든 유저가 래디를 했다면
                                    if(_isStart)
                                    {
                                        //시작 하는 메세지를 전송한다.
                                        _stateRoom.SendToAll("UNITYSTATE:" + _stateInfo[0] + "/" + _stateInfo[1]);
                                    }
                                    //모든 유저가 래디를 하지 않았다면
                                    else
                                    {
                                        //시작 할수없다는 안내메세지를 날린다.
                                        _stateRoom.SendToAll("UNITYSTATE:준비를 안한 유저가 있습니다.");
                                    }
                                }
                            }
                        break;

                        //방장이 맵을 변경하고난후 보낸다.
                        case "ROOMREFRESH":   
                            Room _MapChangeRoom =  m_roomList.get(m_roomID);
                            _MapChangeRoom.m_roomMap = _arrMSG[1];
                            _MapChangeRoom.SendToAll("ROOMREFRESH:"+_MapChangeRoom.m_roomMap+"/"+_MapChangeRoom.m_roomName+"/"+_MapChangeRoom.GetRoomUserCount()+"/"+_MapChangeRoom.m_roomAllCount);
                        break;


                        case "ROOMMAKE":      
                        
                            int _roomID = m_atomicInteger.incrementAndGet();                            
                            //Room(맵이름,방제목,총인원) 이 넘어온다.
                            String[] _roomInfo  = _arrMSG[1].split("/");
                            //해당 정보로 방생성
                            Room _newRoom = new Room(_roomID, _roomInfo[0], _roomInfo[1], _roomInfo[2], m_user);                            
                            //방ID / Room(방ID,맵이름,방제목,총인원,방장)
                            m_roomList.put(_roomID, _newRoom);
                            //방장도 방안의 유저목록에 넣는다.
                            _newRoom.JoinToRoom(_nickname, m_user);
                            m_roomID = _newRoom.m_id;                                                                   
                            //방갱신(맵이름/방제목/현재인원/총인원))
                            _newRoom.SendToAll("ROOMREFRESH:"+_newRoom.m_roomMap+"/"+_newRoom.m_roomName+"/"+_newRoom.GetRoomUserCount()+"/"+_newRoom.m_roomAllCount);
                            //방생성 메세지
                            _newRoom.SendToAll("MSG:"+"< "+ _nickname + "님이 " + _newRoom.m_roomName + "방을 생성 하였습니다. >");
                            _newRoom.SendToAll("ROOMJOIN:");
                            break;

                        case "ROOMKICK":  

                            Room _kickRoom =  m_roomList.get(m_roomID);
                            //ROOMKICK:유저닉네임
                            _kickRoom.SendToAll("ROOMKICK:"+_arrMSG[1]);
                            break;

                        case "ROOMOUT":      

                            Room _outRoom =  m_roomList.get(m_roomID);
                            if(_outRoom != null)
                            {
                                //방장이면 방장을 넘겨준다.
                                if(_outRoom.m_master.m_nickName == m_user.m_nickName)
                                {
                                    //만약에 방안에 혼자라면 즉, 자기자신만있다면 방을 삭제해야한다.
                                    if(_outRoom.GetRoomUserCount() == 1)
                                    {               
                                        //방안에 유저를 제거한다.
                                        _outRoom.OutToRoom(_nickname);     
                                        
                                        //방을 제거한다.
                                        m_roomList.remove(_outRoom.m_id);
                                        _outRoom = null;
                                    }
                                    //혼자가 아니라면 방장을 위임한다. 가장 오래된 사람한테 간다.
                                    else
                                    {
                                        //방장을위임한다.
                                        _outRoom.ChangeMaster();
                                    }
                                }                                          
                            }
                            
                            if(_outRoom != null)
                            {
                                //방안에 유저를 제거한다.
                                _outRoom.OutToRoom(_nickname);       
                                                                                                        
                                //방갱신(맵이름/방제목/현재인원/총인원))
                                //_outRoom.SendToAll("ROOMREFRESH:"+_outRoom.m_roomMap+"/"+_outRoom.m_roomName+"/"+_outRoom.GetRoomUserCount()+"/"+_outRoom.m_roomAllCount);
                                
                                _outRoom.SendToAll("MSG:< "+ _nickname + "님이 방에서 나갔습니다. >");

                                    //방갱신(맵이름/방제목/현재인원/총인원))
                                _outRoom.SendToAll("ROOMREFRESH:"+_outRoom.m_roomMap+"/"+_outRoom.m_roomName+"/"+_outRoom.GetRoomUserCount()+"/"+_outRoom.m_roomAllCount);
                            }

                            //유저가 들어간 방 변수를 NULL로 변경
                            m_roomID = null;
                            
                            break;   

                        case "ROOMJOIN":                                    

                            Room _joinroom = m_roomList.get(Integer.parseInt(_arrMSG[1]));
                            //해당 방이 존재 하는 경우
                            if(_joinroom != null)
                            {
                                //인원 제한 체크(4/4)인경우에 들어가면 인원초과이므로 안내메세지를 돌려준다.
                                if(_joinroom.GetRoomUserCount() + 1 > Integer.parseInt(_joinroom.m_roomAllCount))
                                {
                                    m_user.bw.write("ROOMJOIN:인원수가 초과 하였습니다." + "\n");
                                    m_user.bw.flush();                                                   
                                }
                                //그 외에는 들어가도 된다.
                                else
                                {
                                    _joinroom.JoinToRoom(_nickname, m_user);
                                    m_roomID = _joinroom.m_id;                                                                                                                                  
                                    //방갱신(맵이름/방제목/현재인원/총인원))
                                    _joinroom.SendToAll("ROOMREFRESH:"+_joinroom.m_roomMap+"/"+_joinroom.m_roomName+"/"+_joinroom.GetRoomUserCount()+"/"+_joinroom.m_roomAllCount);
                                    _joinroom.SendToAll("ROOMJOIN:");
                                    _joinroom.SendToAll("MSG:< "+ _nickname + "님이 방에 들어왔습니다. >");
                                }                                            
                            }
                            //해당 방이 존재하지않는경우 유저가 새로고침 하기전에 방이 사라진경우             
                            else
                            {
                                m_user.bw.write("ROOMJOIN:방이 존재하지 않습니다." + "\n");
                                m_user.bw.flush();                                               
                            }                   
                            break;                            

                        case "ROOMLISTSHOW":        
                            //생성되어있는방이없을경우 클라이언트에 방이없다는것을 알려 방이없습니다를 출력하게한다.
                            if(m_roomList.isEmpty())
                            {
                                m_user.bw.write("ROOMLISTSHOW:" + "\n");
                                m_user.bw.flush();   
                            }
                            //생성되어있는 방이 있을경우
                            else
                            {
                                for(Integer _roomIndex : m_roomList.keySet())
                                {                                                
                                    Room _room = m_roomList.get(_roomIndex);   

                                    //방ID,맵이름,방제목,현재인원,총인원
                                        String _roomInfoMSG = "";                                                 
                                        _roomInfoMSG += "ROOMLISTSHOW:";
                                        _roomInfoMSG += (_room.m_id + "/");
                                        _roomInfoMSG += (_room.m_roomMap + "/");
                                        _roomInfoMSG += (_room.m_roomName + "/" );
                                        _roomInfoMSG += (_room.GetRoomUserCount() + "/");
                                        _roomInfoMSG += _room.m_roomAllCount;

                                        m_user.bw.write(_roomInfoMSG + "\n");
                                        m_user.bw.flush();                     
                                }                                                 
                            }
                                                                                                        
                            break;                            

                        case "MSG":                 
                                                        
                            Room _room = m_roomList.get(m_roomID);

                            if(_room != null)
                            {                                    
                                _room.SendToAll("MSG:[ "+ _nickname  + " ] " + _arrMSG[1]);
                            }
                            else
                            {
                                System.out.println("ErrorMSG[메세지를 보낼 방이 없음] : "+_msg);
                            }
                            
                            break;

                        //그 외에는 잘못 온 메세지이다. 서버에 출력하자.    
                        default:
                            System.out.println("ErrorMSG[메세지타입 판별불가] : "+_msg);
                            break;
                    }
                }                    
            } 
            catch (IOException e) { e.printStackTrace(); }
            finally 
            {                                                                                                                             
                //정상적으로 방에서 나간경우가 아니다. 나가기버튼을 누르고 나간것이 아니라면 ex) 인터넷이 끈켰던가 에디터 종료
                if(m_roomID != null)
                {                                                           
                    Room _outRoom =  m_roomList.get(m_roomID);                                                                
                    //방장이면 방장을 넘겨준다.
                    if(_outRoom.m_master.m_nickName == m_user.m_nickName)
                    {
                        //만약에 방안에 혼자라면 즉, 자기자신만있다면 방을 삭제해야한다.
                        if(_outRoom.GetRoomUserCount() == 1)
                        {                
                            //방안에 유저를 제거한다.
                            try {
                                _outRoom.OutToRoom(_nickname);
                            } 
                            catch (IOException e) {                                
                                e.printStackTrace();
                            }
                            //방을 제거한다.
                            m_roomList.remove(_outRoom.m_id);
                            _outRoom = null;
                        }
                        //혼자가 아니라면 방장을 위임한다. 가장 오래된 사람한테 간다.
                        else
                        {
                            try {
                                _outRoom.ChangeMaster();
                            } 
                            catch (IOException e) { e.printStackTrace(); }
                        }
                    }                                
                    if(_outRoom != null)
                    {
                        //방안에 유저를 제거한다.
                        try {
                            _outRoom.OutToRoom(_nickname);

                            _outRoom.SendToAll("ROOMREFRESH:" + _outRoom.m_roomMap + "/" + _outRoom.m_roomName + "/"+ _outRoom.GetRoomUserCount() + "/" + _outRoom.m_roomAllCount);                                    

                            _outRoom.SendToAll("MSG:< "+ _nickname + "님이 방에서 나갔습니다. >");

                            //방갱신(맵이름/방제목/현재인원/총인원))
                            _outRoom.SendToAll("ROOMREFRESH:"+_outRoom.m_roomMap+"/"+_outRoom.m_roomName+"/"+_outRoom.GetRoomUserCount()+"/"+_outRoom.m_roomAllCount);
                        } catch (IOException e) { e.printStackTrace(); }
                    }
                    //유저가 들어간 방 변수를 NULL로 변경
                    m_roomID = null;
                }
                //======================================총 유저목록에서 나가려는 유저 제거======================================
                m_userAllList.remove(_nickname);
                //============================서버 로그============================
                System.out.println("[" + m_socket.getInetAddress() + ":"+ m_socket.getPort() + "]" + "에서 접속을 종료하였습니다.");
                System.out.println("현재 서버접속자 수는 " + m_userAllList.size() + "입니다.");
                //자원 해제
                try {
                    in.close();
                    out.close();
                    m_socket.close();
                }
                catch (IOException e) { e.printStackTrace(); }
            }
        }
    }
}