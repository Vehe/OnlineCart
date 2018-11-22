<?php

    include '../../seguridad/carrito/dbconnect.php';

    /* Se realiza la conexion con la base de datos */
    $conexion = @mysqli_connect(IP,USER,PW,DB);
    if(!$conexion){ echo '<h1 style="color:red;text-align:center;">Ha ocurrido un error al conectarse a la DB</h1>'; exit; }
    mysqli_set_charset($conexion,'utf8');
    mysqli_autocommit($conexion, FALSE);

    $query_success = true;

    if(isset($_POST["json_string"]) && isset($_POST["dni"])) {
        $jsondata = json_decode($_POST["json_string"], true);
        $dni = strip_tags(trim($_POST["dni"]));

        /* Comprueba que el DNI exista en la bbdd */
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

        /* Efectua un insert en la bbdd */
        $sql = "insert into facturas (fecha,dni) values (str_to_date(?,'%d/%m/%Y'),?)";
        $dia = date('d') . "/" . date('m') . "/" . date('Y');
        $query = mysqli_prepare($conexion,$sql);
        mysqli_stmt_bind_param($query,'ss',$dia,$dni);
        mysqli_stmt_execute($query);
        mysqli_stmt_close($query);

        /* Pregunta por el id de la factura que se acaba de añadir */
        $lastid = mysqli_insert_id($conexion);

        foreach ($jsondata as $key => $value) {
            $query = mysqli_prepare($conexion,'select stock, codigo from productos where nombre=?');
            mysqli_stmt_bind_param($query,'s',$key);
            mysqli_stmt_execute($query);
            mysqli_stmt_bind_result($query,$stock,$codigo);
            mysqli_stmt_fetch($query);
            mysqli_stmt_close($query);

            $query = mysqli_prepare($conexion,'select pvp from productos where nombre=?');
            mysqli_stmt_bind_param($query,'s',$key);
            mysqli_stmt_execute($query);
            mysqli_stmt_bind_result($query,$precio);
            mysqli_stmt_fetch($query);
            mysqli_stmt_close($query);

            $p = $value*floatval($precio);
            $query = mysqli_prepare($conexion,'insert into lineas (numero,codigo,cantidad,pvp) values (?,?,?,?)');
            mysqli_stmt_bind_param($query,'ssss',$lastid,$codigo,$value,$p);
            mysqli_stmt_execute($query);
            mysqli_stmt_close($query);

            $newstock = $stock - $value;
            echo $newstock;
            if($newstock >= 0) {
                $query = mysqli_prepare($conexion,'update productos set stock=? where nombre=?');
                mysqli_stmt_bind_param($query,'ss',$newstock,$key);
                mysqli_stmt_execute($query);
                mysqli_stmt_close($query);
            } else {
                $query_success = false;
            }
        }
    }

    if($query_success) { mysqli_commit($conexion); echo "correcto"; } else { mysqli_rollback($conexion); echo "rollback"; }
    mysqli_close($conexion);
?>