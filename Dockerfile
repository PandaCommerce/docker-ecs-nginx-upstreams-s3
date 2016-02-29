FROM ubuntu:14.04

ADD script.php $HOME/
ADD run.sh /usr/local/bin/run.sh
ADD awscli-bundle /tmp/awscli-bundle

RUN apt-get update && apt-get install -y \
    php5-cli \
    php5-json \
    nginx \
    make \
    curl \
    git \
    ca-certificates \
    python \
    python-dev \
    aws-cli \
  && rm -rf /var/cache/apk/*

EXPOSE 80

ENTRYPOINT ["run.sh"]
