## Setup:
* Install node modules via `npm install`
* Fill `.env` file with Discord login credentials (`DISCORD_MAIL` and `DISCORD_PASSWORD`)
* Run docker container:
```
cd discord-bot/node
docker run -it --rm --name discord-bot -v "$PWD":/usr/src/app -w /usr/src/app node:latest node server.js
```