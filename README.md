## Setup:
* Install node modules via `npm install`
* Fill `.env` file with Discord login credentials (`DISCORD_MAIL` and `DISCORD_PASSWORD`)
* Build docker container:
```
cd discord-bot/node
docker build -t node-ffmpeg .
```
* Run docker container:
```
docker run --sig-proxy=false --rm --name discord-bot -v "$PWD":/usr/src/app -w /usr/src/app -v "$PWD/uploads":/uploads -p 8080:8080 node-ffmpeg node server.js
```
* Install systemd service file:
```
cat << EOF > /etc/systemd/system/discord-bot.service
[Unit]
Description=Discord Bot
Requires=docker.service
After=docker.service

[Service]
Restart=always
ExecStart=/usr/bin/docker start -a discord-bot
ExecStop=/usr/bin/docker stop -t 2 discord-bot

[Install]
WantedBy=local.target
EOF
```

## Requirements:
for the youtube download to work you will need the youtube-dl package as well as ffmpeg
```
apt-get install youtube-dl ffmpeg
```