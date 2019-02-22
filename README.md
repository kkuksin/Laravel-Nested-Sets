### Requirements
----

* docker
* docker-compose
* composer

# Installation process for debian-based systems.
---
### Get code
```
git clone https://github.com/dudich/binder.git <target directory name>
```
```
composer install

```

add .env file
```
cp .env.example .env
```

change file .env for connected DB
* DB_CONNECTION=mysql
* DB_HOST=db
* DB_PORT=3306
* DB_DATABASE=tree
* DB_USERNAME=root
* DB_PASSWORD=root
* DB_TABLE_PREFIX=tbl

run commands  
```
cd docker
sudo -s
make start
make connect_app
php artisan migrate --seed
```

From the outside, the port 3333 is used to access the database, which you can change in the settings of the docker/docker-compose.yml file

### Run scripts
---
#### A) add a binder
```
php artisan binder:add --dbID=<value> --parentId=<value> --name=<value>
```
if  command will call without params than will create new row with 
* parentID = null,
* dbID = MAX(dbID)+1
* name=MASTER

if name will be empty than name will be RANDOM string

#### B) fully delete the soft-deleted entries

```
php artisan binder:delete --dbID=<value>
```

#### C) move one set of binders into another set of binders

```
php artisan binder:move --id=<value>  --parentID=<value>
```
