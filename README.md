## Setup:
* Install node modules via `npm install`
* Fill `.env` file with Discord login credentials (`DISCORD_MAIL` and `DISCORD_PASSWORD`)
* Run docker container:
```
cd discord-bot/node
docker run --sig-proxy=false --rm --name discord-bot -v "$PWD":/usr/src/app -w /usr/src/app -v "$PWD/uploads":/uploads -p 8080:8080 node:latest node server.js
```

## Requirements:
for the youtube download to work you will need the youtube-dl package as well as ffmpeg
```
apt-get install youtube-dl ffmpeg
```