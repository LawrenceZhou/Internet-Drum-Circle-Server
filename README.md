# Internet-Drum-Circle-Server
This is the  code of Internet Drum Circle server side.
More information can be found here: https://sourceforge.net/p/floctrl/wiki/drum-circle-home/

  A band can be faced with the problems such as lack of physical space, lack of members. The internet makes it possible for musicians to overcome these problems. However, the inherent delay of the network is intolerable for music performance. The server in Internet Drum Circle specially designed for drum show solves the delay problem and other potent problems.

  The Internet Drum Circle is designed by Roger Dannenberg to connect many drum players performing live in a worldwide drum show over the Internet, especially for the students who take the introduction to the computer music in Carnegie Mellon University in 2016 Spring. In the design, there is one drum server and a number of players. With the help of the Internet Drum Circle, players can play drums together, talk with each others, get instructions from the conductor, etc. The configuration that the Internet Drum Circle adopted is star arrangement. A client should first contact music.cs.cmu.edu to get the location data of the server, and then send to and hear from the server all kinds of messages. During the performance, players will only communicate with the server. 

  In the following sections, I will first discuss the functions provided by the server. Then, the main issues I met and solutions to deal with them will be presented.

Server Functions
  The goal of adding a server is to coordinate and control players because it is much more practical than to have a complete peer-to-peer in which organization each drum player is connected by each other. The server is a Python Common Gate Interface (CGI) essentially, running permanently on a virtual machine in Carnegie Mellon University. When an administrator opens the start page, the server will start running.
  
Reliable Protocol and Bandwidth
  In this system, we transmit the packages by using TCP, which guarantees data reaches its destination in time without duplication. However, TCP operates on streams of data instead of packets. We implement the length-prefixing method to delimit the data stream into messages, which prepends each message with the length of that message.  
  Assuming 50 drum players, this gives us 8 bytes of data package per drum player (including the information of source player, drum, velocity and clock synchronization). Transmitting 10 packages per second, we get 4k bytes per second, or 32kbps download bandwidth from the server to each drum player, and 1.6Mbps outgoing bandwidth from the server. The upload bandwidth to the server is much less than the download bandwidth, which is 4k bytes per second, or 32kbps upload from all drum players to the server.

Process Messages
  The drum server can process messages of different kinds. In this part, I mainly discuss three kinds of information: drum stroke, clock synchronization information and chat message.

Drum Stroke
  Drum stroke has drum number, velocity, timestamp and other information essential to performance. The server gets a drum stroke from a player and will broadcast it with a certain delay so that performance information can be sent to all players and played synchronously.

Clock Synchronization Information
  Drum players scatter around the global, so the time of their machines can be different because time systems they adopt can be different from others or there can be a small difference even though they use the same time system. So synchronizing the server and players is necessary. Clock sync is basically achieved as follows:
    A round-trip message goes from a player to server and back. The message has a sequence number. The player side remembers the local time at which each message is sent. The server responds by sending a reply message with the player's sequence number and the server's local time. The player computes the mean of the send-time and receive-time to estimate the player's local time that corresponds to the server local time returned in the message. The difference between player local time and server local time is the clock offset.

  To make this more robust, the player should save info from the last 10 clock sync messages. Use the computed clock offset from the fastest round trip only.
  Clients should send clock sync messages every 10s, but probably every 1s for the first 20s after connecting so that it will get a good clock estimate soon after connecting.

Chat Message
  The Internet Drum Circle allows administrators and players to chat with each others. One can choose one chatting mode from talk-to-all, talk-to-privately, or talk-to-admins. The server classifies the chat messages and sends them to the right destinations.

Main Issues and Solutions
Make a Webpage Stay Active
  The server is still a CGI task serving the start page opened by the administrator – it continues to send status information so the administrator can monitor who is online, round-trip delays, etc. There is an issue of how to make a web page that stays active and receives continuous updates from the server.
  We use an event handler in the start page (written in HTML) to acquire information from the CGI task.

Network Delay
  Latency comes with communication. Musicians typically deal with delays of 50ms or more, as sound travels at the speed of 340m/s. However, the latencies over the network can be much greater than this. According to the result of the experiment I did in Carnegie Mellon University located in Pittsburgh, average roundtrip times from Pittsburgh include: 22ms (Carnegie Mellon University), 74ms (Pittsburgh), 128ms (Los Angeles), and 352ms (Shenyang, China). These delays are caused by the time light traveling through the cable, local routing and cable or DSL modem. This means the delay over the network is not tolerable for global music performance.
  So we take an idea from the Global Net Orchestra. We delay everything by one-period, for instance, 8 seconds (this is determined by 16 beats at 120 beats per minute). This method makes drum players line up perfectly.
  
  Incomplete Received Message
  As mentioned, we use the length-fixing method to delimit messages. However, when we send chat messages, one chat message can be separated into several messages. The issue is that the length of the message embedded in the first of these messages is the length of the whole chat message, which is definitely larger than that of the first message. 
  To deal with it, we introduce a buffer to store the message that the server receives. Each time the server reads a message from the buffer and then process it unless the buffer is empty. If the length of the message is larger than the length of the whole buffer, the server will put the message back to the buffer until the length of the buffer is not smaller than the first message.

Conclusion
  The Internet Drum Circle has been tested by Professor Roger Dannenberg and me several times and it runs successfully. We decide to introduce a bass for the main tempo into our system, which will make players follow it easier. 
  The server provides a sustaining reliable, and effective service for the whole system. It brings up new ideas about dealing with an intolerable delay to music performance. It will become the future of Internet music show.  

 

