<?php

    include '../../seguridad/carrito/dbconnect.php';
    date_default_timezone_set("Europe/Madrid");

    /* Se realiza la conexion con la base de datos */
    $conexion = @mysqli_connect(IP,USER,PW,DB);
    if(!$conexion){ echo '<h1 style="color:red;text-align:center;">Ha ocurrido un error al conectarse a la DB</h1>'; exit; }
    mysqli_set_charset($conexion,'utf8');
    mysqli_autocommit($conexion, FALSE);

    $datosnovalidos = array();
    $query_success = true;

    if(isset($_POST["json_string"]) && isset($_POST["dni"])) {
        $jsondata = json_decode($_POST["json_string"], true);
        $dni = strip_tags(trim($_POST["dni"]));

        /* 
            Comprueba que el DNI exista en la base de datos, en caso contrario
            se hara un rollback.
        */
        $sql = 'select nombre from clientes where dni=? for update';
        $query = mysqli_prepare($conexion,$sql);
        mysqli_stmt_bind_param($query,'s',$dni);
        mysqli_stmt_execute($query);
        mysqli_stmt_bind_result($query,$nombre);
        mysqli_stmt_fetch($query);
        if(mysqli_affected_rows($conexion) == 0){
            $query_success = false;
        }
        mysqli_stmt_close($query);

        /* 
            Inserta en la tabla de facturas la nueva factura creada, de tal manera
            que se tenga registro de ella desde el principio.
        */
        $sql = "insert into facturas (fecha,dni) values (str_to_date(?,'%d/%m/%Y'),?)";
        $dia = date('d') . "/" . date('m') . "/" . date('Y');
        $query = mysqli_prepare($conexion,$sql);
        mysqli_stmt_bind_param($query,'ss',$dia,$dni);
        mysqli_stmt_execute($query);
        mysqli_stmt_close($query);

        /* Pregunta por el id de la factura que se acaba de añadir */
        $lastid = mysqli_insert_id($conexion);

        /*
            Pasa por todo el array que contiene los elementos que se han comprado, y realiza lo siguiente:
            selecciona el stock actual de cada producto y lo compara con el comprado, en caso de que no se disponga
            de suficiente stock se hace rollback, inserta en la tabla lineas, lo que se ha comprado, y hace el update del stock
            de los productos comprados.
        */
        foreach ($jsondata as $key => $value) {
            $query = mysqli_prepare($conexion,'select stock, codigo, pvp from productos where nombre=?');
            mysqli_stmt_bind_param($query,'s',$key);
            mysqli_stmt_execute($query);
            mysqli_stmt_bind_result($query,$stock,$codigo,$precio);
            mysqli_stmt_fetch($query);
            mysqli_stmt_close($query);

            $p = $value*floatval($precio);
            $query = mysqli_prepare($conexion,'insert into lineas (numero,codigo,cantidad,pvp) values (?,?,?,?)');
            mysqli_stmt_bind_param($query,'ssss',$lastid,$codigo,$value,$p);
            mysqli_stmt_execute($query);
            mysqli_stmt_close($query);

            $newstock = $stock - $value;
            if($newstock >= 0) {
                $query = mysqli_prepare($conexion,'update productos set stock=? where nombre=?');
                mysqli_stmt_bind_param($query,'ss',$newstock,$key);
                mysqli_stmt_execute($query);
                mysqli_stmt_close($query);
            } else {
                array_push($datosnovalidos,$key);
                $query_success = false;
            }
        }
    }

?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <title>Tienda de Pablo</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="img/icono.png" rel="shortcut icon" type="image/png">
        <link href="https://fonts.googleapis.com/css?family=Roboto+Mono" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="css/carrito.css">
        <script src="js/factura.js"></script>
    </head>
    <body>
        <header>
            <nav>
                <ul>
                    <li><a href="index.php">Volver a la Tienda</a></li>
                </ul>
            </nav>
        </header>
        <?php
            /* En caso de que el dni exista, y el stock sea suficiente, se hace el commit */
            if($query_success) { 
                mysqli_commit($conexion); 
                echo '<main><div class="titulopag"><h1>Factura Nº ' . $lastid .'</h1></div><div class="contendiocarrito"><table id="cesta"><tr><th>Nº. Articulos</th><th>Nombre</th></tr>';       
                foreach($_COOKIE as $n => $v) {
                    echo '<tr>';
                    echo "<td>" . $v . "</td>";
                    echo "<td>" . $n . "</td>";
                    echo '</tr>';
                }
                echo '</table></div></main>';
            } else { 
                mysqli_rollback($conexion); 
                echo '<main><div class="titulopag"><h1>Compra Fallida, los siguientes elementos no estan disponibles.</h1></div><div class="contendiocarrito"><table id="cesta"><tr><th>Nombre</th></tr>';       
                foreach($datosnovalidos as $n) {
                    echo '<tr>';
                    echo "<td>" . $n . "</td>";
                    echo '</tr>';
                }
                echo '</table></div></main>';
            }
            mysqli_close($conexion);
        ?>
    </body>
<html>