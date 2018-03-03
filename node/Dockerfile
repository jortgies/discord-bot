FROM node:8

RUN echo deb http://http.debian.net/debian jessie-backports main >> /etc/apt/sources.list.d/backports.list

RUN apt-get update && \
    apt-get -t jessie-backports install -y ffmpeg

RUN node -v