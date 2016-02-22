require('dotenv').config();
var lame = require('lame');
var fs = require('fs');
var Discordie = require("discordie");
var express  = require('express');
var bodyParser = require('body-parser');
var Events = Discordie.Events;
var app      = express();
var client = new Discordie();
var currentVoiceChannel;
var uploadPath = process.env.UPLOAD_PATH;

client.connect({
    email: process.env.DISCORD_MAIL,
    password: process.env.DISCORD_PASSWORD
});

client.Dispatcher.on(Events.GATEWAY_READY, function() {
    console.log("Connected as: " + client.User.username);
    say('hello, yes dis is bot!', 'general', 'BotTesting');
});

client.Dispatcher.on(Discordie.Events.DISCONNECTED, (e) => {
    const delay = 5000;
    const sdelay = Math.floor(delay/100)/10;

    if (e.error.message.indexOf("gateway") >= 0) {
        console.log("Disconnected from gw, resuming in " + sdelay + " seconds");
    } else {
        console.log("Failed to log in or get gateway, reconnecting in " + sdelay + " seconds");
    }
    setTimeout(connect, delay);
});

client.Dispatcher.on(Events.MESSAGE_CREATE, function(e) {
    if (e.message.content == "ping")
        e.message.channel.sendMessage("pong");

    if(e.message.content == "vjoin")
        voiceJoin('testing', 'BotTesting');

    if(e.message.content == "vleave")
        voiceLeave();

    if(e.message.content == "play")
        play();

    if(e.message.content == "gtfo") {
        client.disconnect();
        process.exit();
    }
});

function reconnect() {
    if(client.connected)
        client.disconnect();
    client.connect({
        email: process.env.DISCORD_MAIL,
        password: process.env.DISCORD_PASSWORD
    });
    if(currentVoiceChannel !== null)
        voiceJoin(currentVoiceChannel.guild, currentVoiceChannel.voiceChannelName);
}

var stopPlaying = true;
function play(filename, voiceConnectionInfo) {
    stopPlaying = false;

    var mp3decoder = new lame.Decoder();
    mp3decoder.on('format', decode);
    fs.createReadStream(filename).pipe(mp3decoder);

    function decode(pcmfmt) {
        var options = {
            frameDuration: 60,
            sampleRate: pcmfmt.sampleRate,
            channels: pcmfmt.channels,
            float: false,

            multiThreadedVoice: true
        };

        const frameDuration = 60;

        var readSize =
            pcmfmt.sampleRate / 1000 *
            options.frameDuration *
            pcmfmt.bitDepth / 8 *
            pcmfmt.channels;

        mp3decoder.once('readable', function() {
            if(!client.VoiceConnections.length) {
                reconnect();
                return console.log("Voice not connected...trying to reconnect");
            }

            if(!voiceConnectionInfo) {
                voiceConnectionInfo = client.VoiceConnections[0];
            }
            var voiceConnection = voiceConnectionInfo.voiceConnection;

            var encoder = voiceConnection.getEncoder(options);

            const needBuffer = () => encoder.onNeedBuffer();
            encoder.onNeedBuffer = function() {
                var chunk = mp3decoder.read(readSize);
                if (stopPlaying) return;

                if (!chunk) return setTimeout(needBuffer, options.frameDuration);

                var sampleCount = readSize / pcmfmt.channels / (pcmfmt.bitDepth / 8);
                encoder.enqueue(chunk, sampleCount);
            };

            needBuffer();
        });

        //mp3decoder.once('end', () => setTimeout(play, 100, voiceConnectionInfo));
    }
}

function stop() {
    stopPlaying = true;
}

function say(text, textChannelName, guildName) {
    var guild = client.Guilds.find(g => g.name == guildName);
    var channel = guild.channels.find(c => c.name == textChannelName);
    channel.sendMessage(text);
}

function voiceJoin(voiceChannelName, guildName) {
    currentVoiceChannel = { guild: guildName, voiceChannelName: voiceChannelName };
    var guild = client.Guilds.find(g => g.name == guildName);
    guild.voiceChannels
        .forEach(channel => {
        if(channel.name.toLowerCase().indexOf(voiceChannelName) >= 0)
            channel.join();
    });
}

function voiceLeave() {
    currentVoiceChannel = null;
    client.Channels
        .filter(channel => channel.type == 'voice')
        .forEach(channel => {
            if(channel.joined)
                channel.leave();
    });
}

app.listen(8080);
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));

app.post('/sounds/play', function(req, res) {
    var file = uploadPath+req.body.filename;
    fs.access(file, function(err) {
        if(err) {
            res.send('file not found');
        }
        else {
            play(file);
            res.send('playing sound');
        }
    });
});

app.get('/sounds/stop', function(req, res) {
    stop();
    res.send('stopping sound');
});

app.get('/sounds/files', function(req, res) {
    fs.readdir(uploadPath, function(err, files) {
        var newFiles = [];
        files.forEach(function(element, index, array) {
            newFiles.push({ id: index + 1, name: element });
        });
        res.send(newFiles);
    });
});

app.post('/text/send', function(req, res) {
    var guildName = req.body.guildName;
    var channelName = req.body.channelName;
    var text = req.body.text;
    say(text, channelName, guildName);
    res.send('sent message');

});

app.post('/voice/join', function(req, res) {
    var guildName = req.body.guildName;
    var channelName = req.body.channelName;
    voiceJoin(channelName, guildName);
    res.send('joined voice chat');
});

app.get('/voice/leave', function(req, res) {
    voiceLeave();
    res.send('left voice chat');
});

app.get('/testSounds', function(req, res) {
	var file = uploadPath+'01.mp3';
	var rs = fs.createReadStream(file);
	rs.on('open', function() {
		rs.pipe(res);
	});
});

/*
app.get('/', function(req, res) {
    res.sendFile(__dirname+'/public/index.html');
});

app.get('/core.js', function(req, res) {
    res.sendFile(__dirname+'/public/core.js');
});
*/
