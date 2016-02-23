#!/usr/bin/python -u
# Server side
import cgi
import socket
import time
import threading
import random
import os
import re
import urllib2
import sys
import sqlite3
import select
import struct

AUDIOMSG = 1
CHATMSG = 2 
DRUMMSG = 3
CLOCKSYNCMSG = 4 
CONFIGMSG = 5 
HELLOMSG = 6
SETDELAY = 7

ACCEPTED = 1
WRONGNAME = 2
WRONGPWD = 3
WRONGSSC = 4
WRONGSTATE = 5
FATAL = 6

DELAYTIME = 8000
BEATSPERCYCLE = 16
BEATPERIOD = 500

TIMEOFFSET = time.time()

#message type
LOGIN = 1
LOGOUT = 2
ROUNDTRIP = 3

ALL = 1
ADMINS = 2

# Address and time info
addre = ''
HOST = ''
SERVERPORT = 8004

UserNumber = 1

#List to keep track of socket descriptors
CONNECTION_LIST = []
SOCKET_USERNAME = {}
SOCKET_ADMINNAME = {}
RECV_BUFFER = 4096
SockData = ''
IncomingData = {}


def Location(SERVERPORT):
    SERVERPORT = SERVERPORT
    #get the external ip of DrumServer
    externalip = re.search('\d+\.\d+\.\d+\.\d+',urllib2.urlopen("http://www.whereismyip.com").read()).group(0)
    EXTERNALIP = str(externalip)
    currtime=time.strftime("%Y-%m-%d %X", time.localtime())
    sessioncode=random.randint(0,65535)
    #value = [EXTERNALIP, str(SERVERPORT),str(currtime),sessioncode]
    value = ['192.168.1.104', str(SERVERPORT),str(currtime),sessioncode]#the IP address should be the address of the server in CMU
    return value


def trans(Type, IP, Port, Time):
    sys.stdout.write('data: %d %s %s %s \r\n\r\n' %(Type, IP, Port, Time))
    sys.stdout.flush()

def send_data(sock, message):
    try:
        sock.sendall(message)
    except:
        if sock in CONNECTION_LIST:
            CONNECTION_LIST.remove(sock)
        if SOCKET_USERNAME.pop(sock, None) == None:
            SOCKET_ADMINNAME.pop(sock, None)
        IncomingData.pop(sock.fileno(), None)
        socket.close()


#Function to broadcast chat messages to all connected clients
def broadcast_data(sock, message, type):
    if type == ADMINS:
        #Do not send the message to master socket and the client who has send us the message
        for socket in CONNECTION_LIST:
            if socket != server_socket and socket in SOCKET_ADMINNAME:
                try :
                    send_data(socket, message)
                    print "Sent"
                except :
                    # broken socket connection may be, chat client pressed ctrl+c for example
                    if sock in CONNECTION_LIST:
                        CONNECTION_LIST.remove(sock)
                    if SOCKET_USERNAME.pop(sock, None) == None:
                        SOCKET_ADMINNAME.pop(sock, None)
                    IncomingData.pop(sock.fileno(), None)
                    socket.close()

    elif type == ALL:
        #Do not send the message to master socket and the client who has send us the message
        for socket in CONNECTION_LIST:
            if socket != server_socket:
                try :
                    send_data(socket, message)
                except :
                    # broken socket connection may be, chat client pressed ctrl+c for example
                    if sock in CONNECTION_LIST:
                        CONNECTION_LIST.remove(sock)
                    if SOCKET_USERNAME.pop(sock, None) == None:
                        SOCKET_ADMINNAME.pop(sock, None)
                    IncomingData.pop(sock.fileno(), None)
                    socket.close()


# IncomingData saves bytes as they arrive for each connection
# this is a dictionary that maps the connection, represented by
# the socket file number (sock.fileno()) - a small unique integer
# the connection is mapped to a byte array, which is everything
# that has been received so far, but not yet processed because
# we are waiting for more data. Also, if we get more than one
# message to process, the saved byte array will contain the
# remaining unprocessed bytes

def get_data_for_connection(n):
    return IncomingData.get(n, '')

def put_data_for_connection(n, data):
    IncomingData[n] = data


#Server
ServerLocation = Location(SERVERPORT)

server_socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
server_socket.bind((HOST, SERVERPORT))
server_socket.listen(64)


#write into database, they are supposed to be read by clients
con = sqlite3.connect('/Users/hamasakiyijun/Sites/user_admin.db') #this should be the excact path of the database

cur = con.cursor()    
cur.execute('INSERT INTO serverlocation(id, ip, port, time, colomn) VALUES(NULL, ?, ?, ?, ?)', ServerLocation)
con.commit()
sys.stdout.write('Content-Type: text/event-stream\r\n\r\n')


#Add server socket to the list of readable connections
CONNECTION_LIST.append(server_socket)


while True :
    # accept and establish connection, get the current time
    # Get the list sockets which are ready to be read through select
    print "Still Running"
    read_sockets,write_sockets,error_sockets = select.select(CONNECTION_LIST,[],[])
    print read_sockets

    for sock in read_sockets:
        #New connection
        if sock == server_socket:
            # Handle the case in which there is a new connection recieved through server_socket
            conn, addr = server_socket.accept()
            print 1
            CONNECTION_LIST.append(conn)
            put_data_for_connection(conn.fileno(), '')
            
            cIP = str(addr[0])
            cPort = str(addr[1])
            cTime = time.strftime("%Y-%m-%d %X", time.localtime())
            trans(LOGIN, cIP, cPort, 'NULL')
            print read_sockets, CONNECTION_LIST
            
        #Some incoming message from a client
        else:
            # Data received from client, process it
            try:
                #In Windows, sometimes when a TCP program closes abruptly,
                # a "Connection reset by peer" exception will be thrown                
                SockData = sock.recv(RECV_BUFFER)
                if SockData:
                    current = get_data_for_connection(sock.fileno())
                    current = current + SockData
                    put_data_for_connection(sock.fileno(), current)

                else:
                    # remove the socket that's broken    
                    if sock in CONNECTION_LIST:
                        CONNECTION_LIST.remove(sock)
                    if SOCKET_USERNAME.pop(sock, None) == None:
                        SOCKET_ADMINNAME.pop(sock, None)
                    IncomingData.pop(sock.fileno(), None)
                    addr = sock.getpeername()
                    sock.close()
                    cIP = str(addr[0])
                    cPort = str(addr[1])
                    cTime=time.strftime("%Y-%m-%d %X", time.localtime())
                    trans(LOGOUT, cIP, cPort, cTime)

                    # at this stage, no data means probably the connection has been broken
                   

            except:
                addr = sock.getpeername()
                cIP = str(addr[0])
                cPort = str(addr[1])
                cTime=time.strftime("%Y-%m-%d %X", time.localtime())
                trans(LOGOUT, cIP, cPort, 'NULL')
                if sock in CONNECTION_LIST:
                    CONNECTION_LIST.remove(sock)
                if SOCKET_USERNAME.pop(sock, None) == None:
                    SOCKET_ADMINNAME.pop(sock, None)
                IncomingData.pop(sock.fileno(), None)
                sock.close()
                continue

    for sock in CONNECTION_LIST:
        if sock != server_socket:
            addr = sock.getpeername()
            cIP = str(addr[0])
            cPort = str(addr[1])
            while True:
                current = get_data_for_connection(sock.fileno())
                if len(current) < 1:
                    break
                data = current[0]
                MsgType = struct.unpack('!B',data)
                if MsgType[0] == HELLOMSG:
                    print 'buffer length', len(IncomingData[sock.fileno()])
                    if len(current) < 5:
                        break
                    data = current[1 : 5]
                    length = struct.unpack('!I',data)
                    if len(current) < length[0] + 5:
                        break
                    data = current[5 : length[0] + 5]
                    SecretSession = struct.unpack('!I',data[0 : 4])
                    UserInfo = data[4 : ].split('#')
                    Username = UserInfo[0]
                    Password = UserInfo[1][ : -1]
                    current = current[length[0] + 5 : ]
                    put_data_for_connection(sock.fileno(), current)
    
                    cursor = con.execute("SELECT * FROM list WHERE name = ? ",(Username,))
    
                    row = cursor.fetchone()
    
                    cursor = con.execute("SELECT colomn FROM serverlocation ORDER BY id DESC LIMIT 1")
                    row2 = cursor.fetchone()
                    #print row2[0], SecretSession, row[1], Username, row[2], Password
                    if row is None:
                        RegisterMsg = struct.pack('!BB',HELLOMSG,WRONGNAME)
                        send_data(sock, RegisterMsg)
                        CONNECTION_LIST.remove(sock)
                        IncomingData.pop(sock.fileno(), None)
                    else:
                        if str(row[2]) != Password: #and int(row2[0]) == int(SecretSession[0]):
                            RegisterMsg = struct.pack('!BB',HELLOMSG,WRONGPWD)
                            send_data(sock, RegisterMsg)
                            CONNECTION_LIST.remove(sock)
                            IncomingData.pop(sock.fileno(), None)
                        else:
                            if int(row2[0]) != int(SecretSession[0]):
                                RegisterMsg = struct.pack('!BB',HELLOMSG,WRONGSSC)
                                send_data(sock, RegisterMsg)
                                CONNECTION_LIST.remove(sock)
                                IncomingData.pop(sock.fileno(), None)
                            else:
                                if str(row[4]) == 'waiting' or str(row[5]) == '---':
                                    RegisterMsg = struct.pack('!BB',HELLOMSG,FATAL)
                                    send_data(sock, RegisterMsg)
                                    CONNECTION_LIST.remove(sock)
                                    IncomingData.pop(sock.fileno(), None)
                                else:                                    
                                    RegisterMsg = struct.pack('!BB',HELLOMSG,ACCEPTED)
                                    if str(row[3]) == 'user':
                                        SOCKET_USERNAME[sock] = Username
                                    else:
                                        SOCKET_ADMINNAME[sock] = Username
                                    print "RegisterMsg"
                                    send_data(sock, RegisterMsg)
        
                                    BeatsPerCycle = 16    #when and how to set them?
                                    BeatPeriod = 500
                                    ConfigMsg = struct.pack('!BBHB', CONFIGMSG, BEATSPERCYCLE, BEATPERIOD, UserNumber)
                                    UserNumber = UserNumber + 1
                                    print "ConfigMsg Sent"
                                    send_data(sock, ConfigMsg)

                    # NO break HERE BECAUSE THERE MIGHT BE ANOTHER INCOMING
                    #   MESSAGE TO PROCESS IN while True LOOP

                elif MsgType[0] == CLOCKSYNCMSG:
                    print 'buffer length', len(IncomingData[sock.fileno()])
                    if len(current) < 7:
                        break
                    data = current[1 : 7]
                    roundTime = struct.unpack('!I', data[2 : ])[0]
                    print "ClkMsg", roundTime
                    ClkMsg = struct.pack('!B',CLOCKSYNCMSG)
                    ClkMsg += data[0 : 2]
                    print struct.unpack('!H',data[ : 2]) 
                    curTime = (time.time() - TIMEOFFSET) * 1000
                    ClkMsg += struct.pack('!I',curTime)
                    send_data(sock, ClkMsg)
                    trans(ROUNDTRIP, cIP, cPort, roundTime)
                    current = current[7 : ]
                    put_data_for_connection(sock.fileno(), current)

                elif MsgType[0] == SETDELAY:
                    print 'buffer length', len(IncomingData[sock.fileno()])
                    if len(current) < 5:
                        break
                    data = current[1 : 5]
                    print "Delay ", struct.unpack('!I', data)[0]
                    delaytime = int(struct.unpack('!I', data)[0])
                    current = current[5 : ]
                    put_data_for_connection(sock.fileno(), current)

                elif MsgType[0] == DRUMMSG:
                    print 'buffer length', len(IncomingData[sock.fileno()])
                    if len(current) < 8:
                        break
                    data = current[1 : 8]
                    print "Drum Stroke Received"
                    SendData = struct.pack('!B', DRUMMSG) + data[0]
                    GlobalTime = int(struct.unpack('!I', data[1:5])[0])
                    recvData = struct.unpack('!BB', data[-2:])
                    HeardTime = GlobalTime + DELAYTIME
                    print GlobalTime,recvData[0],recvData[1], HeardTime
                    SendData += struct.pack('!I', HeardTime) + data[-2:]
                    broadcast_data(sock, SendData, ALL)
                    current = current[8 : ]
                    put_data_for_connection(sock.fileno(), current)

                elif MsgType[0] == CHATMSG:
                    print 'buffer length', len(IncomingData[sock.fileno()])
                    if len(current) < 5:
                        break
                    data = current[1 : 5]
                    length = struct.unpack('!I',data)
                    if len(current) < length[0] + 5:
                        break
                    DestinText = current[5 : length[0] + 5]
                    DestinText = DestinText.split('\0')
                    Destination = DestinText[0]
                    print "Chat message", Destination, DestinText[1]
                    current = current[length[0] + 5 : ]
                    put_data_for_connection(sock.fileno(), current)

                    if Destination == '':
                        sender = SOCKET_USERNAME.get(sock)
                        if sender == None:
                            sender = SOCKET_ADMINNAME.get(sock)
                        print sender
                        ChatMsg = sender + '\0' + 'A'
                        ChatMsg = ChatMsg + DestinText[1] + '\0'
                        length = len(ChatMsg)
                        ChatMsg = struct.pack('!BI', CHATMSG, length) + ChatMsg
                        broadcast_data(sock, ChatMsg, ADMINS)

                    elif Destination == '*':
                        sender = SOCKET_USERNAME.get(sock)
                        if sender == None:
                            sender = SOCKET_ADMINNAME.get(sock)
                        ChatMsg = sender + '\0' + '*'
                        ChatMsg = ChatMsg + DestinText[1] + '\0'
                        length = len(ChatMsg)
                        ChatMsg = struct.pack('!BI', CHATMSG, length) + ChatMsg
                        broadcast_data(sock, ChatMsg, ALL)

                    elif Destination in SOCKET_USERNAME.values():
                        sender = SOCKET_USERNAME.get(sock)
                        if sender == None:
                            sender = SOCKET_ADMINNAME.get(sock)
                        print sender
                        ChatMsg = sender + '\0' + 'P'
                        ChatMsg = ChatMsg + DestinText[1] + '\0'
                        length = len(ChatMsg)
                        ChatMsg = struct.pack('!BI', CHATMSG, length) + ChatMsg

                        for s, uName in SOCKET_USERNAME.items():
                            if uName == Destination:
                                send_data(s, ChatMsg)

                    elif Destination in SOCKET_ADMINNAME.values():
                        sender = SOCKET_USERNAME.get(sock)
                        if sender == None:
                            sender = SOCKET_ADMINNAME.get(sock)
                        print sender
                        ChatMsg = sender + '\0' + 'P'
                        ChatMsg = ChatMsg + DestinText[1] + '\0'
                        length = len(ChatMsg)
                        ChatMsg = struct.pack('!BI', CHATMSG, length) + ChatMsg

                        for s, uName in SOCKET_ADMINNAME.items():
                            if uName == Destination:
                                send_data(s, ChatMsg)

                    else:
                        print "no Recipient"
                        sender = SOCKET_USERNAME.get(sock)
                        if sender == None:
                            sender = SOCKET_ADMINNAME.get(sock)
                        print sender
                        ChatMsg = sender + '\0' + '*'
                        ChatMsg = ChatMsg + 'Recipient not found: '+ DestinText[1] + '\0'
                        length = len(ChatMsg)
                        ChatMsg = struct.pack('!BI', CHATMSG, length) + ChatMsg
                        print "Sent back"
                        send_data(sock, ChatMsg)

                elif MsgType[0] == CONFIGMSG:
                    print 'buffer length', len(IncomingData[sock.fileno()])
                    if len(current) < 4:
                        break
                    data = current[1 : 4]
                    print "Configure Message Received"
                    recvData = struct.unpack('!BH', data)
                    BEATSPERCYCLE = recvData[0]
                    BEATPERIOD = recvData[1]
                    current = current[4 : ]
                    put_data_for_connection(sock.fileno(), current)


                elif MsgType[0] == AUDIOMSG:
                    print 'buffer length', len(IncomingData[sock.fileno()])
                    if len(current) < 5:
                        print 2
                        break
                    data = current[1 : 5]                        
                    length = struct.unpack('!I',data)
                    if len(current) < length[0] + 5:
                        print 3, length[0], len(current)
                        break
                    data = current[ : length[0] + 5]

                    # find message associated with this connection
                    # (need a dictionary mapping connections to byte arrays)
                    SendData = data
                    broadcast_data(sock, SendData, ALL)
                    print "sent"
                    current = current[length[0] + 5 : ]
                    #print current
                    put_data_for_connection(sock.fileno(), current)

             
            
conn.close()
server_socket.close()
