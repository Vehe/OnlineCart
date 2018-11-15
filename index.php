<?php
    include '../../seguridad/carrito/dbconnect.php';

    /* Se realiza la conexion con la base de datos */
    $conexion = @mysqli_connect(IP,USER,PW,DB);
    if(!$conexion){ echo '<h1 style="color:red;text-align:center;">Ha ocurrido un error al conectarse a la DB</h1>'; exit; }
    mysqli_set_charset($conexion,'utf8');

    
    if(isset($_POST["n"]) && isset($_POST["v"])){
        $n = strip_tags(trim($_POST["n"]));
        $v = strip_tags(trim($_POST["v"]));
        setcookie($n, $v);
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
        <link rel="stylesheet" type="text/css" href="css/main.css">
        <script src="js/main.js"></script>
        <script src="js/jquery-3.3.1.js"></script>
    </head>
    <header>
        <div class="dropdown">
            <img src="img/cart.png">
            <div class="dropdown-content">
                <table id="contenidoCarrito">
                    <tr class="errorCarrito">
                        <th><img src="img/error.gif" class="notfound"></th>
                    </tr>
                    <tr class="errorCarrito">
                        <td>El carrito esta vacío.</td>
                    </tr>
                </table>
            </div>
        </div>
    </header>
    <body onload="init()">
        <main>
            <div class="rowdata">
            <?php

                $sql = 'select nombre, stock from productos';
                $query = mysqli_prepare($conexion,$sql);
                $aux = 0;
                $cont = 0;

                for($i=0;$i<2;$i++){
                    if($i == 0){
                        mysqli_stmt_execute($query);
                        mysqli_stmt_bind_result($query,$nombre,$stock);

                        echo '<div class="coldata">';
                        echo '<form action=""><input type="text" class="nombres" value="NOMBRE" readonly="readonly"><input type="text" class="nombres" value="STOCK" readonly="readonly"><input type="text" class="nombres" value="CANTIDAD" readonly="readonly"></form>';
                        echo '<form action="index.php" class="insideForm" method="POST" id="form'.$aux.'">';
                        while(mysqli_stmt_fetch($query)){
                            
                            echo '<input type="text" name="nombre" value="'.$nombre.'" class="inp'.$aux.'" readonly="readonly">';
                            echo '<input type="number" name="stock" value="'.$stock.'" class="inp'.$aux.'" readonly="readonly">';
                            echo '<input type="number" id="canti'.$aux.'" name="cantidad" value="0" class="inp'.$aux.' userInp">';
                            $aux++;
                            if($i == 0) {$cont++;};
                        }
                        echo '</form>';
                        echo '</div>';
                    } else {
                        echo '<div class="colbtn">';
                        echo '<form action=""><input type="text" id="add_form_dec" class="nombres" value="AÑADIR" readonly="readonly"></form>';
                        for($j=0;$j<$cont;$j++){
                            echo '<button class="btn'.$j.'" onclick="addProductToCart('.$j.')" name="add_to_cart">Añadir al Carrito</button>';
                        }
                        echo '</div>';
                    }
                }

                mysqli_stmt_close($query);
                mysqli_close($conexion);
            ?>
            </div>
        </main>
    </body>
</html>