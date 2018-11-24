<?php
    include '../../seguridad/carrito/dbconnect.php';

    /* Se realiza la conexion con la base de datos */
    $conexion = @mysqli_connect(IP,USER,PW,DB);
    if(!$conexion){ echo '<h1 style="color:red;text-align:center;">Ha ocurrido un error al conectarse a la DB</h1>'; exit; }
    mysqli_set_charset($conexion,'utf8');
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
        <script src="js/jquery-3.3.1.js"></script>
        <script src="js/jquery.redirect.js"></script>
        <script src="js/carrito.js"></script>
    </head>
    <body class="bodycssloader">
        <header class="contenidomain">
            <nav>
                <ul>
                    <li><a href="index.php">Volver a la Tienda</a></li>
                </ul>
            </nav>
        </header>
        <section class="loadercontent">
            <div id="app-cover">
                <div class="square">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>    
                <div id="ghost">
                    <div id="eyes">
                    <div class="eye"></div>
                    <div class="eye"></div>
                    </div>
                    <div id="mouth"></div>
                    <div id="legs"></div>
                </div>
            </div>     
        </section>
        <div class="titulo loadercontent">
            <h1>Cargando contenido del carrito ...</h1>
        </div>
        <main class="contenidomain">
            <div class="titulopag">
                <h1>Artículos</h1>
            </div>
            <div class="contendiocarrito">
                <table id="cesta">
                    <tr>
                        <th>Nº. Articulos</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                    </tr>

                    <?php
                        $sumatotal = 0;

                        if(isset($_COOKIE) && count($_COOKIE) > 0) {
                            foreach ($_COOKIE as $key=>$val){
                                if(substr($key, 0, 8) == 'PRODUCTO'){
                                    $query = mysqli_prepare($conexion,'select pvp from productos where nombre=?');
                                    mysqli_stmt_bind_param($query,'s',$key);
                                    mysqli_stmt_execute($query);
                                    mysqli_stmt_bind_result($query,$precio);
                                    mysqli_stmt_fetch($query);
                                    $sumatotal += $val*floatval($precio);
                                    echo '<tr id="'.$key.'">';
                                    echo '<td>'.$val.'</td>';
                                    echo '<td>'.$key.'  <a href="#" id="'.$key.'" class="borrarproducto">Borrar</a></td>';
                                    echo '<td id="precio'.$key.'">'.($val*floatval($precio)).'€</td>';
                                    echo '</tr>';
                                    mysqli_stmt_close($query);
                                }
                            }
                        } else {
                            echo '<tr><td colspan="3">No se encuentran productos.</td></tr>';
                        }
                        mysqli_close($conexion);
                    ?>

                    <tr class="finalinfo">
                        <td>Precio total</td>
                        <td id="costetotal"><?=$sumatotal?>€</td>   
                        <td class="btncontainer"><button id="finalizarcompra">Finalizar Compra</button></td>
                    </tr>
                </table>
            </div>
            <div class="dniinput">
                <h2>Introduce un DNI para identificarte:</h2>
                <div class="dni">
                    <input type="text" id="userdni" value="00391578A">
                    <button id="procesarpedido">Procesar Pedido</button>
                </div>
            </div>
        </main>
    </body>
</html>