# RES - Projet Infrastructure - HTTP :

###### Dylan Hame & Yannis Ansermoz / HEIG-VD / Juin 2018

## Lancer une machine docker sur virtualbox.

```bas
docker-machine create --driver=virtualbox vbox-test
docker-machine env vbox-test
docker-machine ls
docker-machine ssh vbox-test
```

Après cela, il est possible de se connecter à la machine Docker.

Les informations sur la machine sont contenus dans 

```bash
/Users/dylan.hamel/.docker/machine/machines/vbox-test
```

```bash
➜  vbox-test docker-machine ls
NAME        ACTIVE   DRIVER       STATE     URL                         SWARM   DOCKER        ERRORS
vbox-test   -        virtualbox   Running   tcp://192.168.99.100:2376           v18.05.0-ce
```

On connait maintenant l'adresse IP de notre docker-machine.

```bas
➜  vbox-test ping 192.168.99.100
PING 192.168.99.100 (192.168.99.100): 56 data bytes
64 bytes from 192.168.99.100: icmp_seq=0 ttl=64 time=0.356 ms
64 bytes from 192.168.99.100: icmp_seq=1 ttl=64 time=0.346 ms
^C
--- 192.168.99.100 ping statistics ---
2 packets transmitted, 2 packets received, 0.0% packet loss
round-trip min/avg/max/stddev = 0.346/0.351/0.356/0.005 ms
➜  vbox-test
```



## Créer un container docker avec un contenu static PHP

Après s'être connecté sur la docker-machine via la commande :

```bash
docker-machine ssh vbox-test
```

Créer un dossier de travail

```bash
/home/docker/RES/RES_Labo_HTTP_Infra/docker-images
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images$ ls
apache-php-image/     apache-reverse-proxy/ express-image/
```

Aller dans le dossier "apache-php-image/".

Créer un Dockerfile 

```dockerfile
FROM php:7.0-apache
COPY content/ /var/www/html/
```

Cela va signifier, que lorsque l'on créera un container docker à partir de ce fichier, c'est-à-dire un cointainer apache avec php 7.0, le dossier ```content/``` sera copié dans le dossier ```/var/www/html/``` du container.

Copier dans le dossier  ```content/``` les fichiers d'une page Boostrap

https://startbootstrap.com/template-overviews/coming-soon/

```bas
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-php-image$ ls content/
LICENSE            gulpfile.js        js/                package.json
README.md          img/               mp4/               scss/
css/               index.html         package-lock.json  vendor/
```

Créer un "build" de ce container 

```bash
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-php-image$ pwd
/home/docker/RES/RES_Labo_HTTP_Infra/docker-images/apache-php-image
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-php-image$ ls
Dockerfile  content/    dockercmd

docker build -t res/apache_php .
docker run -d -p 9090:80 res/apache_php
```

à ce moment, le container "php-apache" est lancé.

Vérifier cela :

```bash
docker@vbox-test:/$ docker ps
CONTAINER ID        IMAGE                  COMMAND                  CREATED             STATUS              PORTS                    NAMES
d6fa0903f4eb        res/apache_php         "docker-php-entrypoi…"   43 minutes ago      Up 43 minutes       0.0.0.0:9090->80/tcp     dreamy_knuth
```

Trouver l'adresse IP de ce container :

```bash
docker@vbox-test:/$ docker inspect dreamy_knuth | grep -i ipaddress
            "SecondaryIPAddresses": null,
            "IPAddress": "172.17.0.2",
                    "IPAddress": "172.17.0.2",
docker@vbox-test:/$
```

Vérification du fonctionnement

![Capture d’écran 2018-05-28 à 21.12.44](/Users/dylan.hamel/Desktop/Capture d’écran 2018-05-28 à 21.12.44.png)



## Créer un container docker avec une page JavaScript

Comme précédement, aller dans 

```bash
/home/docker/RES/RES_Labo_HTTP_Infra/docker-images/express-image
```

et créer les fichier "Dockerfile" et le sous-dossier ```src/```

```bash
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/express-image$ ls
Dockerfile  src/
```

Contenu du Dockerfile :

```dockerfile
FROM node:8.11.2
COPY src /opt/app

CMD ["node", "/opt/app/index.js"]
```

Cela veut donc dire que le dossier source sera copié dans ```opt/app/``` et que les commande après CMD seront exécutées.

Créer un fichier ```index.js``` contenant le code Javascript suivant

```javascript
var Chance = require('chance');
var chance = new Chance();

var express = require('express');
var app = express();

app.get('/', function(request, respond) {
	respond.send(generateStudents());

});

app.listen(3000, function() {
	console.log("Accept HTTP requests");
});


function generateStudents() {
 	var numberOfStudents = chance.integer({min:0,max:10});
	console.log(numberOfStudents);
	var students = [];
	for (var i=0;i<numberOfStudents;i++) {
		var gender = chance.gender();
		var birthYear = chance.year({min:1986,max:1996});
		students.push({fistName:chance.first({gender: gender}), lastname:chance.last(),gender:gender,birthday:chance.birthday({year: birthYear})});
	};
	console.log(students);
	return students;
}
```

Créer un "build" de ce container 

```bash
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/express-image$ pwd
/home/docker/RES/RES_Labo_HTTP_Infra/docker-images/express-image
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/express-image$ ls
Dockerfile  src/
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/express-image$

docker build -t res/express_students .
docker run -d -p 9091:80 res/express_students
```

à ce moment, le container "express_students" est lancé.

Vérifier cela :

```bash
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/express-image$ docker ps
CONTAINER ID        IMAGE                  COMMAND                  CREATED             STATUS              PORTS                    NAMES
9bd83a0cb49c        res/express_students   "node /opt/app/index…"   32 minutes ago      Up 32 minutes       0.0.0.0:9091->3000/tcp   sleepy_pasteur
d6fa0903f4eb        res/apache_php         "docker-php-entrypoi…"   About an hour ago   Up About an hour    0.0.0.0:9090->80/tcp     dreamy_knuth
```

![Capture d’écran 2018-05-28 à 21.12.38](/Users/dylan.hamel/Desktop/Capture d’écran 2018-05-28 à 21.12.38.png)



## Créer un container docker avec un Reverse Proxy Apache

Comme précédemment, aller dans le dossier destiner au reverse proxy d'apache et créer les fichiers nécessaires.

```bash
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy$ pwd
/home/docker/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy$ ls
Dockerfile  conf/
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy$
```

Contenu du Dockerfile 

```dockerfile
FROM php:7.0-apache
COPY conf/ /etc/apache2

RUN a2enmod proxy proxy_http
RUN a2ensite 000-* 001-*
```

Cela signifie que ce container sera un container "apache2" et que les deux commandes ci-dessous seront lancées :

* ```RUN a2enmod proxy proxy_http``` permet de charger les modules pour le reverse proxy
* ```RUN a2ensite 000-* 001-*``` Permet d'activer les fichier commançant par "000-" et "000-1"



dans le dossier de conf, on va créer les fichiers que l'on veut insérer dans le container.

Ces fichiers seront copiés dans ```/etc/apache2```

```bash
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy$ ls -R
.:
Dockerfile  conf/

./conf:
sites-available/

./conf/sites-available:
000-default.conf        001-reverse-proxy.conf
```

Modifier le contenu du fichier ```001-reverse-proxy.conf```

```bash
<VirtualHost *:80>
        ServerName demo.res.ch

        #ErrorLog ${APACHE_LOG_DIR}/error.log
        #CustomLog ${APACHE_LOG_DIR}/access.log combined

        ProxyPass "/api/students/" "http://172.17.0.3:3000/"
        ProxyPassReverse "/api/sutdents" "http://172.17.0.3:3000/"

        ProxyPass "/" "http://172.17.0.2/"
        ProxyPassReverse "/" "http://172.17.0.2/"

</VirtualHost>
```

l'IP 172.17.0.3 est l'IP du container qui contient du Javascript

l'IP 172.17.0.2 est l'IP du container qui contient du php avec la page Boostrap

Modifier le fichier ```/etc/hosts``` et ajouter la ligne 

```bash
192.168.99.100  demo.res.ch
```

Cela permettra de taper cela dans le navigateur web ```demo.res.ch``` et qu'il soit résolu comme 192.168.99.100 qui est l'adresse IP de la docker-machine.

![Capture d’écran 2018-05-28 à 21.22.26](/Users/dylan.hamel/Desktop/Capture d’écran 2018-05-28 à 21.22.26.png)

Créer un "build" de ce container 

```bash
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy$ pwd
/home/docker/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy$ ls
Dockerfile  conf/
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy$

docker build -t res/apache_rp .
docker run -d -p 8080:80 res/apache_rp
```



Vérfication du bon fonctionnement du Reverse Proxy :

![Capture d’écran 2018-05-28 à 21.23.02](/Users/dylan.hamel/Desktop/Capture d’écran 2018-05-28 à 21.23.02.png)

![Capture d’écran 2018-05-28 à 21.23.27](/Users/dylan.hamel/Desktop/Capture d’écran 2018-05-28 à 21.23.27.png)



Push sur GIT de la partie 3 depuis la **Docker-Machine** :

```git
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra$ git init
Reinitialized existing Git repository in /home/docker/RES/RES_Labo_HTTP_Infra/.git/
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra$ git config user.email "dylan.hamel@heig-vd.ch"
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra$ git config user.name "Dylan-Hamel"
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra$ git add .
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra$ git commit -m "Partie 3 - Reverse Proxy Done"
[master 7e748c0] Partie 3 - Reverse Proxy Done
 3 files changed, 22 insertions(+), 11 deletions(-)
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra$ git push origin master
Username for 'https://github.com': Dylan-Hamel
Password for 'https://Dylan-Hamel@github.com':
Counting objects: 12, done.
Compressing objects: 100% (11/11), done.
Writing objects: 100% (12/12), 1.47 KiB | 0 bytes/s, done.
Total 12 (delta 3), reused 0 (delta 0)
remote: Resolving deltas: 100% (3/3), completed with 3 local objects.
To https://github.com/Dylan-Hamel/RES_Labo_HTTP_Infra.git
   bf28017..7e748c0  master -> master
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra$
```



## 4. Implémenter un requête AJAX avec JQuery

Modifier le "Dockerfile" pour installer VIM et faire les mises à jour de la machine Linux

```dockerfile
FROM php:7.0-apache

RUN apt-get update && \
	apt-get install -y vim

COPY content/ /var/www/html/
```

Après avoir modifier le Dockerfile, il faut reconstruire l'image 

```bash
docker build -t res/apache_php .
docker run -d -p 9090:80 res/apache_php
```

Modifier le fichier ```index.html``` pour intégrer un script

Ajouter une ligne pour aller chercher un scritp JavaScript qui sera stocké dans le dossier ```/var/www/html/js/```

```html
	<!-- Custom JavaScript to load Students -->                        
    <script src="js/students.js"></script> 
```

Créer le fichier ```students.js```

```bash
root@d6fa0903f4eb:/var/www/html/js# touch students.js
root@d6fa0903f4eb:/var/www/html/js# ls
coming-soon.js	coming-soon.min.js  students.js
```

Pour tester, écrire dans ce fichier 

```javascript
$(function() {
console.log("Loading Students");
});
```

![/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/photo01.png ](/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/photo01.png )

Implémenté dans le fichier de la docker-machine :

```bash
/home/docker/RES/RES_Labo_HTTP_Infra/docker-images/apache-php-image/content
```

Rendre le script exécutable 

```bash
chmod +x students.js 

docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-php-image/content/js$ ls -lah
total 12
drwxr-sr-x    2 docker   staff        100 Jun  3 09:24 ./
drwxr-sr-x    8 docker   staff        320 Jun  3 09:21 ../
-rwxr-xr-x    1 docker   staff        245 May 28 18:19 coming-soon.js
-rwxr-xr-x    1 docker   staff        125 May 28 18:19 coming-soon.min.js
-rwxr-xr-x    1 docker   staff         51 Jun  3 09:25 students.js
```

on voit que le script a bien été trouvé et que le contenu est correct.

=> Relancer le container pour que cela prenne effet.

```bash
docker build -t res/apache_php .
docker run -d res/apache_php   # Il n'y a plus besoin de faire du port forwarding -> PROXY

docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-php-image$ docker ps
CONTAINER ID        IMAGE                  COMMAND                  CREATED             STATUS              PORTS                    NAMES
5c49616abc20        res/apache_php         "docker-php-entrypoi…"   3 seconds ago       Up 2 seconds        80/tcp                   loving_liskov
b9d3154a5901        res/apache_rp          "docker-php-entrypoi…"   5 days ago          Up 5 days           0.0.0.0:8080->80/tcp     eager_saha
9bd83a0cb49c        res/express_students   "node /opt/app/index…"   5 days ago          Up 5 days           0.0.0.0:9091->3000/tcp   sleepy_pasteur
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-php-image$ 
```



Modifier le script ```students.js```

```javascript
$(function() {
        console.log("Loading Students");

    function loadStudents() {
        $.getJSON( "/api/students/", function( students ) {
            console.log(students[0]);
            var message = "Nobody is here";
            if (students.length > 0) {
                message = students[0].fistName + " " + students[0].lastname;
            }
            $(".mb-3").text(message);
        });
    };
    loadStudents();
    setInterval( loadStudents, 1000);
});
```

la ligne ```$(".mb-3").text(message);``` permet de remplacer le texte de la classe "mb-3" du fichier HTML

![photo02](/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/photo02.png)



Ses appels sont basés sur la fonction qui se trouve dans 

```javascript
var Chance = require('chance');
var chance = new Chance();

var express = require('express');
var app = express();
 
app.get('/', function(request, respond) {
	respond.send(generateStudents());

});

app.listen(3000, function() {
	console.log("Accept HTTP requests");
});


function generateStudents() {
 	var numberOfStudents = chance.integer({min:0,max:10});
	console.log(numberOfStudents);
	var students = [];
	for (var i=0;i<numberOfStudents;i++) {
		var gender = chance.gender();
		var birthYear = chance.year({min:1986,max:1996});
		students.push({fistName:chance.first({gender: gender}), lastname:chance.last(),gender:gender,birthday:chance.birthday({year: birthYear})});
	};
	console.log(students);
	return students;
}
```



/!\ nous avons fait quelques erreures dans l'écriture de nos fonctions.

* students.fistName

* student.lastname

  

**Résultat :**

![photo03](/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/photo03.png)



## 5. Reverse Proxy étape 2

L'argument "-e" de Docker permet de setter des variables d'environements dans les containers.

```bash
docker run -e HELLO=world -it res/apache_rp /bin/bash

# set une variable d'environnement "HELLO=world"
# "export" pour voir les variables d'environements
```



Remplacer les adresses IP des container ```res/apache_php``` et ```res/express_students``` par des nom statiques.

Créer un nouveau fichier dans le dossier du reverse proxy. Ce fichier sera un script.

```bash
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy$ pwd
/home/docker/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy$ touch apache2-foreground
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy$ chmod +x apache2-foreground 
```

Insérer le contenu suivant dans le fichier (script) créé

```bash
#!/bin/bash
set -e

echo "Start setup RES"
echo "Static app URL: $STATIC_APP"
echo "Dynamic app URL: $DYNAMIC_APP"

rm -f /var/run/apache2/apache2.pid
exec apache2 -D FOREGROUND
```

Modifier également le Dockerfile

```dockerfile
FROM php:7.0-apache
COPY conf/ /etc/apache2
COPY apache2-foreground /usr/local/bin/
                       
RUN apt-get update && \       
        apt-get install -y vim

                            
RUN a2enmod proxy proxy_http
RUN a2ensite 000-* 001-*
```

```bash
docker build -t res/apache_rp .
docker run -d -e STATIC_APP=172.17.0.2:80 -e DYNAMIC_APP=172.17.0.3:3000 -p 8080:80 res/apache_rp
# Sans le -d pour voir les erreurs et les #echo du du fichier apache2-foreground
```

Dans notre cas, le reverse proxy avait l'IP 172.17.0.4.

Il suffit à présent de récupérer via le langage PHP ces variables d'environnement pour pouvoir les utiliser dans le fichier de configuration du reverse proxy.

On récupère la valeur en utilisant la fonction ```getenv()``` :

```php
<?php
    $ipAddress = getenv("DYNAMIC_APP");
>
```

Créer un nouveau dossier  ```template ``` 

Mettre dans un fichier la configuration ci-dessous. Cette configuration permettera d'utiliser les variables d'envrionnement passer au container Docker lors de son lancement.

```php
<?php
        $ip_static = getenv('STATIC_APP');
        $ip_dyn = getenv("DYNAMIC_APP");
?>

<VirtualHost *:80>
        ServerName demo.res.ch

        #ErrorLog ${APACHE_LOG_DIR}/error.log
        #CustomLog ${APACHE_LOG_DIR}/access.log combined

        ProxyPass '/api/students/' 'http://<?php print "$ip_dyn";?>/'
        ProxyPassReverse '/api/sutdents' 'http://<?php print "$ip_dyn";?>/'

        ProxyPass '/' 'http://<?php print "$ip_static";?>/'
        ProxyPassReverse '/' 'http://<?php print "$ip_static";?>/'

</VirtualHost>
```

Modifier ensuite le Dockerfile pour que ce dossier soit copier dans les containers.

```dockerfile
FROM php:7.0-apache

RUN apt-get update && \
        apt-get install -y vim

COPY conf/ /etc/apache2
COPY template/ /var/apache2/template/
COPY apache2-foreground /usr/local/bin/

RUN a2enmod proxy proxy_http
RUN a2ensite 000-* 001-*
```

Tester que le fichier est bien présent dans le container

```bash
docker run -it res/apache_rp /bin/bash

ker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy$ docker run -it res/apache_rp /bin/bash
root@6a233a3049cb:/var/www/html# cat /var/apach2/template/config-template.php 

<?php 
// STATIC_APP=172.17.0.2:80 -e DYNAMIC_APP=172.17.0.3:3000
// USE ENV VARIABLE 
$ip_static = getenv("STATIC_APP");           
$ip_dyn = getenv("DYNAMIC_APP");                        
?>                                                       
                                                            
<VirtualHost *:80>                                                
        ServerName demo.res.ch                                    
                                                                  
        #ErrorLog ${APACHE_LOG_DIR}/error.log           
        #CustomLog ${APACHE_LOG_DIR}/access.log combined
                                                            
        ProxyPass '/api/students/' 'http://<?php print "$ip_dyn"?>/'
        ProxyPassReverse '/api/sutdents' 'http://<?php print "$ip_dyn"?>/'
                                                                  
        ProxyPass '/' 'http://<?php print $ip_static?>/'       
        ProxyPassReverse '/' 'http://<?php print "$ip_static"?>/' 
                                                 
</VirtualHost>   
root@6a233a3049cb:/var/www/html# exit
```

Modifier également le script d'installation 

```bash
#!/bin/bash
set -e

echo "Start setup RES"
echo "Static app URL: $STATIC_APP"
echo "Dynamic app URL: $DYNAMIC_APP"

php /var/apache2/template/config-template.php > /etc/apache2/sites-available/001-reverse-proxy.conf

rm -f /var/run/apache2/apache2.pid
exec apache2 -DFOREGROUND
service apache2 start
```

#### Nous avions des problèmes de variables d'environnements

nous avons donc récupérer un script sur internet

```bash
#!/bin/bash
set -e

# Add
echo "Setup for the res lab..."
echo "Statc app URL: $STATIC_APP"
echo "Dynamic app URL: $DYNAMIC_APP"


# Note: we don't just use "apache2ctl" here because it itself is just a shell-script wrapper around apache2 which provides extra functionality like "apache2ctl start" for l
# (also, when run as "apache2ctl <apache args>", it does not use "exec", which leaves an undesirable resident shell process)

: "${APACHE_CONFDIR:=/etc/apache2}"
: "${APACHE_ENVVARS:=$APACHE_CONFDIR/envvars}"
if test -f "$APACHE_ENVVARS"; then
        . "$APACHE_ENVVARS"
fi

# Apache gets grumpy about PID files pre-existing
: "${APACHE_RUN_DIR:=/var/run/apache2}"
: "${APACHE_PID_FILE:=$APACHE_RUN_DIR/apache2.pid}"
rm -f "$APACHE_PID_FILE"

# create missing directories
# (especially APACHE_RUN_DIR, APACHE_LOCK_DIR, and APACHE_LOG_DIR)
for e in "${!APACHE_@}"; do
        if [[ "$e" == *_DIR ]] && [[ "${!e}" == /* ]]; then
                # handle "/var/lock" being a symlink to "/run/lock", but "/run/lock" not existing beforehand, so "/var/lock/something" fails to mkdir
                #   mkdir: cannot create directory '/var/lock': File exists
                dir="${!e}"
                while [ "$dir" != "$(dirname "$dir")" ]; do
                        dir="$(dirname "$dir")"
                        if [ -d "$dir" ]; then
                                break
                        fi
                        absDir="$(readlink -f "$dir" 2>/dev/null || :)"
                       	if [ -n "$absDir" ]; then
                                mkdir -p "$absDir"
                        fi
                done

                mkdir -p "${!e}"
        fi
done


php /var/apache2/template/config-template.php > /etc/apache2/sites-available/001-reverse-proxy.conf

exec apache2 -DFOREGROUND "$@"
```

Remonter le container pour mettre à jour ```res/apache_rp```

```bash
docker build -t res/apache_rp .
docker run -d -e STATIC_APP=172.17.0.2:80 -e DYNAMIC_APP=172.17.0.3:3000 -p 8080:80 res/apache_rp
```

```
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/apache-reverse-proxy$ docker run -e STATIC_APP=172.17.0.2:80 -e DYNAMIC_APP=172.17.0.3:3000 -p
 8080:80 res/apache_rp
Start setup RES
Static app URL: 172.17.0.2:80
Dynamic app URL: 172.17.0.3:3000
```

![rp01](/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/rp01.png)

![rp02](/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/rp02.png)



Mettre le tout sur GitHub

```bash
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra$ git commit -m "Partie 5 - OK"
[master c686f4d] Partie 5 - OK
 5 files changed, 59 insertions(+), 5 deletions(-)
 create mode 100755 docker-images/apache-reverse-proxy/apache2-foreground
 create mode 100644 docker-images/apache-reverse-proxy/conf/sites-available/001-reverse-proxy.conf-part1
 create mode 100644 docker-images/apache-reverse-proxy/template/config-template.php
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra$ 
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra$ 
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra$ git push
Username for 'https://github.com': dylan.hamel@heig-vd.ch
Password for 'https://dylan.hamel@heig-vd.ch@github.com': 
Counting objects: 10, done.
Compressing objects: 100% (8/8), done.
Writing objects: 100% (10/10), 1.44 KiB | 0 bytes/s, done.
Total 10 (delta 0), reused 0 (delta 0)
To https://github.com/Dylan-Hamel/RES_Labo_HTTP_Infra.git
   78027ce..c686f4d  master -> master
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra$ 
```



## Load-Balancing

###### Sources :

* https://www.youtube.com/watch?v=se4PhIwyWLw&t=157s
* https://blogs.oracle.com/oswald/easy-http-load-balancing-with-apache
* https://httpd.apache.org/docs/2.4/fr/mod/mod_proxy_balancer.html



Pour cette partie, nous allons tout d'abord créer deux dossier contenant la configuration de deux serveurs apache extrêmement simple :

```bash
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images$ mkdir srv_apache_01
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images$ mkdir srv_apache_02

# Création des fichiers Dockerfile. 
# Ils sont identiques à ceux de apache-php-image
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images$ cp apache-php-image/Dockerfile srv_apache_01/Dockerfile 
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images$ cp apache-php-image/Dockerfile srv_apache_02/Dockerfile 

# Création des dossier "content" qui sera copier dans "/var/www/html/"
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images$ mkdir srv_apache_01/content
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images$ mkdir srv_apache_02/content
```

Contenu des fichier index.html se trouvant dans les dossiers content 

```bash
# srv_apache_01/content/index.html

SERVEUR 01

# srv_apache_01/content/index.html

SERVEUR 02
```

Créer un build de ces containers et les tester.

```bash
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/srv_apache_01$ docker build -t res/srv01 .
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/srv_apache_02$ docker build -t res/srv02 .
```

Lancer les container et tester en mappant un port.

```bash
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/srv_apache_02$ docker run -d -p 1000:80 res/srv02
18657e9180323134c8dda808a938077397420a5b24e570cabfc1d598b9d41975
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/srv_apache_02$ docker run -d -p 1001:80 res/srv01
29a9d0da9874f7b7c0f4ab460593c59e237d51481b4ff93f708f935d708025b2
docker@vbox-test:~/RES/RES_Labo_HTTP_Infra/docker-images/srv_apache_02$
```

![srv_apache01](/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/srv_apache01.png)

![srv_apache02](/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/srv_apache02.png)

=> les deux containers fonctionnent.

```bash
docker run -d  res/srv01 # IP 172.17.0.5
docker run -d  res/srv02 # IP 172.17.0.4

# obtenu avec les commandes 
# docker inspect mystifying_mendeleev | grep -i ipaddr
```

Créer maintenant un nouveau dossier qui sera le dossier du container apache load-balancing de test. Créer également un dossier de configuration d'apache et un Dockerfile

```bash
mkdir apache-load-balancing
cd apache-load-balancing
touch Dockerfile
mkdir conf
cd conf
mkdir sites-available
cd sites-available
touch 001-lb.conf
```

remplir le Dockerfile comme ci-dessous :

```dockerfile
FROM php:7.0-apache

RUN apt-get update && \
        apt-get install -y vim

COPY conf/ /etc/apache2/

RUN a2enmod lbmethod_byrequests
RUN a2enmod proxy_balancer
RUN a2enmod proxy proxy_http
RUN a2ensite 000-* 001*
```

Et le fichier de configuration Apache comme suit :

```bash
<VirtualHost *:80>
        ProxyRequests off

        ServerName demo.res.ch

        <Proxy balancer://mycluster>
                # IP du container du serveur 1
                BalancerMember http://172.17.0.4:80
                # IP du container du serveur 2
                BalancerMember http://172.17.0.5:80

                # Security "technically we aren't blocking
                # anyone but this is the place to make
                # those changes.
                Require all granted
                # In this example all requests are allowed.

                # Load Balancer Settings
                # We will be configuring a simple Round
                # Robin style load balancer.  This means
                # that all webheads take an equal share of
                # of the load.
                ProxySet lbmethod=byrequests

        </Proxy>

        # balancer-manager
        # This tool is built into the mod_proxy_balancer
        # module and will allow you to do some simple
        # modifications to the balanced group via a gui
        # web interface.
        <Location /balancer-manager>
                SetHandler balancer-manager

                # I recommend locking this one down to your
                # your office
                Require host example.org

        </Location>

        # Point of Balance
        # This setting will allow to explicitly name the
        # the location in the site that we want to be
        # balanced, in this example we will balance "/"
        # or everything in the site.
        ProxyPass /balancer-manager !
        ProxyPass / balancer://mycluster/

</VirtualHost>
```

Créer un build de ce containter et lancer le.

```bash
docker build -t res/apache_lb .
docker run -p 80:80 -d res/apache_lb
```

![srv_apache03](/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/srv_apache03.png)

![srv_apache04](/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/srv_apache04.png)

#### Intégrer maintenant cette configuration au reverse proxy.

Copier la configuration actuelle du reverse proxy dans un nouveau dossier

```bash
cp apache-reverse-proxy/ rp-lb/
```

Rajouter ensuite le module nécessaire dans le Dockerfile

```dockerfile
FROM php:7.0-apache

RUN apt-get update && \
        apt-get install -y vim

COPY apache2-foreground /usr/local/bin/
COPY conf/ /etc/apache2
COPY template/ /var/apache2/template/


RUN a2enmod proxy proxy_http proxy_balancer lbmethod_byrequests
RUN a2ensite 000-* 001-*
```

éditer ensuite le fichier de template 

```php
<?php
        $ip_static = getenv('STATIC_APP');
        $ip_static2 = getenv('STATIC_APP2');
        $ip_dyn = getenv('DYNAMIC_APP');
        $ip_dyn2 = getenv('DYNAMIC_APP2');
?>

<VirtualHost *:80>
        ServerName demo.res.ch

        <Proxy "balancer://apache-php">
                # Static
                BalancerMember 'http://<?php print "$ip_static"?>'
                # Static
                BalancerMember 'http://<?php print "$ip_static2"?>'
        </Proxy>


        <Proxy "balancer://express-dyn">
                # Dynamic
                BalancerMember 'http://<?php print "$ip_dyn"?>'
                # Dynamic
                BalancerMember 'http://<?php print "$ip_dyn2"?>'
        </Proxy>

        ProxyPass "/api/students/" "balancer://express-dyn"
        ProxyPassReverse "/api/students/" "balancer://express-dyn"

        ProxyPass "/" "balancer://apache-php"
        ProxyPassReverse "/" "balancer://apache-php"

        <Location "/balancer-manager">
                SetHandler balancer-manager
                Require host demo.res.ch
        </Location>

</VirtualHost>
```

On créer 2 load-balancer appelés :

* apache-php
* express-dyn

Dans lesquels on met respectivement les deux containers static qui seront lancés.

```bash
docker run -d res/apache_php
docker run -d res/apache_php
docker run -d res/express_dyn
docker run -d res/express_dyn
```

```bash
docker@vbox-test:~/RES_Labo_HTTP_Infra/docker-images$ docker ps
CONTAINER ID        IMAGE               COMMAND                  CREATED             STATUS              PORTS               NAMES
e4cfbc0b0424        res/apache_php      "docker-php-entrypoi…"   10 seconds ago      Up 9 seconds        80/tcp              jovial_gates
3746bca61dc8        res/express_dyn     "node /opt/app/index…"   13 seconds ago      Up 13 seconds                           musing_wilson
a20091e2b43c        res/apache_php      "docker-php-entrypoi…"   5 minutes ago       Up 5 minutes        80/tcp              amazing_archimedes
46028be05fa9        res/express_dyn     "node /opt/app/index…"   5 minutes ago       Up 5 minutes                            nervous_wilson
docker@vbox-test:~/RES_Labo_HTTP_Infra/docker-images$
```

Récupérer les IP des différents containers et lancer la commande

```bash
docker build -t res/rp-lb .
docker run -p 8888:80 -e STATIC_APP=172.17.0.3 -e STATIC_APP2=172.17.0.6 -e DYNAMIC_APP=172.17.0.3 -e DYNAMIC_APP2=172.17.0.5 -d res/rp-lb
```

>apache_php = 172.17.0.3
>
>apache_php = 172.17.0.6
>
>express_dyn = 172.17.0.3
>
>express_dyn = 172.17.0.5

Modifier un minimum les deux serveurs secondaires pour voir la différence lorsque l'on se connecte

```bash
docker exec -it jovial_gates /bin/bash
```

```html
<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-tt
o-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

<!--    <title>Coming Soon - Start Bootstrap Theme</title>		-- Ligne supprimée-->
    <title>SERVEUR 02 - Start Bootstrap Theme</title>
```

![lb01](/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/lb01.png)

![lb02](/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/lb02.png)



```bash
➜  ~ wget -S http://demo.res.ch:8888
--2018-06-07 14:19:42--  http://demo.res.ch:8888/
Résolution de demo.res.ch (demo.res.ch)… 192.168.99.100
Connexion à demo.res.ch (demo.res.ch)|192.168.99.100|:8888… connecté.
requête HTTP transmise, en attente de la réponse… 
  HTTP/1.1 200 OK
  Date: Thu, 07 Jun 2018 12:19:42 GMT
  Server: Apache/2.4.10 (Debian)
  Last-Modified: Sun, 03 Jun 2018 09:41:10 GMT
  ETag: "b4d-56db99df4d980"
  Accept-Ranges: bytes
  Content-Length: 2893
  Vary: Accept-Encoding
  Content-Type: text/html
  Keep-Alive: timeout=5, max=100
  Connection: Keep-Alive
Taille : 2893 (2,8K) [text/html]
Sauvegarde en : « index.html »

index.html               100%[==================================>]   2,83K  --.-KB/s    ds 0s      

2018-06-07 14:19:42 (184 MB/s) — « index.html » sauvegardé [2893/2893]

➜  ~ 
```

Mettre le tout sur git.



## Load-Balancing Sticky Session

Dans le ```wget``` on voit qu'il n'y a pas de Cookie de session.

C'est ce que nous allons implémenter dans cette étape pour les requêtes sur le serveur statique.

Dans le Dockerfile rajouter un module :

```dockerfile
FROM php:7.0-apache

RUN apt-get update && \
        apt-get install -y vim

COPY apache2-foreground /usr/local/bin/
COPY conf/ /etc/apache2
COPY template/ /var/apache2/template/


RUN a2enmod proxy proxy_http proxy_balancer lbmethod_byrequests
RUN a2enmod headers
RUN a2ensite 000-* 001-*
```

Rajouter les informations dans le fichier ```template/config-template.php``` pour obtenir le fichier comme ci-dessous :

```bash
<?php
        $ip_static = getenv('STATIC_APP');
        $ip_static2 = getenv('STATIC_APP2');
        $ip_dyn = getenv('DYNAMIC_APP');
        $ip_dyn2 = getenv('DYNAMIC_APP2');
?>

<VirtualHost *:80>
        ServerName demo.res.ch

        <Proxy "balancer://apache-php">
                # Static
                BalancerMember 'http://<?php print "$ip_static"?>' route=1
                # Static
                BalancerMember 'http://<?php print "$ip_static2"?>' route=2
        </Proxy>


        <Proxy "balancer://express-dyn">
                # Dynamic
                BalancerMember 'http://<?php print "$ip_dyn"?>'
                # Dynamic
                BalancerMember 'http://<?php print "$ip_dyn2"?>'
        </Proxy>

        ProxyPass /api/students/ balancer://express-dyn
        ProxyPassReverse /api/students/ balancer://express-dyn

        Header add Set-Cookie "ROUTEID=.%{BALANCER_WORKER_ROUTE}e; path=/" env=BALANCER_ROUTE_CHANGED

        ProxyPass / balancer://apache-php stickysession=ROUTEID
        ProxyPassReverse / balancer://apache-php

        <Location "/balancer-manager">
                SetHandler balancer-manager
                Require host demo.res.ch
        </Location>

</VirtualHost>
```

Tester avec la commande ```wget```:

```bash
➜  ~ wget -S http://demo.res.ch:8888
--2018-06-07 16:38:38--  http://demo.res.ch:8888/
Résolution de demo.res.ch (demo.res.ch)… 192.168.99.100
Connexion à demo.res.ch (demo.res.ch)|192.168.99.100|:8888… connecté.
requête HTTP transmise, en attente de la réponse…
  HTTP/1.1 200 OK
  Date: Thu, 07 Jun 2018 14:30:12 GMT
  Server: Apache/2.4.10 (Debian)
  Last-Modified: Sun, 03 Jun 2018 09:41:10 GMT
  ETag: "b4d-56db99df4d980"
  Accept-Ranges: bytes
  Content-Length: 2893
  Vary: Accept-Encoding
  Content-Type: text/html
  Set-Cookie: ROUTEID=.2; path=/	# <== on voit la route qu'il utilise dans le cookie
  Keep-Alive: timeout=5, max=100
  Connection: Keep-Alive
Taille : 2893 (2,8K) [text/html]
Sauvegarde en : « index.html.5 »

index.html.5                       100%[================================================================>]   2,83K  --.-KB/s    ds 0s

2018-06-07 16:38:38 (345 MB/s) — « index.html.5 » sauvegardé [2893/2893]

➜  ~
```

```bash
➜  ~ wget -S http://demo.res.ch:8888/api/students/
--2018-06-07 14:46:47--  http://demo.res.ch:8888/api/students/
Résolution de demo.res.ch (demo.res.ch)… 192.168.99.100
Connexion à demo.res.ch (demo.res.ch)|192.168.99.100|:8888… connecté.
requête HTTP transmise, en attente de la réponse… 
  HTTP/1.1 200 OK
  Date: Thu, 07 Jun 2018 12:46:47 GMT
  Server: Apache/2.4.10 (Debian)
  Last-Modified: Sun, 03 Jun 2018 09:41:10 GMT
  ETag: "b4d-56db99df4d980"
  Accept-Ranges: bytes
  Content-Length: 2893
  Vary: Accept-Encoding
  Content-Type: text/html
  Keep-Alive: timeout=5, max=100
  Connection: Keep-Alive
Taille : 2893 (2,8K) [text/html]
Sauvegarde en : « index.html.2 »

index.html.2                      100%[============================================================>]   2,83K  --.-KB/s    ds 0s      

2018-06-07 14:46:47 (212 MB/s) — « index.html.2 » sauvegardé [2893/2893]

➜  ~ 

# Pas de Cookie pour les requêtes vers demo.res.ch:8888/api/students/
```

Avec chaque requête on arrivera sur le SERVEUR 02.

Nous avons relancer une requête en supprimant le Cookie, on voit que l'on utilise la deuxième route.

![stickySession](/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/stickySession.png)

![stickySession2](/Volumes/Data/HEIG_VD/RES/Labo/_Projet/images/stickySession2.png)



## Github

Tous les fichiers se trouvent sur le Github ci-dessous :

```
https://github.com/Dylan-Hamel/RES_Labo_HTTP_Infra.git
```



## Astuces DOCKER 

Voir les logs d'un Container

```
docker logs containerName
```

Se connecter en SSH sur le container

```bash
docker exec -it res/apache_php /bin/bash
# [OR]
docker run -it res/apache_php /bin/bash
```

Supprimer tous les containers :

```dockerfile
# docker rm 'docker ps -qa'
docker kill NomDuContainer
```

