require('dotenv').config();
var lame = require('lame');
var fs = require('fs');
var Discordie = require("discordie");
var express  = require('express');
var bodyParser = require('body-parser');
var youtubeStream = require('youtube-audio-stream')
var Events = Discordie.Events;
var app      = express();
var client = new Discordie({autoReconnect: true});
var currentVoiceChannel;
var uploadPath = process.env.UPLOAD_PATH;
var token = process.env.DISCORD_TOKEN;
var stopPlaying = true;

if(token != "")
{
    client.connect({token: token});
}
else
{
    client.connect({
        email: process.env.DISCORD_MAIL,
        password: process.env.DISCORD_PASSWORD
    });
}

client.Dispatcher.on(Events.GATEWAY_READY, function() {
    console.log("Connected as: " + client.User.username);
    //say('hello, yes dis is bot!', 'general', 'BotTesting');
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

function play(filename, info) {
    stopPlaying = false;
    if (!client.VoiceConnections.length) {
        return console.log("Voice not connected");
    }

    if (!info) info = client.VoiceConnections[0];

    client.User.setGame(filename);

    var mp3decoder = new lame.Decoder();
    var file = fs.createReadStream(filename);
    file.pipe(mp3decoder);

    mp3decoder.on('format', pcmfmt => {
        var options = {
            frameDuration: 60,
            sampleRate: pcmfmt.sampleRate,
            channels: pcmfmt.channels,
            float: false
        };

        var encoderStream = info.voiceConnection.getEncoderStream(options);
        if (!encoderStream) {
            return console.log(
                "Unable to get encoder stream, connection is disposed"
            );
        }

        encoderStream.resetTimestamp();
        encoderStream.removeAllListeners("timestamp");
        encoderStream.on("timestamp", function(timestamp){
            if(stopPlaying)
                mp3decoder.unpipe();
        });

        mp3decoder.pipe(encoderStream);

        encoderStream.once("unpipe", () => file.destroy());
    });
}

function playYoutube(url, info) {
    stopPlaying = false;
    if (!client.VoiceConnections.length) {
        return console.log("Voice not connected");
    }

    if (!info) info = client.VoiceConnections[0];

    client.User.setGame("Youtube");

    var mp3decoder = new lame.Decoder();
    try {
        youtubeStream(url).pipe(mp3decoder);
    }
    catch(e) {
        console.log(e);
    }


    mp3decoder.on('format', pcmfmt => {
        var options = {
            frameDuration: 60,
            sampleRate: pcmfmt.sampleRate,
            channels: pcmfmt.channels,
            float: false
        };

        var encoderStream = info.voiceConnection.getEncoderStream(options);
        if (!encoderStream) {
            return console.log(
                "Unable to get encoder stream, connection is disposed"
            );
        }

        encoderStream.resetTimestamp();
        encoderStream.removeAllListeners("timestamp");
        encoderStream.on("timestamp", function(timestamp){
            if(stopPlaying)
                mp3decoder.unpipe();
        });

        mp3decoder.pipe(encoderStream);
    });
}

function stop() {
    client.User.setGame(null);
    stopPlaying = true;
}

function say(text, textChannelName, guildName) {
    var guild = client.Guilds.find(g => g.name == guildName);
    var channel = guild.channels.find(c => c.name == textChannelName);
    channel.sendMessage(text);
}

function voiceJoin(voiceChannelId, guildId) {
    currentVoiceChannel = { guild: guildId, voiceChannelId: voiceChannelId};
    var guild = client.Guilds.find(g => g.id == guildId);
    guild.voiceChannels
        .forEach(channel => {
        if(channel.id.indexOf(voiceChannelId) >= 0)
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

var allowCrossDomain = function(req, res, next) {
    res.header('Access-Control-Allow-Origin', '*');
    res.header('Access-Control-Allow-Methods', 'GET,PUT,POST,DELETE');
    res.header('Access-Control-Allow-Headers', 'Content-Type,X-XSRF-TOKEN,X-Requested-With');

    next();
};
app.use(allowCrossDomain);

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

app.post('/sounds/playYoutube', function(req, res) {
    var url = req.body.url;
    playYoutube(url);
    res.send("playing url "+url);
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
    var guildId = req.body.guildId;
    var channelId= req.body.channelId;
    voiceJoin(channelId, guildId);
    res.send('joined voice chat: ' + guildId + ' - ' + channelId);
});

app.get('/voice/leave', function(req, res) {
    voiceLeave();
    res.send('left voice chat');
});

app.get('/voice/guilds', function(req, res) {
    res.send(client.Guilds);
});

app.get('/voice/channels/:guild', function(req, res) {
    var guild = req.params.guild;
    var channels = client.Channels.voiceForGuild(guild);
    res.send(channels);
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
