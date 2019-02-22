app = docker_app_1
docker_nginx = docker_nginx_1

#some commands
start: #Containers start
	@docker-compose up -d

stop: #Stop
	@docker-compose stop

show_containers:
	@docker ps

connect_app:
	@docker exec -it $(app) bash

connect_nginx:
	@docker exec -it $(docker_nginx) bash
