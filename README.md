# dashboardWeb
Dashboard interactivo para una central telefonica Asterisk.

Este dashboard presenta estadisticas de un servidor asterisk a traves de un explorador web.
El backend esta realizado en PHP mediante framework Yii y el frontend mediante bootstrap.

El proyecto se conecta al servidor Asterisk mediante una conexion AMI y muestra estadisticas en
tiempo real de cantidad de agentes conectados, agentes ocupados, llamadas en espera, y estadisticas
por cola.

El servidor hace uso de una BD central (archivo BD adjunto), que guarda los datos de configuracion y le permite a todas las instancias abiertas de la aplicacion actualizarce con el mismo contenido


##Nota: 

- Requiere del framework Yii 1.1.16 para funcionar correctamente
- La configuración de la conexión con el servidor AMI (Asterisk Manager Interface) debe ser modificada en protected/config/main.php, arreglo 'params' (final del archivo).
- El sistema requiere de la conexión con la base de datos. Esto le permite a las diferentes instancias del dashboard abiertas en distintos ordenadores sincronizarse entre si con la misma información. El nombre de la BD debe ser "dashboard" y adjunto se encuentra el archivo sql para la creacion de la tabla "settings" con sus diferentes campos. La dirección de la BD debe tambien modificarse en el connectionString en protected/config/main.php
