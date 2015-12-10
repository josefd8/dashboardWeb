

<script type="text/javascript">
//Conjunto de variables que se utilizan para las funciones dentro del archivo de funcionesDashboard.js
var guardarFotoUrl =  "<?php echo $this->createUrl('site/guardarFoto'); ?>";
var baseUrl = "<?php echo Yii::app()->request->baseUrl;?>"
var SSERequestUrl =  "<?php echo $this->createUrl('site/SSERequest'); ?>";
var CambiarTextoCintillo =  "<?php echo $this->createUrl('site/CambiarTextoCintillo'); ?>";
var ActualizarColasBD =  "<?php echo $this->createUrl('site/ActualizarColasBD'); ?>";
</script>

<script type="text/javascript"
	src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<script type="text/javascript"
	src="<?php echo Yii::app()->request->baseUrl; ?>/js/marquee.js"></script>
<script type="text/javascript"
	src="<?php echo Yii::app()->request->baseUrl; ?>/js/jCook.js"></script>
<script type="text/javascript"
	src="<?php echo Yii::app()->request->baseUrl; ?>/js/funcionesDashboard.js"></script>	
	
<div class="row" style="overflow: hidden" id="containerPrincipal">

	<div class="row">
		<br>
	</div>

	<div class="row">
		<div class="col-md-3">
			<div id="maincontainer">
				<div id="contenedorfoto" style="cursor: pointer"
					onclick="ocultarBoton()">

					<input type="file" name="fileToUpload" id="fileToUpload"
						onchange="uploadImg()">
					<div id="Cargados">
						<!-- Aqui deberia cargarse la foto -->
					</div>

				</div>
			</div>
		</div>

		<div class="col-md-6">
			<div id="titulo" class="panel panel-default">
				<div>
					<h1>Gesti&#243n Centro de Contacto</h1>
				</div>
			</div>

		</div>

		<div class="col-md-3">
			<div id="maincontainer">
				<div id="contenedorfoto">
					<img id="foto"
						src="<?php echo Yii::app()->request->baseUrl; ?>/images/Advantel.png"
						class="img-rounded">
				</div>
			</div>
		</div>

	</div>

	<div class="row">
		<br> 
	</div>

	
	<!-- Añado algo de espacio entre el titulo y la tabla cuando solo hay una cola seleccionada -->
	<div class="row" id="espaciado">
	<br>
	<br>
	</div>
	
	<div class="row">
		<div class="col-md-4"></div>
		<div class="col-md-4">
		<!-- En caso de que el usuario eliga ver una sola cola, se mostrara el nombre de esta como titulo -->
		<div id="titulo_tabla">
		</div>
		<br>
		</div>
		<div class="col-md-4"></div>
	</div>
	
	<!-- Tabla de dashboard -->
	<div class="row">
		<div class="col-md-1"></div>
		<div class="col-md-10">

			<div id="tabla">

				<div class="panel panel-default blanco">
					<div id="tablaDashboard">

						<table class="table table-bordered" id="table"
							onclick="cambiarTabla()">
							<tr>
								<th class="tableheader titulo">Cola</th>
								<th class="tableheader">Recib.</th>
								<th class="tableheader">Contes.</th>
								<th class="tableheader">Aband.</th>
								<th class="tableheader">ASA</th>
								<th class="tableheader">Cont. N/S</th>
								<th class="tableheader">N/S</th>
								<th class="tableheader">% Aban</th>
								<th class="tableheader">Espera</th>
								<th class="tableheader">AHT</th>
								<th class="tableheader">Conec.</th>
								<th class="tableheader">Ocup.</th>
								<th class="tableheader">Disp.</th>
								<th class="tableheader">No Disp.</th>
							</tr>
						</table>

					</div>
				</div>

			</div>

		</div>
		<div class="col-md-1"></div>
	</div>
	
	<!-- Añado algo de espacio entre el titulo y la tabla cuando solo hay una cola seleccionada -->
	<div class="row" id="espaciado2">
	<br>
	<br>
	<br>
	</div>

	<!-- relojes y cuadro de llamadas totales-->
	<div class="row">



		<div class="col-md-3">
		<?php
		
		$this->renderPartial ( 'solidgauge', array (
				'id' => 'reloj1', // id del div que lo contendra (debe ser unico)
				'titulo' => "<b>N/S</b>",
				'leyenda' => '%',
				'rango' => array (
						0,
						100 
				),
				'rango1' => 65,
				'rango2' => 75,
				'rango3' => 90,
				'colorRango1' => '#DF5353',
				'colorRango2' => '#DDDF0D',
				'colorRango3' => '#55BF3B',
				'tag' => 'relojNS' 
		) ); // Indica el tag html donde estara cargado el valor con el que se
		     // actualizara el valor
		
		?>

		</div>

		<div class="col-md-3">
		<?php
		
		$this->renderPartial ( 'solidgauge', array (
				'id' => 'reloj2', // id del div que lo contendra (debe ser unico)
				'titulo' => "<b>ASA</b>",
				'leyenda' => 'seg',
				'rango' => array (
						0,
						100 
				),
				'rango1' => 30,
				'rango2' => 40,
				'rango3' => 90,
				'colorRango1' => '#55BF3B',
				'colorRango2' => '#DDDF0D',
				'colorRango3' => '#DF5353',
				'tag' => 'relojASA' 
		) ); // Indica el tag html donde estara cargado el valor con el que se
		     // actualizara el valor
		
		?>
		</div>

		<div class="col-md-3">
		<?php
		
		$this->renderPartial ( 'solidgauge', array (
				'id' => 'reloj3', // id del div que lo contendra (debe ser unico)
				'titulo' => "<b>Abandono</b>",
				'leyenda' => '%',
				'rango' => array (
						0,
						100 
				),
				'rango1' => 3,
				'rango2' => 10,
				'rango3' => 15,
				'colorRango1' => '#55BF3B',
				'colorRango2' => '#DDDF0D',
				'colorRango3' => '#DF5353',
				'tag' => 'relojAbandonadas' 
		) ); // Indica el tag html donde estara cargado el valor con el que se
		     // actualizara el valor.
		
		?>
		</div>

		<div class="col-md-3">
			<div id="llamadasTotales"></div>
			<!-- cantidad de llamadas totales -->
			<?php $this->renderPartial('llamadasTotales');?>
		</div>



	</div>
	<!-- fin de relojes y cuadro de llamadas totales-->

	<div class="row">
		<br>
	</div>

</div>

<!-- Cintillo de texto -->
<div class="row cintillo" id="cintillo" onclick="opendialog()">
	<marquee behavior="scroll" direction="left" scrollamount="3">Advantel Consultores C.A.</marquee>
</div>

<script type="text/javascript"
	src="<?php echo Yii::app()->request->baseUrl; ?>/js/highcharts.js"></script>
<script type="text/javascript"
	src="<?php echo Yii::app()->request->baseUrl; ?>/js/highcharts-more.js"></script>
<script type="text/javascript"
	src="<?php echo Yii::app()->request->baseUrl; ?>/js/exporting.js"></script>
<script type="text/javascript"
	src="<?php echo Yii::app()->request->baseUrl; ?>/js/solid-gauge.js"></script>