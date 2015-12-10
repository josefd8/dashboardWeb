# dashboardWeb
Dashboard interactivo para una central telefonica Asterisk.

Este dashboard presenta estadisticas de un servidor asterisk a traves de un explorador web.
El backend esta realizado en PHP mediante framework Yii y el frontend mediante bootstrap.

El proyecto se conecta al servidor Asterisk mediante una conexion AMI y muestra estadisticas en
tiempo real de cantidad de agentes conectados, agentes ocupados, llamadas en espera, y estadisticas
por cola.

El servidor hace uso de una BD central (archivo BD adjunto), que guarda los datos de configuracion y le permite a todas las instancias abiertas de la aplicacion actualizarce con el mismo contenido
