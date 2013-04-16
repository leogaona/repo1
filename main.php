<?
	$s_time = microtime(true);
	error_reporting(1);

	$l = mysql_connect("localhost", "root", "");
	if(!$l) {
		log_event(mysql_error());
		die("ERROR: " . mysql_error());
	}
	if(!mysql_selectdb("unicef", $l)) {
		log_event(mysql_error());
		die("ERROR: " . mysql_error());
	}

	$final = false;

	$generic_url = "http://192.168.0.46:13013/cgi-bin/sendsms?username=simple&password=12345&to=#{sms.to}&text=#{sms.body}&from=#{sn}&smsc=TIGO_NB&priority=3";
	
	$from = $_GET['from'];
	$sn = $_GET['to'];	
	$body = $_GET['body'];
	$operator = $_GET['operator'];
	
	if($operator != 'TIGO_220') {
		print "Operator is not TIGO\n";
		log_event("Operator is not TIGO");
		die();
	}

	//if(preg_match("/98[\d]{7}/", $from, $matches) == 0) {
	if(preg_match("/98[\d]{7}|97[\d]{7}|99[\d]{7}|96[\d]{7}/", $from, $matches) == 0) {
		print "Phone string couldn't be found\n";
		log_event("Phone string couldn't be found");
		die();
	}
	
	$from = "595" . $matches[0];
	
	$unicef = "/^candidato$/i";
	$response = "";
if( preg_match($unicef, $body)){
		//mensaje 1: seleccion de candidato
		$response = "Selecciona a quien queres enviar tu pregunta:\r\nA.Horacio Cartes\r\nB.Mario Ferreiro\r\nC.Efrain Alegre\r\nD.Miguel Carrizosa\r\nE.Anibal Carrillo\r\nF.Lilian Soto\r\nOrden segun numero de lista. Mensaje sin Costo";
		#$response = "Selecciona";
}

else if(preg_match("/^[a-f]$/i", $body, $matches) >0 ){
		$select = $matches[0];
		$select = strtoupper($select);

		// Busco si el numero tiene session
		$user_session = mysql_query("SELECT * from user_sessions where phone = '$from'", $l);


		//NO SESSION
		if(!mysql_num_rows($user_session)){
			$user_insert = mysql_query("INSERT INTO users(created_at, updated_at,phone, body) VALUES(now(), now(), '$from','$body')", $l);
			$user_session_id =  mysql_insert_id();

			//guardo session
			$user_candidato = mysql_query("INSERT INTO user_sessions(created_at, updated_at,user_id, candidato, phone) VALUES(now(), now(), '$user_session_id','$select', '$from')", $l);	


			//mensaje 2: preguntas a seleccionar
			$response = "Que propone para..?\r\nA)Aumentar la inversion en la infancia\r\nB)Evitar que 1.600 bebes mueran anualmente antes del 1er anho\r\nC)Mejorar la calidad de la educacion";
			#$response ="Los candidatos";
		//SESSION
		}else if(mysql_num_rows($user_session) && preg_match("/^[a-c]$/i", $select, $matches) > 0){
			$pregunta = strtoupper($select);
			while($row=mysql_fetch_array($user_session)){
	        	$letra = $row["candidato"];
	        	$user_delete = $row["id"];
	        	$usuario = $row["user_id"];
	        	$tel = $row["phone"];
			}
			$seleccion = mysql_query("INSERT INTO selecciones(created_at, updated_at,user_id, candidato, pregunta, phone) VALUES(now(), now(), '$usuario','$letra', '$pregunta', '$tel')", $l);	
			
			// mensaje 3: respuestas de candidatos
			if($letra == "A"){
				switch ($pregunta) {
				    case "A":
				        $response = "Impulsar e implementar la Ley de Financiamiento del Sistema Nacional de Proteccion de la Ninhez. Descentralizar la atencion a la infancia asistiendo y dando presupuesto a las CODENIs. Asegurar el presupuesto  para ejecutar eficientemente el Plan Nacional de Desarrollo de la Primera Infancia, entre varias acciones mas";
				        break;
				    case "B":
				        $response = "Garantizar la atencion prenatal de la embarazada. Fomentar la lactancia materna. Aumentar y equipar con clinicas moviles las Unidades de Salud de la Familia, para prevenir y atender precozmente la salud infantil. Suministrar complemento nutricional a embarazadas y madres lactantes con desnutricion, entre varias mas";
				        break;
				    case "C":
				        $response = "Inversion eficiente en formacion de maestros e instalacion definitiva de la carrera docente. Racionalizacion eficiente de recursos en el MEC. Elevar al 7% del PIB la inversion en Educacion. Impulsar la Formacion Tecnico Profesional. Inversion fuerte en nivel inicial, a partir de los tres anhos, entre varias acciones mas";
				        break;
				}
			}
		
			if($letra == "B"){
				switch ($pregunta) {
				    case "A":
				        $response = "Presupuestaremos necesidades de infancia con participacion de comunidades, bajo lineamientos del Plan Nacional de Desarrollo Integral de Primera Infancia. Revisaremos la matriz tributaria con criterios de justicia para aumentar los recursos del Estado que puedan revertirse en servicios publicos universales y de calidad";
				        break;
				    case "B":
				        $response = "Desarrollaremos la Estrategia Promocional de Calidad de Vida y redes de servicios del Sistema de Salud territorialmente por lineas de atencion que aseguren la cobertura de embarazo, parto, seguimiento neonatal y lactancia en el marco de un Sistema Universal de Protecciones Sociales que no genere gasto de bolsillo";
				        break;
				    case "C":
				        $response = "Enfatizar la formacion del docente y su ingreso a un sistema de educacion permanente que permita mejor interaccion con los demas miembros de la comunidad educativa, con desarrollo tecnologico y asegurando el acceso a educacion superior de calidad incrementando ademas la inversion en educacion en relacion al PIB";
				        break;
				}
			}

		
			if($letra == "C"){
				switch ($pregunta) {
				    case "A":
				        $response = "De lo que hagamos y les ensenhemos hoy depende el futuro de nuestros ninhos. Invertiremos \$150 millones en 5 anhos para erradicar la desnutricion infantil y formar ciudadanos con oportunidades. Y mas de 100 millones de USD por anho con fondos blindados por FONACIDE para mejorar la educacion #compromisoPyAlegre";
				        break;
				    case "B":
				        $response = "Proponemos atencion integral universal, servicios de salud, antes, durante y despues del embarazo para cuidar a ninhos y mujeres embarazadas. Duplicar el presupuesto e instalar el seguro universal de salud, unidades neonatales en cabeceras departamentales y unidades de la flia en cada comunidad, 1500 #compromisoPyAlegre";
				        break;
				    case "C":
				        $response = "Vamos a educar emprendedores creativos. En 5 anhos universalizaremos el programa Una computadora por Nino, 700 mil ninhos y 70 mil docentes. Aumentaremos a 1% el PIB para apoyar investigacion y desarrollo cientifico, instalaremos bibliotecas en 8mil escuelas, capacitaremos y certificaremos a docentes #CompromisoPyAlegre";
				        break;
				}
			}

			if($letra == "D"){
				switch ($pregunta) {
				    case "A":
				        $response = "Elevaremos el nivel presupuestario de la Secretaria de la Ninhez y Adolescencia, dotandola de recursos por programa, asegurando presupuesto de los mismos siguiendo la exitosa experiencia de la Ley de Nutricion infantil para la primera infancia, para erradicar la desnutricion con apoyo de comunicadores y organizaciones";
				        break;
				    case "B":
				        $response = "Proponemos asegurar 6 controles prenatales, al incluirlos como requisito de las madres dentro de los programas de transferencias monetarias con corresponsabilidad; de manera a que lleguen al parto con cierta preparacion y control. Y duplicar la inversion de unidades de cuidados intensivos neonatales";
				        break;
				    case "C":
				        $response = "Seguiremos apostando a la capacitacion continua de docentes, con incentivos economicos acordes a la formacion de los mismos tal como hemos logrado en la ejecucion del programa de Una Computadora por Ninho en la ciudad de Caacupe, y asegurando que los recursos presupuestados para los mismos sean utilizados para dicho fin";
				        break;
				}
			}

		
			if($letra == "E"){
				switch ($pregunta) {
				    case "A":
				        $response = "Garantizaremos un presupuesto de al menos 15% del PIB para salud, educacion,prevencion y atencion. Garantizaremos la suba de impuestos a las tierras improductivas y a la exportacion de granos. Al menos 20% de los royalties recibidos por las municipalidades iran al fortalecimiento de las CODENIs.";
				        break;
				    case "B":
				        $response = "Implementaremos el plan Primera Infancia, coordinado entre el MEC, MSPyBS y la SNNA. Coordinaremos desde las Unidades de Salud Familiar una politica de prevencion, nutricion y atencion a todas las mujeres embarazadas y recien nacidos, con entrega de kits alimenticios y servicio de informacion.";
				        break;
				    case "C":
				        $response = "Formacion continua de docentes con revision y adecuacion del curriculo a la realidad educativa. Mejoraremos infraestructuras y universalizaremos la gratuidad. Una ley de educacion superior con inversion publica para la apertura pedagogica, la participacion y la investigacion en la universidad publica.";
				        break;
				}
			}

		
			if($letra == "F"){
				switch ($pregunta) {
				    case "A":
				        $response = "Presupuesto que priorice salud, educacion y proteccion. Coordinacion con el Gab Social y Sistema Nacional de Proteccion. Promocion de Derechos del Ninho/a para seguimiento de resultados. Ley de inversion para la ninhez y adolescencia asegurando 10 % de los royalties p/ gobiernos locales p/ inversion especifica en ellos.";
				        break;
				    case "B":
				        $response = "Atencion integral a la mujer embarazada en USFs instaladas en todo el pais con descentralizacion y participacion social. Gratuidad y acceso universal a los centros de atencion con equip de alta complejidad y mejoramiento de logistica. Prevencion de diarreas agudas e IRAs. Atencion integral a bebes menores de 29 dias.";
				        break;
				    case "C":
				        $response = "Promocion y fortalecimiento de politicas culturales y educativas en los niveles sub nacionales, departamentales y municipales. 100% de cobertura de educacion inicial y media para ninhos y ninhas a traves de Programas de acceso a los locales educativos, alimentacion en la escuela y provision de material escolar completo.";
				        break;
				}
			}
			//borro id
			$delete_session = mysql_query("DELETE FROM user_sessions WHERE id = '$user_delete' LIMIT 1");
			$final = true;
		}else if(mysql_num_rows($user_session) && preg_match("/^[a-c]$/i", $select, $matches) == 0){
			while($row=mysql_fetch_array($user_session)){
	        	$user_delete = $row["id"];
			}
			$delete_session = mysql_query("DELETE FROM user_sessions WHERE id = '$user_delete' LIMIT 1");
			$response = "Pregunta no valida. Envia candidato al 220";
		}else{

			$response = "Lo sentimos mensaje no valido. Envia candidato al 220";
		}
}else{
	$response = "Lo sentimos mensaje no valido. Envia candidato al 220";
}


	
mysql_close($l);


if($response != ""){
			$url = str_replace("#{sms.to}", $from, $generic_url);
			$url = str_replace("#{sn}", $sn, $url);
			$url = str_replace("#{sms.body}", urlencode($response), $url);
			$url .= "&binfo=RTELE";
		
			$e_time = microtime(true);
			$t_time = sprintf("%01.5f", $e_time - $s_time);
			file_get_contents($url);
			log_access(colored($t_time) . " " . $body . " " . $from . " " . $body . " " . $url);
}
	


	sleep(1);
	if($final == true){
		
		$response = final_msg();
			
			if($response != ""){
			$url = str_replace("#{sms.to}", $from, $generic_url);
			$url = str_replace("#{sn}", $sn, $url);
			$url = str_replace("#{sms.body}", urlencode($response), $url);
			$url .= "&binfo=RTELE";
		
			$e_time = microtime(true);
			$t_time = sprintf("%01.5f", $e_time - $s_time);
			file_get_contents($url);
			log_access(colored($t_time) . " " . $body . " " . $from . " " . $body . " " . $url);
		}	
	}
	function final_msg(){
		return "Gracias por participar de esta actividad. Antes de votar pensa en los ninhos, ninhas y adolescentes. Unicef, frente por la Ninhez con el apoyo de TIGO y Chena Ventures Paraguay S.A";
	}
	
	function colored($time) {
		if($time>0 && $time<=0.5) {
			return "\033[32m $time \033[39m";
		}
		else if($time>0.5 && $time<1) {
			return "\033[36m $time \033[39m";
		}
		else {
			return "\033[31m $time \033[39m";
		}
		
	}
	
	function log_event($message) {
		$f = fopen("/var/log/sn220.error.log", "a");
		fwrite($f, date('Y-m-d H:i:s')." " . $message."\n");
		fclose($f);
	}
	
	function log_access($message) {
		$f = fopen("/var/log/sn220.access.log", "a");
		fwrite($f, date('Y-m-d H:i:s')." MESSAGE " . $message."\n");
		fclose($f);
	}	
?>