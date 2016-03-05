## Setup:
* Install node modules via `npm install`
* Fill `.env` file with Discord login credentials (`DISCORD_MAIL` and `DISCORD_PASSWORD`)
* Run docker container:
```
cd discord-bot/node
docker run --sig-proxy=false --rm --name discord-bot -v "$PWD":/usr/src/app -w /usr/src/app -v "$PWD/uploads":/uploads -p 8080:8080 node:latest node server.js
```