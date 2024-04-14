# syntax=docker/dockerfile:1

FROM ubuntu:22.04
LABEL maintainer="seotarek1"

ADD ./docker-entrypoint.sh ./docker-entrypoint.sh
ADD ./e2e-tests ./e2e-tests

RUN chmod +x ./docker-entrypoint.sh

ENTRYPOINT ["sh", "-c", "./docker-entrypoint.sh"]

STOPSIGNAL SIGRTMIN+3
