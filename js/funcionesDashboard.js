/**
 * Conjunto de funciones necesarias para el funcionamiento del dashboard
 */

$(function() {
	//Para obtener la informacion de las estadisticas del dashboard, se utiliza la tecnica SSE o "Server Sent Events"
	$(document).ready(function() {
	
		var evtSource = new EventSource(SSERequestUrl);
		
		evtSource.addEventListener("ping", function(e) {
			
			var tabla = jQuery.parseJSON(e.data);
			
			//Elimino todas las filas de la tabla y las creo de nuevo cada vez que el servidor
			//me envia datos nuevos
			$(".fila").remove();

			for (var i = 0; i < tabla.length; i = i + 1) {

				cola = tabla[i];
				tr = document.createElement('tr');
				tr.className = tr.className + "fila";
				
				//Se añade la cola a la cookie. La cookie mantiene una lista de las colas que actualmente
				//envia el servidor
				guardarColaEnCookie(cola[0],cola[14]);
					
				for (var j = 0; j < cola.length -1 ; j = j + 1) {
				
				if (cola[14]) {
					//Creo una fila nueva de la tabla y la agrego a tr
					td = document.createElement('td');
					td.className = td.className + "tableelement";

					var valor = cola[j];
					asignarColor(valor, j, td);

					td.appendChild(document.createTextNode(cola[j]));
					tr.appendChild(td);

					$(".table").append(tr);
					$('#containerPrincipal').fadeIn('slow');
					
					}
				}
			}
			
			ajustarTabla();
			LlamadasYRelojes(tabla);
			
		}, false);
		
		/**
		 * Recibe cada pocos segundos informacion de que colas se despliegan y cuales no. Esta informacino esta almacenada en la BD y es
		 * consultada cada pocos segundos. Esa informacion es utilizada para actualizar la cookie de la cual se vale el navegador
		 * para saber que colas pintar y cuales no. Se que parece muy engorroso esta forma de hacerlo, lo que sucede es que ya el codigo
		 * para el manejo de las colas por cookies estaba hecho, y reescribirlo todo tomaria mucho trabajo, asi que lo siento al que
		 * le toque mantener esto.
		 */
		evtSource.addEventListener("colas", function(e) {
			
			var entrada = e.data;
			
			if (entrada != "") 
			{
			var final = entrada.replace(/"false"/gi,"false");
			final = final.replace(/"true"/gi,"true");
			$.setCookie("colas",final);
			}
			
		},false);
		
		/**
		 * Recibe cada pocos segundos el valor de la variable texto_cintillo almacenada en la BD y lo utiliza para
		 * actualizar el texto del cintillo en el dashboard.
		 */
		evtSource.addEventListener("cintillo", function(e) {
			
			$('#cintillo div div').html(e.data);
			
		},false);
		
		/**
		 * Recibe cada pocos segundos el valor de direccion_foto almacenado en BD y lo utiliza para actualizar
		 * la foto (o el logo de la compañia) en el dashboard.
		 */
		evtSource.addEventListener("foto", function(e) {
			
			var foto_en_BD = e.data;
			
			if (foto_en_BD == ""){
				$("#Cargados img").remove();
			} else {
				$("#Cargados img").remove();
				$("#Cargados")
						.append(
								"<img id='foto' src='"
										+ baseUrl + "/images/" + foto_en_BD
										+ "' alt='' class='img-rounded'>");
			}

		},false);
		
		evtSource.onerror = function(e) {
			  bootbox.alert("Se detect&oacute un problema a la hora de inicar una conexi&oacuten con: " + SSERequestUrl + ".<br><br>" +
					  		"<strong>Error:</strong> " + e.data + "<br><br><br> El explorador intentar&aacute inicar una nueva conexi&oacuten en " +
							 "20 segundos. Puede presionar F5 en cualquier momento para recargar la p&aacutegina.");
			  evtSource.close();
			  console.debug(e);
			  
			  setTimeout(function() 
				{
					location.reload();
				}, 20000);
			};			
	})
})

/**
 * Dependiendo de la cantidad de filas que actualmente se muestren en el dashboard, se ajusta el tamaño de
 * la letra y se añade un titulo. Si el usuario selecciona una sola cola, el dashboard se veria muy vacio
 * y ademas dificil de leer a la distancia, por eso se coloca algo mas grande.
 */
function ajustarTabla(){

	var contador = $('.fila').length;

			if (contador == 1) {
				var nombre = $('td:nth-child(1)').html();
				$('#titulo_tabla').html("Estado de cola: " + nombre);
				$('#titulo_tabla').show();
				$('#espaciado').show();
				$('#espaciado2').show();
				$('td:nth-child(1)').hide();
				$('th:nth-child(1)').hide();
				$('td:nth-child(9)').hide();
				$('th:nth-child(9)').hide();
			} else {
				$('#espaciado').hide();
				$('#espaciado2	').hide();
				$('#titulo_tabla').hide();
				$('td:nth-child(1)').show();
				$('th:nth-child(1)').show();
				$('td:nth-child(9)').show();
				$('th:nth-child(9)').show();
			}
			
			//Aqui dependiendo de la cantidad de colas que se muestren, agrega algo de espacio 
			//entre el borde superior y el borde inferior de la tabla. Esto es para mejorar un poco
			//visualmente los elementos en el dashboard y que no se vean tan pegados.
			if (contador < 3) {
				$('#espaciado').show();
				$('#espaciado2').show();
			} 
			if (contador == 3) {
				$('#espaciado').hide();
				$('#espaciado2').show();
			}
			
			if (contador > 3) {
				$('#espaciado').hide();
				$('#espaciado2').hide();
			}

}

/**
 * Almacena en la cookie "colas" si una cola en particular es visible o no.
 * @param cola Nombre de la cola
 * @param estado Estado de la cola (true la cola es visible, false de lo contrario)
 */
function guardarColaEnCookie(cola, estado){
	
	try {
	
		var colas = $.getCookie("colas");
	
	if (colas == "") {
		colas = []
		$.setCookie("colas", "[]");
	} else {
		colas =  JSON.parse(colas);
	}
	
	
	var isInArray =  false;
	for	(var index = 0; index < colas.length; index++) {
		if (colas[index][0] == cola){
			colas[index][1] = estado;			
			isInArray = true;
	    }
	}
	
	
	if (isInArray == false) {
		colas.push([cola,estado]);
	}	
	
	$.setCookie("colas", JSON.stringify(colas));
	
	} catch (err){
		console.log("guardarColaEnCookie: " + err.message);
	}

}

/**
 * Metodo para calcular los valores que contendran los relojes y el cuadro de llamadas totales.
 * El calculo debe realizarse en base a las colas que actualmente son visibles en el dashboard
 * @param tabla Tabla de estadisticas de las colas tal y como la envia el servidor
 */
function LlamadasYRelojes(tabla) {
	//Estructura cola
	//0- nombreCola
	//1- llamadasRecibidas
	//2- llamadasContestadas
	//3- llamadasAbandonadas
	//4- asa
	//5- contestadasEnNS
	//6- ns
	//7- porcentajeAbandonadas
	//8- espera
	//9- aht
	//10- agentes
	//11- agentesOcupados
	//12- agentesDisponibles
	//13- agentesNoDisponibles
	//14- llamadasTotalesSuma

	var colas =  JSON.parse($.getCookie("colas"));
	var llamadas_totales = 0;
	var total_contestadas = 0;
	var total_contestadas_en_ns = 0;
	var total_asa = 0;
	var total_recibidas = 0;
	var total_abandonadas = 0;
	var total_colas = 0;
	
	for (var i = 0; i < tabla.length; i = i + 1) {
		
		//Si la cola se encuentra como visible en las cookies, se toma en cuanta para
		//los calculos
		if(colaEsVisible(tabla[i][0],colas)){
			
			llamadas_totales += parseFloat(tabla[i][8]); 
			total_contestadas += parseFloat(tabla[i][2]);
			total_contestadas_en_ns += parseFloat(tabla[i][5]);
			total_asa += parseFloat(tabla[i][4]);
			total_recibidas += parseFloat(tabla[i][1]);
			total_abandonadas += parseFloat(tabla[i][3]);
			total_colas += 1;
		}
	}
	
	//El resultado de la cantidad de llamadas totales (que es la suma de las llamadas en espera
	//de las colas visibles en el dashboard), se agrega al elemento DOM con el id llamadas
	$("#llamadas").text(llamadas_totales);
	
	//Dependiendo de la cantidad de llamadas en espera, se le puede colocar un color
	var temp = parseFloat(llamadas_totales);
	if (temp >= 9) {
		$('.panel').addClass('panel-danger');
	} else {
		$('.panel').removeClass('panel-danger');
	}
	
	
	//Para el resto de los relojes (relojNS, relojASA y relojAbandonadas) se actualiza un valor en un div que
	//esta oculto. Los relojes internamente tienen una rutina que se dispara cada segundo y que busca el valor
	//escrito en el div correspondiente para actualizar su valor.
	
	var relojNS = 0;
	if (total_contestadas == 0) {
		relojNS = 0;
	} else {
		relojNS = ((total_contestadas_en_ns/total_contestadas)*100).toFixed(2);
	}
	crearDivRelojes("relojNS",relojNS);

	
	var relojASA = 0;
	if (total_colas == 0) {
		relojASA = 0;
	} else {
		relojASA = (total_asa/total_colas).toFixed(2);
	}
	crearDivRelojes("relojASA",relojASA);
	
	
	relojAbandonadas = 0;
	if (total_recibidas == 0) {
		relojAbandonadas = 0;
	} else {
		relojAbandonadas = ((total_abandonadas/total_recibidas)*100).toFixed(2);
	}
	crearDivRelojes("relojAbandonadas",relojAbandonadas);
	
	
	//Si solo se va a mostrar una cola, la tabla se puede poner un poco mas grande
	if (total_colas == 1) {
		$('.table').removeClass('tablaNormal');
		$('.table').addClass('tablaGrande');
	} else {
		$('.table').removeClass('tablaGrande');
		$('.table').addClass('tablaNormal');
	}
}

/**
 * Crea un div oculto en el documento con el nombre pasado como parametro y con el valor señalado
 * @param reloj Nombre del div
 * @param valor valor del div
 */
function crearDivRelojes(reloj,valor){
	$("#" + reloj).remove();
	div = document.createElement('div');
	div.setAttribute("id", reloj)
	div.className = div.className + "divoculto";
	div.appendChild(document.createTextNode(valor));
	$("#tabla").append(div);
}


/**
 * Verifica si la cola pasada como parametro esta marcada como visible en el arreglo de colas de la cookie.
 * Para que una cola sea visible, debe estar marcada como true en la cookie
 * @param cola Cola a verificar
 * @param colas Arreglo de colas y visibilidad (nombre_cola => true/false)
 * @returns {Boolean} True si la cola es visible, false lo contrario
 */
function colaEsVisible(cola, colas){

	var esVisible = false;
	
	for (var i = 0; i < colas.length; i = i + 1) {
		if (colas[i][0] == cola) {
			if (colas[i][1] == true) {
				return true;
			} else {
				return false;
			}
		}
	}
}

function asignarColor(valor, j, td) {
	//Estructura cola
	//0- nombreCola
	//1- llamadasRecibidas
	//2- llamadasContestadas
	//3- llamadasAbandonadas
	//4- asa
	//5- contestadasEnNS
	//6- ns
	//7- porcentajeAbandonadas
	//8- espera
	//9- aht
	//10- agentes
	//11- agentesOcupados
	//12- agentesDisponibles
	//13- agentesNoDisponibles
	//14- llamadasTotalesSuma

	switch (j) {
	case 6:
		if (valor < 70) {
			td.className = td.className + " rojo";
		}

		if (valor < 80 && valor > 70) {
			td.className = td.className + " amarillo";
		}

		break;

	case 4:
		if (valor > 40) {
			td.className = td.className + " rojo";
		}

		if (valor > 30 && valor < 40) {
			td.className = td.className + " amarillo";
		}

		break;

	case 8:
		if (valor > 9) {
			td.className = td.className + " rojo";
		}

		if (valor > 5 && valor < 9) {
			td.className = td.className + " amarillo";
		}

		break;

	case 7:
		if (valor > 15) {
			td.className = td.className + " rojo";
		}

		if (valor > 5 && valor < 15) {
			td.className = td.className + " amarillo";
		}

		break;

	}

}

/**
 * Muestra un cuadro de dialogo que le permite al usuario seleccionar mediante checkboxes que colas van a ser 
 * visibles en el dashboard.
 * Para esto, lee la cookie "colas" para obtener las colas disponibles, y coloca como marcadas las que tienen estado
 * true. Este metodo basicamente permite al usuario cambiar el estado de las colas en la cookie, colocandolas como true
 * o false. De esta forma, en el siguiente mensaje proveniente del servidor, el dashboard solo desplegara las que 
 * esten como true en la cookie.
 */
function cambiarTabla() {

	var colas =  JSON.parse($.getCookie("colas"));
	var texto = "";
	
	for	(var index = 0; index < colas.length; index++) {
		if (colas[index][1] == false) {
			texto += '<input type="checkbox" name="'+ colas[index][0] +'"> ' + colas[index][0] + '<br>';
		} else {
			texto += '<input type="checkbox" name="'+ colas[index][0] +'" checked="checked"> ' + colas[index][0] + '<br>';
		}
	}

	bootbox
			.dialog({
				title : "Cambiar colas a mostrar.",
				message : 		'<div class="row">'+
				'<div class="col-md-12">'+
				'<form class="form-horizontal">'+
					'<div class="form-group">'+
						'<div class="col-md-12">'+
							'<span class="help-block">Seleccione las colas que aparecer&aacuten en el dashboard:</span>'+
						'</div>'+
					'</div>'+
					'<div class="form-group">'+
						'<div class="col-md-12">'+
							'<div class="row">'+
								'<div class="col-md-1">'+'</div>'+
								'<div class="col-md-10">'+
									'<div class=row>'+
									texto +
									'</div>'+
								'</div>'+
								'<div class="col-md-1">'+'</div>'+
							'</div>'+
						'</div>'+
					'</div>'+
					'<br>'+
					'Despu&eacutes de presionar "Guardar" los cambios pueden tardar un par de segundos en reflejarse.'+
				'</form>'+
			'</div>'+
		'</div>',
				buttons : {
					success : {
						label : "Guardar",
						className : "btn-success",
						callback : function() {
							
							for	(var index = 0; index < colas.length; index++) {
								var respuesta = $("input[name='" + colas[index][0] + "']").is(":checked");
								colas[index][1] = respuesta;
							}
							
							//Genero ademas una llamada AJAX para que se guarde en BD que colas fueron seleccionadas
							//Esto permite que todos los dashboards se sincronicen con las mismas colas
							$.ajax({
								  url: ActualizarColasBD,
								  cache: false,
								  type: 'post',
								  data: {salida : colas},
								});
							
						}
					}
				}
			});

}

$(function() {

	$(document)
			.ready(
					function() {
						$('#fileToUpload').hide();
						$('marquee').marquee();
						$('#containerPrincipal').hide();
					})

})

function opendialog() {

	bootbox.prompt("Cambiar texto del cintillo:", function(result) {
		if (result != null) {
			$('#cintillo div div').html(result);
			
			//Ejecuto una funcion AJAX para notificar al servidor del cambio
			$.ajax({
				  url: CambiarTextoCintillo,
				  cache: false,
				  type: 'post',
				  data: { texto: result },
				  success: function(html){
				    console.log(html);
				  }
				});
		}

	})

}

function ocultarBoton() {
	$('#fileToUpload').toggle('fast');
	$('#Cargados').toggle('fast');
}

function uploadImg() {

	var archivo = document.getElementById("fileToUpload");
	if (jQuery("#fileToUpload").val() == "")
		return false;

	var archivo = archivo.files;
	var data = new FormData();

	//Como no sabemos cuantos archivos subira el usuario, iteramos la variable y al
	//objeto de FormData con el metodo "append" le pasamos clave/valor, usamos el indice "i" para
	//que no se repita, si no lo usamos solo tendra el valor de la ultima iteracion
	for (i = 0; i < archivo.length; i++) {
		data.append('archivo', archivo[i]);
	}

	$.ajax({
		url : guardarFotoUrl, //Url a donde la enviaremos
		type : 'POST', //Metodo que usaremos
		contentType : false, //Debe estar en false para que pase el objeto sin procesar
		data : data, //Le pasamos el objeto que creamos con los archivos
		processData : false, //Debe estar en false para que JQuery no procese los datos a enviar
		cache : false, //Para que el formulario no guarde cache

	}).done(function(msg) {
		jQuery("#Cargados").html(msg); //Mostrara los archivos cargados en el div con el id "Cargados"
	});

}